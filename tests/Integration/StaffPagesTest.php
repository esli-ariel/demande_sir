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

it('routes DTS approval directly to control when no structures are selected', function () {
    $dts=User::create(['name'=>'DTS','prenom'=>'Test','email'=>'dts-team@example.invalid','password'=>'test-password']);
    $dts->assignRole('dts');
    $this->actingAs($dts);
    $demande=($this->makeDemande)(['statut'=>'validee_exploitation']);
    foreach (['dashboard','dashboard.dts','demandes.index_dts'] as $route) $this->get(route($route))->assertOk();
    $this->post(route('demandes.valider_dts',$demande),['visa'=>'DTS Test'])->assertSessionHasNoErrors();
    expect($demande->fresh()->statut)->toBe('validee_structure_specialisee')->and(DB::table('demandes_validations')->count())->toBe(1);
    $this->post(route('demandes.valider_dts',$demande),['visa'=>'DTS Test'])->assertForbidden();
});

it('records a single DTS decision when several structures are selected', function () {
    $dts=User::create(['name'=>'DTS','prenom'=>'Test','email'=>'dts-team@example.invalid','password'=>'test-password']);
    $dts->assignRole('dts');$this->actingAs($dts);
    $demande=($this->makeDemande)(['statut'=>'validee_exploitation']);
    foreach (['Mécanique','Électricité'] as $name) {
        $id=DB::table('structures_specialisees')->insertGetId(['nom'=>$name]);
        $demande->structuresSpecialisees()->attach($id);
    }
    $this->post(route('demandes.valider_dts',$demande),['visa'=>'DTS Test'])->assertSessionHasNoErrors();
    expect($demande->fresh()->statut)->toBe('validee_dts')->and(DB::table('demandes_validations')->count())->toBe(1);
});

it('requires a DTS refusal reason and rejects decisions at the wrong stage', function () {
    $dts=User::create(['name'=>'DTS','prenom'=>'Test','email'=>'dts-team@example.invalid','password'=>'test-password']);
    $dts->assignRole('dts');$this->actingAs($dts);
    $demande=($this->makeDemande)(['statut'=>'validee_exploitation']);
    $this->post(route('demandes.rejeter_dts',$demande),['visa'=>'Test'])->assertSessionHasErrors('commentaire');
    $this->post(route('demandes.rejeter_dts',$demande),['visa'=>'Test','commentaire'=>'Motif DTS'])->assertSessionHasNoErrors();
    expect($demande->fresh()->statut)->toBe('refusee_dts');
    $this->post(route('demandes.valider_dts',$demande),['visa'=>'Test'])->assertForbidden();
    $this->actingAs($this->staff)->post(route('demandes.valider_dts',($this->makeDemande)(['statut'=>'validee_exploitation'])),['visa'=>'Test'])->assertForbidden();
});

it('restricts specialist queues and decisions to explicitly assigned structures', function () {
    Role::firstOrCreate(['name'=>'structure_specialisee','guard_name'=>'web']);
    $specialist=User::create(['name'=>'Spécialiste','prenom'=>'Test','email'=>'specialist@example.invalid','password'=>'test-password']);
    $specialist->assignRole('structure_specialisee');
    $mine=DB::table('structures_specialisees')->insertGetId(['nom'=>'Ma structure']);
    $other=DB::table('structures_specialisees')->insertGetId(['nom'=>'Autre structure']);
    $specialist->structuresSpecialisees()->attach($mine);
    $assigned=($this->makeDemande)(['statut'=>'validee_dts','objet_modif'=>'Dossier affecté']);$assigned->structuresSpecialisees()->attach([$mine,$other]);
    $unassigned=($this->makeDemande)(['statut'=>'validee_dts','objet_modif'=>'Dossier non affecté']);$unassigned->structuresSpecialisees()->attach($other);
    $this->actingAs($specialist);
    $this->get(route('dashboard'))->assertOk()->assertViewHas('pending',1)->assertSee('Dossier affecté')->assertDontSee('Dossier non affecté');
    $this->get(route('demandes.show',$unassigned))->assertForbidden();
    $this->post(route('demandes.validerStructure',$assigned),['decision'=>'accord','structure_id'=>$other,'visa'=>'Test'])->assertForbidden();
    $this->post(route('demandes.validerStructure',$assigned),['decision'=>'accord','structure_id'=>$mine,'visa'=>'Test'])->assertSessionHasNoErrors();
    expect($assigned->fresh()->statut)->toBe('validee_dts')->and(DB::table('demandes_validations')->count())->toBe(1);
    $this->get(route('dashboard'))->assertOk()->assertViewHas('pending',0);
    $this->post(route('demandes.validerStructure',$assigned),['decision'=>'accord','structure_id'=>$mine,'visa'=>'Test'])->assertForbidden();
    $specialist->structuresSpecialisees()->attach($other);
    $this->post(route('demandes.validerStructure',$assigned),['decision'=>'accord','structure_id'=>$other,'visa'=>'Test'])->assertSessionHasNoErrors();
    expect($assigned->fresh()->statut)->toBe('validee_structure_specialisee')->and(DB::table('demandes_validations')->count())->toBe(2);
});

