<?php

use App\Models\Demande;
use App\Models\DemandeValidation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

uses(Tests\TestCase::class);
require_once __DIR__.'/../Support/WorkspaceDatabase.php';

beforeEach(function () {
    $this->admin = \Tests\Support\resetWorkspaceDatabase();
    $this->owner = User::create(['name'=>'Demandeur Archive','prenom'=>'Alex','email'=>'archive-owner@example.invalid','password'=>'test-password']);
    $this->owner->assignRole('demandeur');
    $this->makeDemande = fn (array $extra = []) => Demande::create(array_replace([
        'user_id'=>$this->owner->id, 'nom'=>'Nom historique','objet_modif'=>'Modernisation pompe',
        'unite_concernee'=>'Unité A','statut'=>'cloturee_receptionnee','closed_at'=>'2025-06-15 14:35:00',
    ],$extra));
});

it('lists only final receptions and keeps unknown historical dates visible without inventing them', function () {
    $closed=($this->makeDemande)(['numero_dma'=>'MS2025/0001']);
    $legacy=($this->makeDemande)(['objet_modif'=>'Archive ancienne','closed_at'=>null]);
    foreach (['brouillon','soumise','terminee_agent','cloturee','refusee_dts'] as $status) {
        ($this->makeDemande)(['statut'=>$status,'closed_at'=>null,'objet_modif'=>'Exclu '.$status]);
    }
    $this->actingAs($this->admin)->get(route('archives.index'))->assertOk()
        ->assertSee('Archives des demandes')->assertSee('MS2025/0001')->assertSee('15/06/2025 à 14:35')
        ->assertSee('Archive ancienne')->assertSee('Date non renseignée')->assertDontSee('Exclu ')
        ->assertViewHas('demandes',fn($rows)=>$rows->total()===2 && $rows->modelKeys()===[$closed->id,$legacy->id]);
    expect($legacy->fresh()->closed_at)->toBeNull();
});

it('applies number object author unit year and inclusive closure-date filters', function () {
    $target=($this->makeDemande)(['numero_dma'=>'MS2025/0042']);
    ($this->makeDemande)(['user_id'=>$this->admin->id,'objet_modif'=>'Autre unité','unite_concernee'=>'Unité B','closed_at'=>'2024-03-10 00:00:00']);
    $this->actingAs($this->admin);
    foreach ([['q'=>'MS2025/0042'],['q'=>'Modernisation'],['q'=>'Alex'],['unite'=>'Unité A'],['annee'=>'2025'],['du'=>'2025-06-15','au'=>'2025-06-15'],['au'=>'2025-06-15','unite'=>'Unité A'],['du'=>'2025-01-01']] as $filters) {
        $this->get(route('archives.index',$filters))->assertOk()->assertViewHas('demandes',fn($rows)=>$rows->total()===1 && $rows->first()->is($target));
    }
    $this->get(route('archives.index',['annee'=>'2023']))->assertOk()->assertSee('Aucune demande clôturée');
    $this->get(route('archives.index',['au'=>'2025-06-14','unite'=>'Unité A']))->assertOk()->assertViewHas('demandes',fn($rows)=>$rows->total()===0);
});

it('never leaks another requesters archives counts years or detail through searches', function () {
    $own=($this->makeDemande)();
    $other=($this->makeDemande)(['user_id'=>$this->admin->id,'objet_modif'=>'Dossier confidentiel autre','closed_at'=>'2022-01-01 00:00:00']);
    $this->actingAs($this->owner)->get(route('archives.index'))->assertOk()->assertDontSee('Dossier confidentiel autre')
        ->assertViewHas('demandes',fn($rows)=>$rows->total()===1)->assertViewHas('years',fn($years)=>$years->all()===['2025']);
    $this->get(route('archives.index',['q'=>'confidentiel']))->assertOk()->assertViewHas('demandes',fn($rows)=>$rows->total()===0);
    $this->get(route('archives.show',$other))->assertNotFound();
    $this->get(route('archives.show',$own))->assertOk();
});

it('retains the selected-structure perimeter for specialist archives including detail access', function () {
    Role::firstOrCreate(['name'=>'structure_specialisee','guard_name'=>'web']);
    $specialist=User::create(['name'=>'Spécialiste','prenom'=>'Test','email'=>'archive-specialist@example.invalid','password'=>'test-password']);
    $specialist->assignRole('structure_specialisee');
    $structure=DB::table('structures_specialisees')->insertGetId(['nom'=>'Structure A']);
    $specialist->structuresSpecialisees()->attach($structure);
    $own=($this->makeDemande)();$own->structuresSpecialisees()->attach($structure);
    $other=($this->makeDemande)(['objet_modif'=>'Hors périmètre','closed_at'=>'2021-01-01 00:00:00']);
    $this->actingAs($specialist)->get(route('archives.index'))->assertOk()->assertDontSee('Hors périmètre')
        ->assertViewHas('years',fn($years)=>$years->all()===['2025']);
    $this->get(route('archives.show',$own))->assertOk();
    $this->get(route('archives.show',$other))->assertNotFound();
});

