<?php

use App\Models\Demande;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;

uses(Tests\TestCase::class);
require_once __DIR__.'/../Support/WorkspaceDatabase.php';

beforeEach(function () {
    $this->admin = \Tests\Support\resetWorkspaceDatabase();
    $this->owner=User::create(['name'=>'Owner','prenom'=>'Test','email'=>'route-owner@example.invalid','password'=>'test-password']);$this->owner->assignRole('demandeur');
    $this->demande=Demande::create(['user_id'=>$this->owner->id,'nom'=>'Owner','objet_modif'=>'Dossier protégé','statut'=>'soumise']);
});

it('has unique route names and only existing controller methods', function () {
    $names=[];
    foreach (Route::getRoutes() as $route) {
        if ($name=$route->getName()) {
            expect($names)->not->toContain($name);$names[]=$name;
        }
        $controller=$route->getAction('controller');
        if (is_string($controller) && str_contains($controller,'@')) {
            [$class,$method]=explode('@',$controller,2);
            expect(method_exists($class,$method))->toBeTrue("Missing action: $controller");
        }
        if (is_string($controller) && str_starts_with($controller,'App\\Http\\Controllers\\') && ! str_contains($controller,'\\Auth\\')) {
            expect($route->gatherMiddleware())->toContain('auth');
        }
    }
});

it('requires authentication on every private route before processing its input', function () {
    foreach (Route::getRoutes() as $route) {
        if (! in_array('auth',$route->gatherMiddleware(),true) || str_starts_with($route->uri(),'verify-email')) continue;
        $uri=preg_replace_callback('/\{([^}]+)\}/',fn($match)=>$match[1]==='notification' ? '00000000-0000-0000-0000-000000000001' : '1',$route->uri());
        $method=$route->methods()[0];
        $this->call($method,'/'.$uri)->assertRedirect(route('login'));
    }
});

it('restricts every role workspace and admin alias to its intended role', function () {
    $routes=['admin'=>'admin.index','demandeur'=>'dashboard.demandeur','exploitant'=>'dashboard.exploitation','dts'=>'dashboard.dts','structure_specialisee'=>'dashboard.structure','controle_avancee'=>'dashboard.controle','service_technique'=>'dashboard.service','reception'=>'dashboard.reception','chef_structure'=>'dashboard.chef'];
    foreach ($routes as $role=>$name) {
        Role::firstOrCreate(['name'=>$role,'guard_name'=>'web']);
        $user=User::create(['name'=>$role,'prenom'=>'Test','email'=>$role.'-routes@example.invalid','password'=>'test-password']);$user->assignRole($role);
        $this->actingAs($user)->get(route($name))->assertOk();
        foreach ($routes as $other=>$target) {
            if ($other!==$role) $this->get(route($target))->assertForbidden();
        }
    }
    $this->actingAs($this->owner)->get(route('dashboard.admin'))->assertForbidden();
    $this->get(route('admin.demandes.all'))->assertForbidden();
    $this->actingAs($this->admin)->get(route('dashboard.admin'))->assertOk();
    $this->get(route('admin.demandes.all'))->assertOk();
});

it('routes closure lists without confusing the requester control and reception', function () {
    foreach (['controle_avancee','reception'] as $role) {
        Role::firstOrCreate(['name'=>$role,'guard_name'=>'web']);$this->owner->syncRoles([$role]);
        $this->actingAs($this->owner)->get('/demandes/cloture')->assertOk();
    }
    $this->owner->syncRoles(['demandeur']);
    $this->get('/demandes/cloture')->assertOk()->assertSee('Mes réceptions');
    $this->get('/reception/demandes')->assertForbidden();
    $this->actingAs($this->admin)->get('/demandes/cloture')->assertForbidden();
});

it('denies all workflow operations to an unrelated requester including old aliases', function () {
    $actions=['demandes.validerExploitation','demandes.rejeterExploitation','demandes.valider_dts','demandes.rejeter_dts','demandes.validerStructure','demandes.valider_controle','demandes.rejeter_controle','demandes.traiter_agent','demandes.traiter','travaux.terminer','demandes.cloturer','demande.cloture.controle','reception.cloturer','demandes.cloturer_reception','demande.cloture.demandeur','demandes.receptionner','demandes.valider','demandes.rejeter'];
    $this->actingAs($this->owner);
    foreach ($actions as $action) $this->post(route($action,$this->demande),['visa'=>'Test','decision'=>'accord'])->assertForbidden();
    expect($this->demande->fresh()->statut)->toBe('soumise')->and(DB::table('demandes_validations')->count())->toBe(0)->and(DB::table('notifications')->count())->toBe(0);
});

it('retires legacy responsible decisions without changing request status', function () {
    Role::firstOrCreate(['name'=>'responsable_S','guard_name'=>'web']);$this->owner->syncRoles(['responsable_S']);
    $this->actingAs($this->owner)->get(route('responsable.validations'))->assertGone();
    foreach (['demandes.valider','demandes.rejeter'] as $name) $this->post(route($name,$this->demande))->assertGone();
    expect($this->demande->fresh()->statut)->toBe('soumise')->and(DB::table('demandes_validations')->count())->toBe(0);
});

it('blocks accounts without a supported role even when they own an old draft', function () {
    $this->owner->syncRoles([]);$this->demande->update(['statut'=>'brouillon']);
    $this->actingAs($this->owner);
    foreach (['dashboard','demandes.index','demandes.create'] as $name) $this->get(route($name))->assertForbidden();
    $this->get(route('demandes.show',$this->demande))->assertForbidden();
    $this->get(route('demandes.edit',$this->demande))->assertForbidden();
    $this->put(route('demandes.update',$this->demande),[])->assertForbidden();
    $this->delete(route('demandes.destroy',$this->demande))->assertForbidden();
});

it('does not allow public registration to grant an administrative role', function () {
    $payload=['name'=>'Intrus','prenom'=>'Test','email'=>'registration-check@example.invalid','password'=>'test-password','password_confirmation'=>'test-password','role'=>'admin'];
    $this->post(route('register'),$payload)->assertSessionHasErrors('role');
    expect(User::where('email',$payload['email'])->exists())->toBeFalse();
});

it('ignores forged ownership status and role fields in ordinary forms', function () {
    $payload=['structure'=>'Test','unite_concernee'=>'Unité','motif'=>'Motif','repere'=>'R1','fonction'=>'Test','situation_existante'=>'Avant','situation_souhaitee'=>'Après','objet_modif'=>'Brouillon','statut'=>'cloturee_receptionnee','user_id'=>$this->admin->id,'role'=>'admin'];
    $this->actingAs($this->owner)->post(route('demandes.store'),$payload)->assertSessionHasNoErrors();
    $draft=Demande::where('objet_modif','Brouillon')->firstOrFail();
    expect($draft->statut)->toBe('brouillon')->and($draft->user_id)->toBe($this->owner->id);
    $this->put(route('demandes.update',$draft),$payload)->assertSessionHasNoErrors();
    expect($draft->fresh()->statut)->toBe('brouillon')->and($draft->fresh()->user_id)->toBe($this->owner->id);
    $this->patch(route('profile.update'),['name'=>'Owner','prenom'=>'Test','email'=>$this->owner->email,'role'=>'admin','roles'=>['admin']])->assertSessionHasNoErrors();
    expect($this->owner->fresh()->hasRole('admin'))->toBeFalse();
});
