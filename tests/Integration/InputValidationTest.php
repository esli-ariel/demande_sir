<?php

use App\Models\Demande;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

uses(Tests\TestCase::class);
require_once __DIR__.'/../Support/WorkspaceDatabase.php';

beforeEach(function () {
    $this->admin=\Tests\Support\resetWorkspaceDatabase();
    $this->owner=User::create(['name'=>'Owner','prenom'=>'Test','email'=>'input-owner@example.invalid','password'=>'test-password']);$this->owner->assignRole('demandeur');
    $this->payload=['structure'=>'Test','unite_concernee'=>'Unité','objet_modif'=>'Demande','motif'=>'Motif','repere'=>'R1','fonction'=>'Test','situation_existante'=>'Avant','situation_souhaitee'=>'Après'];
    $this->actingAs($this->owner);
});

it('rejects malformed demand payloads before storing requests or files', function (string $field, mixed $value, ?string $error = null) {
    Storage::fake('public');
    $payload=array_replace($this->payload,[$field=>$value]);
    $this->postJson(route('demandes.store'),$payload)->assertUnprocessable()->assertJsonValidationErrors($error ?? $field);
    expect(Demande::count())->toBe(0)->and(DB::table('demandes_pieces_jointes')->count())->toBe(0)->and(DB::table('notifications')->count())->toBe(0);
})->with([
    ['motif',['nested'=>'text']], ['motif',str_repeat('a',10001)],
    ['structures_specialisees','1 OR 1=1'], ['structures_specialisees',[['id'=>1]],'structures_specialisees.0'],
    ['structures_specialisees',['named'=>1]], ['structures_included',['value'=>1]],
    ['pieces_jointes','/etc/passwd'], ['pieces_jointes',['/etc/passwd'],'pieces_jointes.0'],
]);

it('limits upload count and accepts real supported uploads only', function () {
    Storage::fake('public');
    $files=array_map(fn($i)=>UploadedFile::fake()->create("file-$i.pdf",10,'application/pdf'),range(1,11));
    $this->postJson(route('demandes.store'),$this->payload+['pieces_jointes'=>$files])->assertUnprocessable()->assertJsonValidationErrors('pieces_jointes');
    expect(Demande::count())->toBe(0)->and(Storage::disk('public')->allFiles())->toBe([]);
    $this->post(route('demandes.store'),$this->payload+['pieces_jointes'=>[UploadedFile::fake()->create('one.pdf',10,'application/pdf')]])->assertSessionHasNoErrors();
    expect(Demande::count())->toBe(1)->and(DB::table('demandes_pieces_jointes')->count())->toBe(1);
});

it('validates list filters and pagination as scalars before building queries', function () {
    $this->getJson(route('demandes.index',['search'=>['malformed']]))->assertUnprocessable()->assertJsonValidationErrors('search');
    $this->getJson(route('demandeur.receptions',['page'=>['malformed']]))->assertUnprocessable()->assertJsonValidationErrors('page');
    $this->getJson(route('notifications.index',['vue'=>['toutes']]))->assertUnprocessable()->assertJsonValidationErrors('vue');
    $this->actingAs($this->admin)->getJson(route('users.index',['role'=>['admin']]))->assertUnprocessable()->assertJsonValidationErrors('role');
    $this->getJson(route('admin.index',['page'=>100001]))->assertUnprocessable()->assertJsonValidationErrors('page');
});

it('keeps SQL looking search values in bindings and preserves requester ownership scopes', function () {
    $attack="%' OR 1=1 --";
    $own=Demande::create($this->payload+['user_id'=>$this->owner->id,'nom'=>'Owner','statut'=>'soumise']);
    $own->update(['objet_modif'=>$attack]);
    $other=Demande::create($this->payload+['user_id'=>$this->admin->id,'nom'=>'Admin','statut'=>'soumise']);$other->update(['objet_modif'=>$attack]);
    DB::flushQueryLog();DB::enableQueryLog();
    $this->get(route('demandes.index',['search'=>$attack]))->assertOk()->assertViewHas('demandes',fn($rows)=>$rows->total()===1 && $rows->first()->id===$own->id);
    $queries=collect(DB::getQueryLog())->filter(fn($q)=>str_contains(strtolower($q['query']),'like'));
    expect($queries)->not->toBeEmpty();
    foreach ($queries as $query) {
        expect($query['query'])->not->toContain($attack);
        expect($query['bindings'])->toContain('%'.$attack.'%');
    }
    DB::disableQueryLog();
    expect(Demande::count())->toBe(2);
});

