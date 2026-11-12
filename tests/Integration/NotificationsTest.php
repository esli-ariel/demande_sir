<?php

use App\Models\Demande;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

uses(Tests\TestCase::class);
require_once __DIR__.'/../Support/WorkspaceDatabase.php';

beforeEach(function () {
    $this->admin = \Tests\Support\resetWorkspaceDatabase();
    $this->account = function (string $name, array $roles): User {
        foreach ($roles as $role) Role::firstOrCreate(['name'=>$role,'guard_name'=>'web']);
        $user = User::create(['name'=>$name,'prenom'=>'Test','email'=>$name.'@example.invalid','password'=>'test-password']);
        $user->syncRoles($roles);
        return $user;
    };
    $this->owner = ($this->account)('owner',['demandeur']);
    $this->makeDemande = fn(array $extra=[]) => Demande::create(array_replace(['user_id'=>$this->owner->id,'nom'=>'Test','statut'=>'soumise','objet_modif'=>'Notification circuit','structure'=>'Unité test','unite_concernee'=>'Unité','repere'=>'R1','fonction'=>'Test','motif'=>'Motif','situation_existante'=>'Avant','situation_souhaitee'=>'Après'],$extra));
});

it('alerts exploitation once when a draft is submitted without notifying the sender', function () {
    $a=($this->account)('exploitation-a',['exploitant']);$b=($this->account)('exploitation-b',['exploitant']);
    $other=($this->account)('unrelated',['demandeur']);
    $demande=($this->makeDemande)(['statut'=>'brouillon']);
    $this->actingAs($this->owner)->post(route('demandes.submit',$demande))->assertSessionHasNoErrors();
    expect($a->notifications()->count())->toBe(1)->and($b->notifications()->count())->toBe(1)->and($other->notifications()->count())->toBe(0)->and($this->owner->notifications()->count())->toBe(0);
    $this->post(route('demandes.submit',$demande))->assertForbidden();
    expect($a->notifications()->count())->toBe(1);
    $this->actingAs($a)->get(route('notifications.index'))->assertOk()->assertSee('Une DMA attend votre intervention')->assertSee('Notification circuit');
});

it('notifies DTS and chiefs after exploitation while deduplicating recipients', function () {
    $actor=($this->account)('exploitation',['exploitant']);$dts=($this->account)('dts-chief',['dts','chef_structure']);$chief=($this->account)('chief',['chef_structure']);
    $reception=($this->account)('reception',['reception']);$demande=($this->makeDemande)();
    $this->actingAs($actor)->post(route('demandes.validerExploitation',$demande),['visa'=>'Visa'])->assertSessionHasNoErrors();
    expect($dts->notifications()->count())->toBe(1)->and($chief->notifications()->count())->toBe(1)->and($this->owner->notifications()->count())->toBe(1)->and($actor->notifications()->count())->toBe(0)->and($reception->notifications()->count())->toBe(0);
    expect($chief->notifications()->first()->data['message'])->toBe('Une DMA entre dans votre suivi');
});

it('targets only selected structures and forwards to control after all selected approvals', function () {
    $dts=($this->account)('dts',['dts']);$control=($this->account)('control',['controle_avancee']);
    $a=($this->account)('specialist-a',['structure_specialisee']);$b=($this->account)('specialist-b',['structure_specialisee']);$other=($this->account)('specialist-other',['structure_specialisee']);
    $ids=[];
    foreach ([$a,$b,$other] as $user) {
        $id=DB::table('structures_specialisees')->insertGetId(['nom'=>$user->name]);$user->structuresSpecialisees()->attach($id);$ids[]=$id;
    }
    $demande=($this->makeDemande)(['statut'=>'validee_exploitation']);$demande->structuresSpecialisees()->attach([$ids[0],$ids[1]]);
    $this->actingAs($dts)->post(route('demandes.valider_dts',$demande),['visa'=>'Visa'])->assertSessionHasNoErrors();
    expect($a->notifications()->count())->toBe(1)->and($b->notifications()->count())->toBe(1)->and($other->notifications()->count())->toBe(0)->and($control->notifications()->count())->toBe(0);
    $this->actingAs($a)->post(route('demandes.validerStructure',$demande),['visa'=>'Visa','decision'=>'accord','structure_id'=>$ids[0]])->assertSessionHasNoErrors();
    expect($this->owner->notifications()->count())->toBe(2)->and($b->notifications()->count())->toBe(1)->and($control->notifications()->count())->toBe(0);
    $this->actingAs($b)->post(route('demandes.validerStructure',$demande),['visa'=>'Visa','decision'=>'accord','structure_id'=>$ids[1]])->assertSessionHasNoErrors();
    expect($control->notifications()->count())->toBe(1)->and($this->owner->notifications()->count())->toBe(3);
    $this->post(route('demandes.validerStructure',$demande),['visa'=>'Visa','decision'=>'accord','structure_id'=>$ids[1]])->assertForbidden();
    expect($control->notifications()->count())->toBe(1);
});