it('provides archives to all remaining supported roles and denies unsupported roles and guests', function () {
    $closed=($this->makeDemande)();
    $this->get(route('archives.index'))->assertRedirect(route('login'));
    foreach (['exploitant','dts','controle_avancee','service_technique','chef_structure','reception'] as $role) {
        Role::firstOrCreate(['name'=>$role,'guard_name'=>'web']);
        $user=User::create(['name'=>$role,'prenom'=>'Test','email'=>$role.'-archive@example.invalid','password'=>'test-password']);
        $user->assignRole($role);
        $this->actingAs($user)->get(route('archives.index'))->assertOk()->assertViewHas('demandes',fn($rows)=>$rows->total()===1);
        $this->get(route('archives.show',$closed))->assertOk();
    }
    $user->syncRoles([]);
    $this->get(route('archives.index'))->assertForbidden();
    $this->get(route('archives.show',$closed))->assertForbidden();
});

it('opens the complete dossier read-only and retains the return filters', function () {
    $closed=($this->makeDemande)(['travaux_realises'=>'Compte rendu vérifié']);
    DemandeValidation::create(['demande_id'=>$closed->id,'user_id'=>$this->admin->id,'role'=>'reception','decision'=>'accord','visa'=>'Visa final','commentaire'=>'Réception conforme','date_validation'=>'2025-06-15']);
    $response=$this->actingAs($this->admin)->get(route('archives.show',['demande'=>$closed->id,'annee'=>'2025','unite'=>'Unité A']))->assertOk()
        ->assertSee('Dossier clôturé · lecture seule')->assertSee('Compte rendu vérifié')->assertSee('Réception conforme')
        ->assertSee('Historique des décisions')->assertSee('Pièces jointes')
        ->assertDontSee(route('demandes.edit',$closed))->assertDontSee('Supprimer le brouillon');
    preg_match('~class="admin-back" href="([^"]+)"~u',$response->getContent(),$link);
    $url=html_entity_decode($link[1]);
    parse_str(parse_url($url,PHP_URL_QUERY),$returnFilters);
    expect(parse_url($url,PHP_URL_PATH))->toBe('/archives')
        ->and($returnFilters)->toMatchArray(['annee'=>'2025','unite'=>'Unité A']);
    $this->put(route('demandes.update',$closed),[])->assertForbidden();
    $this->delete(route('demandes.destroy',$closed))->assertForbidden();
    $active=($this->makeDemande)(['statut'=>'terminee_agent','closed_at'=>null]);
    $this->get(route('archives.show',$active))->assertNotFound();
    expect($closed->fresh()->statut)->toBe('cloturee_receptionnee');
});

it('bounds and validates archive inputs including malformed arrays and reversed periods', function () {
    $this->actingAs($this->admin);
    foreach ([['q'=>['bad']],['q'=>str_repeat('a',151)],['unite'=>['bad']],['annee'=>'abc'],['annee'=>0],['du'=>'not-a-date'],['au'=>'2025-02-30'],['du'=>'2025-06-16','au'=>'2025-06-15'],['page'=>0]] as $filters) {
        $this->getJson(route('archives.index',$filters))->assertUnprocessable();
    }
});

it('paginates archives with stable ordering and preserves filters in pagination and detail links', function () {
    for ($i=1;$i<=17;$i++) ($this->makeDemande)(['objet_modif'=>'Pagination '.$i]);
    $this->actingAs($this->admin)->get(route('archives.index',['unite'=>'Unité A']))->assertOk()
        ->assertViewHas('demandes',fn($rows)=>$rows->total()===17 && $rows->count()===15)
        ->assertSee('page=2')->assertSee('unite=Unit');
    $this->get(route('archives.index',['unite'=>'Unité A','page'=>2]))->assertOk()
        ->assertViewHas('demandes',fn($rows)=>$rows->count()===2);
});

it('records the final reception timestamp once and immediately exposes the closed dossier in archives', function () {
    Role::firstOrCreate(['name'=>'reception','guard_name'=>'web']);
    $reception=User::create(['name'=>'Réception','prenom'=>'Test','email'=>'archive-reception@example.invalid','password'=>'test-password']);
    $reception->assignRole('reception');
    $demande=($this->makeDemande)(['statut'=>'terminee_agent','closed_at'=>null]);
    $this->travelTo(\Carbon\Carbon::parse('2026-11-11 15:20:30'));
    $this->actingAs($this->owner)->post(route('demandes.receptionner',$demande),['visa_demandeur'=>'Visa final','confirmation_reception'=>1])->assertSessionHasNoErrors()->assertRedirect();
    expect($demande->fresh()->closed_at->format('Y-m-d H:i:s'))->toBe('2026-11-11 15:20:30');
    $this->travel(1)->days();
    $this->post(route('demandes.receptionner',$demande),['visa_demandeur'=>'Deuxième visa','confirmation_reception'=>1])->assertForbidden();
    expect($demande->fresh()->closed_at->format('Y-m-d H:i:s'))->toBe('2026-11-11 15:20:30');
    $this->get(route('archives.index',['annee'=>2026]))->assertOk()->assertViewHas('demandes',fn($rows)=>$rows->total()===1);
    $this->travelBack();
});
