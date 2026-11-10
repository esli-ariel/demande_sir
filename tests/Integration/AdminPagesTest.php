<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(Tests\TestCase::class);

require_once __DIR__.'/../Support/WorkspaceDatabase.php';

beforeEach(function () {
    $this->admin = \Tests\Support\resetWorkspaceDatabase();
    $this->actingAs($this->admin);
});

it('creates an account with the selected existing role and redirects to users', function () {
    $this->post(route('users.store'), [
        'name' => 'Technique', 'prenom' => 'Test', 'email' => 'created@example.invalid',
        'password' => 'test-password', 'password_confirmation' => 'test-password', 'role' => 'dts',
    ])->assertRedirect(route('users.index'))->assertSessionHasNoErrors();

    $user = User::where('email', 'created@example.invalid')->firstOrFail();
    expect($user->hasRole('dts'))->toBeTrue()->and(Hash::check('test-password', $user->password))->toBeTrue();
});

it('rejects an unknown role without creating an account', function () {
    $this->post(route('users.store'), [
        'name' => 'Test', 'prenom' => 'Test', 'email' => 'invalid@example.invalid',
        'password' => 'test-password', 'password_confirmation' => 'test-password', 'role' => 'invented',
    ])->assertSessionHasErrors('role');
    expect(User::count())->toBe(1);
});

it('updates a user role without replacing their password', function () {
    $user = User::create(['name' => 'Avant', 'prenom' => 'Test', 'email' => 'existing@example.invalid', 'password' => 'unchanged-password']);
    $user->assignRole('demandeur');
    $password = $user->password;
    $this->put(route('users.update', $user), ['name' => 'Après', 'prenom' => 'Test', 'email' => $user->email, 'role' => 'dts'])
        ->assertRedirect(route('users.index'))->assertSessionHasNoErrors();
    expect($user->fresh()->hasRole('dts'))->toBeTrue()->and($user->fresh()->password)->toBe($password);
});

it('prevents removing the current administrator role or deleting their account', function () {
    $this->put(route('users.update', $this->admin), ['name' => 'Admin', 'prenom' => 'Test', 'email' => $this->admin->email, 'role' => 'demandeur'])->assertSessionHasErrors('role');
    $this->delete(route('users.destroy', $this->admin))->assertSessionHas('error');
    expect(User::count())->toBe(1)->and($this->admin->fresh()->hasRole('admin'))->toBeTrue();
});

it('searches users and blocks non-admin access', function () {
    $this->get(route('users.index', ['q' => 'aucun-resultat']))->assertOk()->assertSee('Aucun utilisateur trouvé.');
    $user = User::create(['name' => 'Ordinaire', 'prenom' => 'Test', 'email' => 'ordinary@example.invalid', 'password' => 'test-password']);
    $user->assignRole('demandeur');
    $this->actingAs($user)->get(route('users.index'))->assertForbidden();
});

it('marks only the signed-in users notification as read', function () {
    $own = (string) Str::uuid();
    $other = (string) Str::uuid();
    foreach ([[$own, $this->admin->id], [$other, $this->admin->id + 100]] as [$id, $userId]) {
        DB::table('notifications')->insert(['id' => $id, 'type' => 'test', 'notifiable_type' => User::class, 'notifiable_id' => $userId, 'data' => json_encode(['message' => 'Test notification']), 'created_at' => now(), 'updated_at' => now()]);
    }
    $this->post(route('notifications.read', $other))->assertNotFound();
    expect(DB::table('notifications')->where('id', $other)->value('read_at'))->toBeNull();
    $this->post(route('notifications.read', $own))->assertRedirect();
    expect(DB::table('notifications')->where('id', $own)->value('read_at'))->not->toBeNull();
});

it('creates a request without files and saves every attached file', function () {
    Storage::fake('public');
    $payload = ['structure' => 'Test', 'unite_concernee' => 'Test', 'repere' => 'Test', 'fonction' => 'Test', 'objet_modif' => 'Test', 'motif' => 'Test', 'situation_existante' => 'Test', 'situation_souhaitee' => 'Test'];
    $this->post(route('demandes.store'), $payload)->assertRedirect(route('demandes.index'))->assertSessionHasNoErrors();
    $this->post(route('demandes.store'), $payload + ['pieces_jointes' => [UploadedFile::fake()->create('one.pdf', 10, 'application/pdf'), UploadedFile::fake()->create('two.pdf', 10, 'application/pdf')]])->assertRedirect(route('demandes.index'))->assertSessionHasNoErrors();
    expect(DB::table('demandes')->count())->toBe(2)->and(DB::table('demandes_pieces_jointes')->count())->toBe(2);
});

it('renders administration forms, notifications, profile and request details', function () {
    foreach (['users.index', 'users.create', 'notifications.index', 'profile.edit', 'demandes.index', 'demandes.create'] as $route) {
        $this->get(route($route))->assertOk();
    }
    $this->get(route('users.edit', $this->admin))->assertOk()->assertSee('Modifier un utilisateur');
    $demande = \App\Models\Demande::create(['user_id' => $this->admin->id, 'nom' => 'Test', 'structure' => 'Test', 'objet_modif' => 'Dossier test', 'statut' => 'brouillon']);
    $this->get(route('demandes.show', $demande))->assertOk()->assertSee('Historique des décisions');
    $this->get(route('demandes.edit', $demande))->assertOk()->assertSee('Modifier la demande');
});