it('rejects a whole request when an assigned structure refuses with a reason', function () {
    Role::firstOrCreate(['name'=>'structure_specialisee','guard_name'=>'web']);
    $specialist=User::create(['name'=>'Spécialiste','prenom'=>'Test','email'=>'specialist@example.invalid','password'=>'test-password']);$specialist->assignRole('structure_specialisee');
    $id=DB::table('structures_specialisees')->insertGetId(['nom'=>'Structure']);$specialist->structuresSpecialisees()->attach($id);
    $demande=($this->makeDemande)(['statut'=>'validee_dts']);$demande->structuresSpecialisees()->attach($id);$this->actingAs($specialist);
    $this->post(route('demandes.validerStructure',$demande),['decision'=>'refus','structure_id'=>$id,'visa'=>'Test'])->assertSessionHasErrors('commentaire');
    $this->post(route('demandes.validerStructure',$demande),['decision'=>'refus','structure_id'=>$id,'visa'=>'Test','commentaire'=>'Motif structure'])->assertSessionHasNoErrors();
    expect($demande->fresh()->statut)->toBe('refusee_structure_specialisee');
});

it('requires an explicit structure assignment when creating specialist accounts', function () {
    Role::firstOrCreate(['name'=>'structure_specialisee','guard_name'=>'web']);$this->actingAs($this->admin);
    $payload=['name'=>'Nouveau','prenom'=>'Test','email'=>'new-specialist@example.invalid','password'=>'test-password','password_confirmation'=>'test-password','role'=>'structure_specialisee'];
    $this->post(route('users.store'),$payload)->assertSessionHasErrors('structure_ids');
    $id=DB::table('structures_specialisees')->insertGetId(['nom'=>'Structure']);
    $this->post(route('users.store'),$payload+['structure_ids'=>[$id]])->assertSessionHasNoErrors()->assertRedirect(route('users.index'));
    expect(User::where('email',$payload['email'])->firstOrFail()->structuresSpecialisees()->count())->toBe(1);
});

it('approves control once, assigns a missing DMA number and notifies its author', function () {
    Role::firstOrCreate(['name'=>'controle_avancee','guard_name'=>'web']);$this->staff->syncRoles(['controle_avancee']);$this->staff->unsetRelation('roles');
    $demande=($this->makeDemande)(['statut'=>'validee_structure_specialisee']);
    foreach (['dashboard','dashboard.controle','demandes.index_controle'] as $route) $this->get(route($route))->assertOk();
    $this->post(route('demandes.valider_controle',$demande),['visa'=>'Contrôle'])->assertSessionHasNoErrors();
    expect($demande->fresh()->statut)->toBe('validee_controle_avancee')->and($demande->fresh()->numero_dma)->toStartWith('DMA-')->and($this->owner->unreadNotifications()->count())->toBe(1);
    $this->post(route('demandes.valider_controle',$demande),['visa'=>'Contrôle'])->assertForbidden();
});

it('requires a control refusal reason and preserves existing DMA numbers', function () {
    Role::firstOrCreate(['name'=>'controle_avancee','guard_name'=>'web']);$this->staff->syncRoles(['controle_avancee']);$this->staff->unsetRelation('roles');
    $demande=($this->makeDemande)(['statut'=>'validee_structure_specialisee','numero_dma'=>'MS2026/0001']);
    $this->post(route('demandes.rejeter_controle',$demande),['visa'=>'Test'])->assertSessionHasErrors('commentaire');
    $this->post(route('demandes.valider_controle',$demande),['visa'=>'Test'])->assertSessionHasNoErrors();
    expect($demande->fresh()->numero_dma)->toBe('MS2026/0001');
});

