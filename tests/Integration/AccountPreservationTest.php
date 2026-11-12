<?php

use App\Models\Demande;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;

uses(Tests\TestCase::class);
require_once __DIR__.'/../Support/WorkspaceDatabase.php';

beforeEach(function () {
    $this->admin = \Tests\Support\resetWorkspaceDatabase();
    $this->owner = User::create(['name'=>'Owner','prenom'=>'Test','email'=>'preserve@example.invalid','password'=>'test-password']);
    $this->owner->assignRole('demandeur');
    $this->demande = Demande::create(['user_id'=>$this->owner->id,'nom'=>'Dossier conservé','statut'=>'soumise']);
});

it('deactivates an account and preserves its requests decisions attachments roles and sessions of other users', function () {
    config(['session.driver'=>'database']);
    DB::table('demandes_validations')->insert(['demande_id'=>$this->demande->id,'user_id'=>$this->owner->id,'role'=>'exploitant','decision'=>'accord']);
    DB::table('validations')->insert(['demande_id'=>$this->demande->id,'validator_id'=>$this->owner->id,'type'=>'responsable_S','statut'=>'accord']);
    DB::table('demandes_pieces_jointes')->insert(['demande_id'=>$this->demande->id,'uploaded_by'=>$this->owner->id,'chemin_fichier'=>'test.pdf']);
    foreach ([$this->owner,$this->admin] as $user) {
        DB::table('sessions')->insert(['id'=>'account-session-'.$user->id,'user_id'=>$user->id,'payload'=>'','last_activity'=>time()]);
    }
    $this->owner->forceFill(['remember_token'=>'old-token'])->save();
    Password::createToken($this->owner);
    $this->actingAs($this->admin)->delete(route('users.destroy',$this->owner))->assertRedirect(route('users.index'));
    expect($this->owner->fresh()->deactivated_at)->not->toBeNull()
        ->and($this->owner->fresh()->remember_token)->toBeNull()
        ->and($this->demande->fresh()->statut)->toBe('soumise')
        ->and(DB::table('demandes_validations')->count())->toBe(1)
        ->and(DB::table('validations')->count())->toBe(1)
        ->and(DB::table('demandes_pieces_jointes')->count())->toBe(1)
        ->and($this->owner->fresh()->hasRole('demandeur'))->toBeTrue()
        ->and(DB::table('sessions')->where('user_id',$this->owner->id)->count())->toBe(0)
        ->and(DB::table('sessions')->where('id','account-session-'.$this->admin->id)->exists())->toBeTrue()
        ->and(DB::table('password_reset_tokens')->where('email',$this->owner->email)->exists())->toBeFalse();
});

it('blocks inactive password login remembered authentication password recovery and existing sessions', function () {
    $this->owner->forceFill(['deactivated_at'=>now(),'remember_token'=>'remember-test'])->save();
    $this->post(route('login'),['email'=>$this->owner->email,'password'=>'test-password','remember'=>true])->assertSessionHasErrors('email');
    $this->assertGuest();
    $provider = Auth::createUserProvider('users');
    expect($provider->retrieveById($this->owner->id))->toBeNull()
        ->and($provider->retrieveByToken($this->owner->id,'remember-test'))->toBeNull()
        ->and(Password::broker()->getUser(['email'=>$this->owner->email]))->toBeNull();
    $this->withSession([Auth::guard()->getName()=>$this->owner->id])->get(route('dashboard'))->assertRedirect(route('login'));
    // Covers a user object resolved before a concurrent deactivation.
    $this->actingAs($this->owner)->getJson(route('notifications.index'))->assertUnauthorized();
    $this->assertGuest();
});

it('lets only administrators reactivate an account without restoring old remember tokens', function () {
    app(\App\Services\UserAccountStatus::class)->deactivate($this->owner,$this->admin);
    $this->actingAs($this->admin)->get(route('users.index'))->assertOk()->assertSee('Désactivé')->assertSee('Réactiver');
    $this->patch(route('users.reactivate',$this->owner))->assertRedirect(route('users.index'));
    expect($this->owner->fresh()->deactivated_at)->toBeNull()->and($this->owner->fresh()->remember_token)->toBeNull();
    Auth::forgetGuards();
    $this->post(route('login'),['email'=>$this->owner->email,'password'=>'test-password'])->assertSessionHasNoErrors();
    $this->assertAuthenticatedAs($this->owner);
    $this->patch(route('users.reactivate',$this->admin))->assertForbidden();
});

