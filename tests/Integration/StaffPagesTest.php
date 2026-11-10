<?php

use App\Models\Demande;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

uses(Tests\TestCase::class);
require_once __DIR__.'/../Support/WorkspaceDatabase.php';

beforeEach(function () {
    $this->admin = \Tests\Support\resetWorkspaceDatabase();
    Role::firstOrCreate(['name' => 'exploitant', 'guard_name' => 'web']);
    $this->staff = User::create(['name' => 'Exploitation', 'prenom' => 'Test', 'email' => 'staff@example.invalid', 'password' => 'test-password']);
    $this->staff->assignRole('exploitant');
    $this->owner = User::create(['name' => 'Demandeur', 'prenom' => 'Test', 'email' => 'author@example.invalid', 'password' => 'test-password']);
    $this->owner->assignRole('demandeur');
    $this->makeDemande = fn (array $extra = []) => Demande::create(array_replace(['user_id'=>$this->owner->id,'nom'=>'Demandeur','objet_modif'=>'Dossier de test','statut'=>'soumise'], $extra));
    $this->actingAs($this->staff);
});

it('shows the exploitation queue, shared pages and scoped statistics', function () {
    ($this->makeDemande)();
    ($this->makeDemande)(['statut'=>'brouillon','objet_modif'=>'Brouillon privé']);
    $this->get(route('dashboard'))->assertOk()->assertSee('Exploitation')->assertViewHas('pending',1)->assertDontSee('Brouillon privé');
    foreach (['staff.demandes','demandes.index_exploitation','dashboard.exploitation','notifications.index','profile.edit'] as $route) {
        $this->get(route($route))->assertOk();
    }
    $this->get(route('demandes.create'))->assertForbidden();
});

it('records one exploitation approval and rejects a repeated decision', function () {
    $demande=($this->makeDemande)();
    $this->get(route('demandes.show',$demande))->assertOk()->assertSee('Avis Exploitation');
    $this->post(route('demandes.validerExploitation',$demande),['visa'=>'Test'])->assertSessionHasNoErrors()->assertRedirect();
    expect($demande->fresh()->statut)->toBe('validee_exploitation')->and(DB::table('demandes_validations')->count())->toBe(1);
    $this->post(route('demandes.validerExploitation',$demande),['visa'=>'Test'])->assertForbidden();
    expect(DB::table('demandes_validations')->count())->toBe(1);
    $this->get(route('staff.demandes',['vue'=>'mes_decisions']))->assertOk()->assertSee('Dossier de test');
});

it('requires a reason for exploitation refusal', function () {
    $demande=($this->makeDemande)();
    $this->post(route('demandes.rejeterExploitation',$demande),['visa'=>'Test'])->assertSessionHasErrors('commentaire');
    expect($demande->fresh()->statut)->toBe('soumise');
    $this->post(route('demandes.rejeterExploitation',$demande),['visa'=>'Test','commentaire'=>'Motif justifié'])->assertSessionHasNoErrors();
    expect($demande->fresh()->statut)->toBe('refusee_exploitation');
});

it('blocks exploitation decisions from other roles and hides drafts', function () {
    $draft=($this->makeDemande)(['statut'=>'brouillon']);
    $this->get(route('demandes.show',$draft))->assertForbidden();
    $this->post(route('demandes.validerExploitation',$draft),['visa'=>'Test'])->assertForbidden();
    $demande=($this->makeDemande)();
    $this->actingAs($this->owner)->post(route('demandes.validerExploitation',$demande),['visa'=>'Test'])->assertForbidden();
    $this->get(route('staff.demandes'))->assertForbidden();
});