it('checks completed works without bypassing final reception', function () {
    Role::firstOrCreate(['name'=>'controle_avancee','guard_name'=>'web']);$this->staff->syncRoles(['controle_avancee']);$this->staff->unsetRelation('roles');
    $demande=($this->makeDemande)(['statut'=>'terminee_agent']);
    $this->get(route('demandes.show',$demande))->assertOk()->assertSee('Transmettre au demandeur');
    $this->post(route('demandes.cloturer',$demande),['visa'=>'Test'])->assertSessionHasErrors('analyse');
    $this->post(route('demandes.cloturer',$demande),['visa'=>'Test','analyse'=>'Travaux conformes'])->assertSessionHasNoErrors();
    expect($demande->fresh()->statut)->toBe('cloturee')->and(DB::table('demandes_validations')->where('commentaire','Travaux conformes')->count())->toBe(1);
    $this->post(route('demandes.cloturer',$demande),['visa'=>'Test','analyse'=>'Doublon'])->assertForbidden();
});

it('runs technical work in two audited steps with coherent dates', function () {
    Role::firstOrCreate(['name'=>'service_technique','guard_name'=>'web']);
    $this->staff->syncRoles(['service_technique']);
    $demande=($this->makeDemande)(['statut'=>'validee_controle_avancee']);
    foreach (['dashboard','dashboard.service','demandes.index_service','service.executions'] as $route) $this->get(route($route))->assertOk();
    $this->get(route('demandes.show',$demande))->assertOk()->assertSee('Démarrer les travaux');
    $this->post(route('travaux.terminer',$demande),['visa'=>'Test'])->assertForbidden();
    $this->post(route('demandes.traiter_agent',$demande),['visa'=>'Test'])->assertSessionHasErrors('date_debut');
    $this->post(route('demandes.traiter_agent',$demande),['visa'=>'Test','date_debut'=>'2026-11-10'])->assertSessionHasNoErrors();
    expect($demande->fresh()->statut)->toBe('en_cours_traitement');
    $this->post(route('demandes.traiter_agent',$demande),['visa'=>'Test','date_debut'=>'2026-11-10'])->assertForbidden();
    $this->get(route('demandes.show',$demande))->assertOk()->assertSee('Terminer les travaux');
    $data=['visa'=>'Test','date_fin'=>'2026-11-09','travaux_realises'=>'Modification effectuée'];
    $this->post(route('travaux.terminer',$demande),$data)->assertSessionHasErrors('date_fin');
    $data['date_fin']='2026-11-11';
    $this->post(route('travaux.terminer',$demande),$data)->assertSessionHasNoErrors();
    expect($demande->fresh()->statut)->toBe('terminee_agent')->and($demande->fresh()->travaux_realises)->toBe('Modification effectuée')->and(DB::table('demandes_validations')->count())->toBe(2)->and($this->owner->notifications()->count())->toBe(2);
    $this->post(route('travaux.terminer',$demande),$data)->assertForbidden();
    $this->actingAs($this->owner)->post(route('demandes.traiter_agent',($this->makeDemande)(['statut'=>'validee_controle_avancee'])),['visa'=>'Test','date_debut'=>'2026-11-10'])->assertForbidden();
});

it('shows the chief only requests already approved by exploitation without decision controls', function () {
    Role::firstOrCreate(['name'=>'chef_structure','guard_name'=>'web']);$this->staff->syncRoles(['chef_structure']);
    $hidden=($this->makeDemande)(['objet_modif'=>'Avant exploitation']);
    ($this->makeDemande)(['statut'=>'refusee_exploitation','objet_modif'=>'Refus exploitation privé']);
    $visible=($this->makeDemande)(['statut'=>'validee_exploitation','objet_modif'=>'Après exploitation']);
    ($this->makeDemande)(['statut'=>'cloturee_receptionnee']);
    ($this->makeDemande)(['statut'=>'refusee_dts']);
    $this->get(route('dashboard'))->assertOk()->assertViewHas('total',3)->assertViewHas('pending',1)->assertViewHas('processed',1)->assertViewHas('refused',1)->assertSee('Chef de structure')->assertDontSee('Mes décisions');
    $this->get(route('dashboard.chef'))->assertOk();
    $this->get(route('staff.demandes',['vue'=>'tous']))->assertOk()->assertSee('Après exploitation')->assertDontSee('Avant exploitation')->assertDontSee('Refus exploitation privé');
    $this->get(route('demandes.show',$hidden))->assertForbidden();
    $this->get(route('demandes.show',$visible))->assertOk()->assertDontSee('Accorder')->assertDontSee('Refuser');
    $this->post(route('demandes.valider_dts',$visible),['visa'=>'Test'])->assertForbidden();
    $this->post(route('demandes.traiter_agent',$visible),['visa'=>'Test'])->assertForbidden();
    $this->get(route('staff.demandes',['vue'=>'mes_decisions']))->assertOk()->assertViewHas('demandes',fn($items)=>$items->total()===2);
});