it('preserves a self-deactivated requester and prevents administrators from deactivating themselves', function () {
    $this->actingAs($this->owner)->delete(route('profile.destroy'),['password'=>'test-password'])->assertRedirect('/');
    expect($this->owner->fresh()->deactivated_at)->not->toBeNull()->and($this->demande->fresh())->not->toBeNull();
    $this->assertGuest();
    $this->actingAs($this->admin)->delete(route('profile.destroy'),['password'=>'test-password'])->assertSessionHasErrors('account');
    expect($this->admin->fresh()->deactivated_at)->toBeNull();
});

it('rejects raw deletion of a referenced user at the database boundary', function () {
    expect(fn () => DB::table('users')->where('id',$this->owner->id)->delete())->toThrow(\Illuminate\Database\QueryException::class);
    expect($this->demande->fresh())->not->toBeNull();
});

it('blocks all demo account seeders in production and seeds only reference roles by default', function () {
    $original = app()->environment();
    app()->detectEnvironment(fn () => 'production');
    try {
        foreach ([\Database\Seeders\UserSeeder::class,\Database\Seeders\DemandeTestSeeder::class,\Database\Seeders\StructuresSpecialiseesSeeder::class] as $seeder) {
            expect(fn () => app($seeder)->run())->toThrow(RuntimeException::class);
        }
        $before = User::count();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        expect(User::count())->toBe($before);
    } finally {
        app()->detectEnvironment(fn () => $original);
    }
});

it('creates an administrator through confidential prompts and never promotes an existing account', function () {
    $this->artisan('dma:create-admin')
        ->expectsQuestion('Nom','Administrateur')->expectsQuestion('Prénom','Entreprise')
        ->expectsQuestion('Adresse e-mail','admin-enterprise@example.invalid')
        ->expectsQuestion('Mot de passe','Example-Admin-2026!')->expectsQuestion('Confirmer le mot de passe','Example-Admin-2026!')
        ->assertSuccessful()->run();
    $admin = User::where('email','admin-enterprise@example.invalid')->firstOrFail();
    expect($admin->hasRole('admin'))->toBeTrue()
        ->and(\Illuminate\Support\Facades\Hash::check('Example-Admin-2026!',$admin->password))->toBeTrue();
    $this->artisan('dma:create-admin')
        ->expectsQuestion('Nom','Existing')->expectsQuestion('Prénom','Test')
        ->expectsQuestion('Adresse e-mail',$this->owner->email)
        ->expectsQuestion('Mot de passe','Example-Admin-2026!')->expectsQuestion('Confirmer le mot de passe','Example-Admin-2026!')
        ->assertFailed()->run();
    expect($this->owner->fresh()->hasRole('admin'))->toBeFalse();
});

it('refuses the local migration helper outside local environments without touching the database', function () {
    $this->artisan('dma:migrate-local')->assertFailed()->run();
    expect(User::count())->toBe(2)->and(Demande::count())->toBe(1);
});

it('enforces unique DMA references and structure selections while allowing no specialized structure', function () {
    $this->demande->update(['numero_dma'=>'MS2026/0042']);
    expect(fn () => Demande::create(['user_id'=>$this->owner->id,'nom'=>'Duplicate','numero_dma'=>'MS2026/0042']))->toThrow(\Illuminate\Database\QueryException::class);
    expect($this->demande->structuresSpecialisees()->count())->toBe(0);
    $id=DB::table('structures_specialisees')->insertGetId(['nom'=>'Test structure']);
    $this->demande->structuresSpecialisees()->attach($id);
    expect(fn () => $this->demande->structuresSpecialisees()->attach($id))->toThrow(\Illuminate\Database\QueryException::class);
    expect(fn () => DB::table('demandes')->where('id',$this->demande->id)->update(['statut'=>null]))->toThrow(\Illuminate\Database\QueryException::class);
    expect($this->demande->fresh()->statut)->toBe('soumise');
});