it('stores SQL looking descriptions as ordinary data without executing them', function () {
    $value="x'); DROP TABLE demandes; --";
    $this->postJson(route('demandes.store'),array_replace($this->payload,['motif'=>$value]))->assertRedirect();
    expect(Demande::count())->toBe(1)->and(Demande::first()->motif)->toBe($value);
});

it('returns validation errors for malformed password confirmation instead of a server error', function () {
    $this->postJson(route('password.confirm'),['password'=>['malformed']])->assertUnprocessable()->assertJsonValidationErrors('password');
    $this->deleteJson(route('profile.destroy'),['password'=>['malformed']])->assertUnprocessable()->assertJsonValidationErrors('password');
    $this->putJson(route('password.update'),['current_password'=>['malformed'],'password'=>'another-password','password_confirmation'=>'another-password'])->assertUnprocessable()->assertJsonValidationErrors('current_password');
});

it('validates reset tokens and login remember flags before any authentication processing', function () {
    auth()->logout();
    $this->postJson(route('password.store'),['token'=>['malformed'],'email'=>$this->owner->email,'password'=>'another-password','password_confirmation'=>'another-password'])->assertUnprocessable()->assertJsonValidationErrors('token');
    $this->postJson(route('login'),['email'=>$this->owner->email,'password'=>'test-password','remember'=>['malformed']])->assertUnprocessable()->assertJsonValidationErrors('remember');
    $this->assertGuest();
    $this->post(route('login'),['email'=>$this->owner->email,'password'=>'test-password','remember'=>'on'])->assertSessionHasNoErrors();
    $this->assertAuthenticatedAs($this->owner);
});

it('rejects bad role types and nul bytes in passwords before creating accounts', function () {
    $this->actingAs($this->admin)->postJson(route('users.store'),['name'=>'Test','prenom'=>'Test','email'=>'bad-role@example.invalid','password'=>'test-password','password_confirmation'=>'test-password','role'=>['admin']])->assertUnprocessable()->assertJsonValidationErrors('role');
    $this->postJson(route('users.store'),['name'=>'Test','prenom'=>'Test','email'=>'bad-password@example.invalid','password'=>"test\0password",'password_confirmation'=>"test\0password",'role'=>'demandeur'])->assertUnprocessable()->assertJsonValidationErrors('password');
    expect(User::count())->toBe(2);
});

it('rejects malformed dates before changing technical work state or notifying anyone', function () {
    Role::firstOrCreate(['name'=>'service_technique','guard_name'=>'web']);$this->admin->syncRoles(['service_technique']);
    $demande=Demande::create($this->payload+['user_id'=>$this->owner->id,'nom'=>'Owner','statut'=>'validee_controle_avancee']);
    $this->actingAs($this->admin)->postJson(route('demandes.traiter_agent',$demande),['visa'=>'Test','date_debut'=>'11/11/2026'])->assertUnprocessable()->assertJsonValidationErrors('date_debut');
    expect($demande->fresh()->statut)->toBe('validee_controle_avancee')->and(DB::table('notifications')->count())->toBe(0);
});

it('rejects malformed route identifiers before database lookup', function () {
    $this->getJson('/demandes/1%20OR%201=1')->assertNotFound();
    $this->deleteJson('/pieces/1%20OR%201=1')->assertNotFound();
    $this->postJson('/notifications/not-a-uuid/read')->assertNotFound();
});

it('parameterizes admin staff and account searches without interpreting SQL fragments', function () {
    $attack="%' OR 1=1 --";
    $demande=Demande::create($this->payload+['user_id'=>$this->owner->id,'nom'=>'Owner','statut'=>'soumise']);$demande->update(['objet_modif'=>$attack]);
    $this->owner->update(['name'=>$attack]);
    Role::firstOrCreate(['name'=>'exploitant','guard_name'=>'web']);
    $staff=User::create(['name'=>'Staff','prenom'=>'Test','email'=>'bound-staff@example.invalid','password'=>'test-password']);$staff->assignRole('exploitant');
    foreach ([[$this->admin,'admin.index'],[$this->admin,'users.index'],[$staff,'staff.demandes']] as [$actor,$route]) {
        DB::flushQueryLog();DB::enableQueryLog();
        $this->actingAs($actor)->get(route($route,['q'=>$attack]))->assertOk();
        $queries=collect(DB::getQueryLog())->filter(fn($query)=>str_contains(strtolower($query['query']),'like'));
        expect($queries)->not->toBeEmpty();
        foreach ($queries as $query) {
            expect($query['query'])->not->toContain($attack);
            expect($query['bindings'])->toContain('%'.$attack.'%');
        }
        DB::disableQueryLog();
    }
    expect(Demande::count())->toBe(1)->and(User::count())->toBe(3);
});