it('keeps reception staff in read-only follow-up without final closure rights', function () {
    Role::firstOrCreate(['name'=>'reception','guard_name'=>'web']);$this->staff->syncRoles(['reception']);
    $early=($this->makeDemande)(['statut'=>'en_cours_traitement','objet_modif'=>'Travaux non terminés']);
    $done=($this->makeDemande)(['statut'=>'terminee_agent','objet_modif'=>'Travaux réceptionnables']);
    foreach (['dashboard','dashboard.reception','notifications.index','profile.edit'] as $route) $this->get(route($route))->assertOk();
    $this->get(route('staff.demandes'))->assertOk()->assertSee('Travaux réceptionnables')->assertDontSee('Travaux non terminés');
    $this->get(route('demandes.show',$early))->assertForbidden();
    $this->get(route('demandes.show',$done))->assertOk()->assertDontSee('Clôturer définitivement la DMA')->assertDontSee('Réceptionner et clôturer ma DMA');
    foreach (['reception.cloturer','demandes.cloturer_reception','demandes.receptionner'] as $route) {
        $this->post(route($route,$done),['visa'=>'Test','visa_demandeur'=>'Test','confirmation_reception'=>1])->assertForbidden();
    }
    expect($done->fresh()->statut)->toBe('terminee_agent')->and(DB::table('demandes_validations')->count())->toBe(0);
});

it('carries one DMA through the entire approval work and final reception circuit', function () {
    $accounts=['exploitant'=>$this->staff];
    foreach (['dts','structure_specialisee','controle_avancee','service_technique','reception'] as $role) {
        Role::firstOrCreate(['name'=>$role,'guard_name'=>'web']);
        $account=User::create(['name'=>$role,'prenom'=>'Test','email'=>$role.'-circuit@example.invalid','password'=>'test-password']);$account->assignRole($role);$accounts[$role]=$account;
    }
    $structure=DB::table('structures_specialisees')->insertGetId(['nom'=>'Structure circuit']);
    $accounts['structure_specialisee']->structuresSpecialisees()->attach($structure);
    $demande=($this->makeDemande)();$demande->structuresSpecialisees()->attach($structure);
    foreach ([['exploitant','demandes.validerExploitation','validee_exploitation'],['dts','demandes.valider_dts','validee_dts'],['structure_specialisee','demandes.validerStructure','validee_structure_specialisee'],['controle_avancee','demandes.valider_controle','validee_controle_avancee']] as [$role,$route,$status]) {
        $this->actingAs($accounts[$role])->post(route($route,$demande),['visa'=>'Circuit','decision'=>'accord','structure_id'=>$structure])->assertSessionHasNoErrors()->assertRedirect();
        expect($demande->fresh()->statut)->toBe($status);
    }
    $this->actingAs($accounts['service_technique'])->post(route('demandes.traiter_agent',$demande),['visa'=>'Circuit','date_debut'=>'2026-11-10'])->assertSessionHasNoErrors();
    $this->post(route('travaux.terminer',$demande),['visa'=>'Circuit','date_fin'=>'2026-11-11','travaux_realises'=>'Modification du circuit'])->assertSessionHasNoErrors();
    expect($demande->fresh()->statut)->toBe('terminee_agent');
    $this->actingAs($this->owner)->post(route('demandes.receptionner',$demande),['visa_demandeur'=>'Circuit','confirmation_reception'=>1,'commentaire'=>'Conforme'])->assertSessionHasNoErrors();
    expect($demande->fresh()->statut)->toBe('cloturee_receptionnee')->and(DB::table('demandes_validations')->where('demande_id',$demande->id)->count())->toBe(7);
    $this->actingAs($this->owner)->get(route('demandes.show',$demande))->assertOk()->assertSee('Clôturée et réceptionnée')->assertSee('Modification du circuit');
});