it('reports refusal to the requester without alerting the next team', function () {
    $actor=($this->account)('exploitation',['exploitant']);$dts=($this->account)('dts',['dts']);$demande=($this->makeDemande)();
    $this->actingAs($actor)->post(route('demandes.rejeterExploitation',$demande),['visa'=>'Visa','commentaire'=>'À revoir'])->assertSessionHasNoErrors();
    expect($this->owner->notifications()->first()->data['commentaire'])->toBe('À revoir')->and($dts->notifications()->count())->toBe(0);
});

it('invites the owner to close after work and informs teams when the owner confirms', function () {
    $service=($this->account)('service',['service_technique']);$control=($this->account)('control',['controle_avancee']);$reception=($this->account)('reception',['reception']);
    $demande=($this->makeDemande)(['statut'=>'en_cours_traitement','date_debut'=>'2026-11-10']);
    $this->actingAs($service)->post(route('travaux.terminer',$demande),['visa'=>'Visa','date_fin'=>'2026-11-11','travaux_realises'=>'Modification terminée'])->assertSessionHasNoErrors();
    expect($control->notifications()->count())->toBe(1)->and($reception->notifications()->count())->toBe(1)->and($this->owner->notifications()->count())->toBe(1);
    $this->actingAs($this->owner)->post(route('demandes.receptionner',$demande),['visa_demandeur'=>'Visa','confirmation_reception'=>1])->assertSessionHasNoErrors();
    expect($this->owner->notifications()->count())->toBe(1)->and($control->notifications()->count())->toBe(2)->and($reception->notifications()->count())->toBe(2)->and($service->notifications()->count())->toBe(1);
    expect($this->owner->notifications()->first()->data['message'])->toBe('Confirmez la réception pour clôturer votre DMA');
    expect($service->notifications()->first()->data['statut'])->toBe('cloturee_receptionnee');
});

it('scopes filters counters read all and opening notifications to the signed in user', function () {
    $demande=($this->makeDemande)();
    $ids=[];
    foreach ([$this->owner,$this->owner,$this->admin] as $user) {
        $id=(string)Str::uuid();$ids[]=$id;
        DB::table('notifications')->insert(['id'=>$id,'type'=>'test','notifiable_type'=>User::class,'notifiable_id'=>$user->id,'data'=>json_encode(['demande_id'=>$demande->id,'message'=>'Notification personnelle']),'created_at'=>now(),'updated_at'=>now()]);
    }
    $this->actingAs($this->owner)->getJson(route('notifications.summary'))->assertOk()->assertJson(['unread'=>2]);
    $this->get(route('notifications.index',['vue'=>'non_lues']))->assertOk()->assertViewHas('notifications',fn($rows)=>$rows->total()===2);
    $this->post(route('notifications.open',$ids[2]))->assertNotFound();
    $this->post(route('notifications.open',$ids[0]))->assertRedirect(route('demandes.show',$demande));
    $this->getJson(route('notifications.summary'))->assertJson(['unread'=>1]);
    $this->post(route('notifications.read_all'))->assertRedirect();
    $this->getJson(route('notifications.summary'))->assertJson(['unread'=>0]);
    $this->get(route('notifications.index',['vue'=>'non_lues']))->assertOk()->assertSee('Vous êtes à jour');
    expect($this->admin->unreadNotifications()->count())->toBe(1);
});

it('checks current request access before opening and rejects unauthenticated counters', function () {
    $this->getJson(route('notifications.summary'))->assertUnauthorized();
    $other=($this->account)('other',['demandeur']);$demande=($this->makeDemande)();$id=(string)Str::uuid();
    DB::table('notifications')->insert(['id'=>$id,'type'=>'test','notifiable_type'=>User::class,'notifiable_id'=>$other->id,'data'=>json_encode(['demande_id'=>$demande->id]),'created_at'=>now(),'updated_at'=>now()]);
    $this->actingAs($other)->post(route('notifications.open',$id))->assertForbidden();
    expect($other->unreadNotifications()->count())->toBe(1);
});
