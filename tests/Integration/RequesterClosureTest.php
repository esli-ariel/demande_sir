<?php

use App\Models\Demande;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

uses(Tests\TestCase::class);
require_once __DIR__.'/../Support/WorkspaceDatabase.php';

beforeEach(function () {
    $this->admin=\Tests\Support\resetWorkspaceDatabase();
    $this->owner=User::create(['name'=>'Propriétaire','prenom'=>'Test','email'=>'closure-owner@example.invalid','password'=>'test-password']);
    $this->owner->assignRole('demandeur');
    $this->makeDemande=fn(array $extra=[])=>Demande::create(array_replace(['user_id'=>$this->owner->id,'nom'=>'Test','objet_modif'=>'Ma DMA à réceptionner','statut'=>'terminee_agent'],$extra));
    $this->payload=['visa_demandeur'=>'Visa propriétaire','confirmation_reception'=>1,'commentaire'=>'Travaux reçus et vérifiés'];
});

it('requires an explicit confirmation and bounded typed fields without writing any decision', function () {
    $demande=($this->makeDemande)();
    $this->actingAs($this->owner);
    foreach ([[],['visa_demandeur'=>'Visa'],['visa_demandeur'=>'Visa','confirmation_reception'=>0],['visa_demandeur'=>['bad'],'confirmation_reception'=>1],['visa_demandeur'=>str_repeat('x',256),'confirmation_reception'=>1],['visa_demandeur'=>'Visa','confirmation_reception'=>1,'commentaire'=>['bad']],['visa_demandeur'=>'Visa','confirmation_reception'=>1,'commentaire'=>str_repeat('x',5001)]] as $data) {
        $this->postJson(route('demandes.receptionner',$demande),$data)->assertUnprocessable();
    }
    expect($demande->fresh()->statut)->toBe('terminee_agent')->and($demande->fresh()->closed_at)->toBeNull()
        ->and(DB::table('demandes_validations')->count())->toBe(0)->and(DB::table('notifications')->count())->toBe(0);
    $this->from(route('demandeur.receptions'))->post(route('demandes.receptionner',$demande),['_reception_demande_id'=>['bad'],'visa_demandeur'=>['bad']])->assertSessionHasErrors();
    $this->get(route('demandeur.receptions'))->assertOk();
});

it('denies every other role and another requester regardless of forged owner fields', function () {
    $demande=($this->makeDemande)();
    foreach (['admin','demandeur','exploitant','dts','structure_specialisee','controle_avancee','service_technique','chef_structure','reception'] as $role) {
        Role::firstOrCreate(['name'=>$role,'guard_name'=>'web']);
        $user=User::create(['name'=>$role,'prenom'=>'Test','email'=>$role.'-closure-denied@example.invalid','password'=>'test-password']);$user->assignRole($role);
        $this->actingAs($user)->post(route('demandes.receptionner',$demande),$this->payload+['user_id'=>$user->id])->assertForbidden();
    }
    expect($demande->fresh()->statut)->toBe('terminee_agent')->and(DB::table('demandes_validations')->count())->toBe(0);
});

it('rejects premature or repeated closure including drafts refused and already archived dossiers', function () {
    $this->actingAs($this->owner);
    foreach (['brouillon','soumise','validee_exploitation','validee_dts','validee_structure_specialisee','validee_controle_avancee','en_cours_traitement','refusee_dts','cloturee_receptionnee'] as $status) {
        $demande=($this->makeDemande)(['statut'=>$status]);
        $this->post(route('demandes.receptionner',$demande),$this->payload)->assertForbidden();
        expect($demande->fresh()->statut)->toBe($status)->and($demande->fresh()->closed_at)->toBeNull();
    }
    expect(DB::table('demandes_validations')->count())->toBe(0);
});

it('closes eligible owner dossiers through both aliases with an immutable server timestamp and one audit decision', function (string $status, string $route) {
    $demande=($this->makeDemande)(['statut'=>$status]);
    $this->travelTo(\Carbon\Carbon::parse('2026-11-11 18:23:45'));
    $this->actingAs($this->owner)->post(route($route,$demande),$this->payload+['closed_at'=>'2000-01-01','user_id'=>$this->admin->id])->assertRedirect(route('demandeur.receptions'))->assertSessionHasNoErrors();
    $decision=DB::table('demandes_validations')->where('demande_id',$demande->id)->first();
    expect($demande->fresh()->statut)->toBe('cloturee_receptionnee')->and($demande->fresh()->user_id)->toBe($this->owner->id)
        ->and($demande->fresh()->closed_at->format('Y-m-d H:i:s'))->toBe('2026-11-11 18:23:45')
        ->and($decision->role)->toBe('demandeur')->and($decision->user_id)->toBe($this->owner->id)->and($decision->visa)->toBe('Visa propriétaire');
    $this->get(route('demandeur.receptions'))->assertOk()->assertDontSee('Ma DMA à réceptionner');
    $this->get(route('archives.index'))->assertOk()->assertSee('Ma DMA à réceptionner');
    $this->travel(1)->days();
    $this->post(route($route,$demande),$this->payload)->assertForbidden();
    expect(DB::table('demandes_validations')->where('demande_id',$demande->id)->count())->toBe(1)
        ->and($demande->fresh()->closed_at->format('Y-m-d H:i:s'))->toBe('2026-11-11 18:23:45');
    $this->travelBack();
})->with([['terminee_agent','demandes.receptionner'],['cloturee','demande.cloture.demandeur']]);

it('rolls back the closure date and decision if notification persistence fails', function () {
    $demande=($this->makeDemande)();
    $this->app->instance(\App\Services\DmaNotifications::class,new class {
        public function transition(...$args): void { throw new RuntimeException('Simulated notification failure'); }
    });
    $this->actingAs($this->owner)->postJson(route('demandes.receptionner',$demande),$this->payload)->assertStatus(500);
    expect($demande->fresh()->statut)->toBe('terminee_agent')->and($demande->fresh()->closed_at)->toBeNull()
        ->and(DB::table('demandes_validations')->count())->toBe(0);
});
