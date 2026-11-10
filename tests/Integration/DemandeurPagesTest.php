<?php

use App\Models\Demande;
use App\Models\DemandePieceJointe;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

uses(Tests\TestCase::class);
require_once __DIR__.'/../Support/WorkspaceDatabase.php';

beforeEach(function () {
    $this->admin = \Tests\Support\resetWorkspaceDatabase();
    $this->requester = User::create(['name' => 'Propriétaire', 'prenom' => 'Test', 'email' => 'requester@example.invalid', 'password' => 'test-password']);
    $this->requester->assignRole('demandeur');
    $this->other = User::create(['name' => 'Autre', 'prenom' => 'Test', 'email' => 'other@example.invalid', 'password' => 'test-password']);
    $this->other->assignRole('demandeur');
    $this->payload = ['structure' => 'Structure test', 'unite_concernee' => 'Unité test', 'objet_modif' => 'Mon dossier', 'motif' => 'Motif test', 'repere' => 'R-1', 'fonction' => 'Test', 'situation_existante' => 'Avant', 'situation_souhaitee' => 'Après'];
    $this->makeDemande = fn (User $owner, array $extra = []) => Demande::create(array_replace($this->payload, ['user_id' => $owner->id, 'nom' => $owner->name, 'statut' => 'brouillon'], $extra));
    $this->actingAs($this->requester);
});

it('shows only the signed-in requesters requests and statistics', function () {
    ($this->makeDemande)($this->requester);
    ($this->makeDemande)($this->other, ['objet_modif' => 'Dossier confidentiel autre auteur', 'statut' => 'refusee_dts']);
    $this->get(route('dashboard'))->assertOk()->assertSee('Mon espace demandeur')->assertDontSee('Dossier confidentiel autre auteur')->assertViewHas('total', 1)
        ->assertViewHas('summary', fn ($summary) => $summary['brouillons'] === 1 && $summary['refusees'] === 0);
    $this->get(route('demandes.index'))->assertOk()->assertSee('Mon dossier')->assertDontSee('Dossier confidentiel autre auteur');
    $this->get(route('demandes.index', ['search' => 'confidentiel']))->assertOk()->assertSee('Aucune demande ne correspond');
});

it('renders requester forms, profile, notifications and the dashboard alias', function () {
    $demande = ($this->makeDemande)($this->requester);
    foreach (['demandes.create', 'profile.edit', 'notifications.index', 'dashboard.demandeur', 'demandeur.receptions'] as $route) {
        $this->get(route($route))->assertOk();
    }
    $this->get(route('demandes.edit', $demande))->assertOk()->assertSee('Modifier mon brouillon');
    $this->get(route('demandes.show', $demande))->assertOk()->assertSee('Envoyer la demande');
});

it('denies reading and changing another requesters request', function () {
    $demande = ($this->makeDemande)($this->other);
    $this->get(route('demandes.show', $demande))->assertForbidden();
    $this->get(route('demandes.edit', $demande))->assertForbidden();
    $this->put(route('demandes.update', $demande), $this->payload)->assertForbidden();
    $this->post(route('demandes.submit', $demande))->assertForbidden();
    $this->delete(route('demandes.destroy', $demande))->assertForbidden();
    expect($demande->fresh()->statut)->toBe('brouillon');
});

it('creates an owned draft and attaches selected structures and every file', function () {
    Storage::fake('public');
    $structureId = DB::table('structures_specialisees')->insertGetId(['nom' => 'Structure de test', 'created_at' => now(), 'updated_at' => now()]);
    $this->post(route('demandes.store'), $this->payload + ['structures_specialisees' => [$structureId], 'pieces_jointes' => [UploadedFile::fake()->create('a.pdf', 10, 'application/pdf'), UploadedFile::fake()->create('b.pdf', 10, 'application/pdf')]])->assertRedirect(route('demandes.index'))->assertSessionHasNoErrors();
    $demande = Demande::firstOrFail();
    expect($demande->user_id)->toBe($this->requester->id)->and($demande->statut)->toBe('brouillon')->and($demande->piecesJointes()->count())->toBe(2)->and($demande->structuresSpecialisees()->count())->toBe(1);
});

it('sends a complete draft once and then prevents editing and deletion', function () {
    $demande = ($this->makeDemande)($this->requester);
    $this->post(route('demandes.submit', $demande))->assertRedirect(route('demandes.show', $demande));
    expect($demande->fresh()->statut)->toBe('soumise');
    $this->post(route('demandes.submit', $demande))->assertForbidden();
    $this->get(route('demandes.edit', $demande))->assertForbidden();
    $this->put(route('demandes.update', $demande), $this->payload)->assertForbidden();
    $this->delete(route('demandes.destroy', $demande))->assertForbidden();
});

it('keeps an incomplete request as a draft', function () {
    $demande = ($this->makeDemande)($this->requester, ['motif' => null]);
    $this->post(route('demandes.submit', $demande))->assertSessionHasErrors('motif');
    expect($demande->fresh()->statut)->toBe('brouillon');
});

it('scopes reception to the owner and records confirmation only once', function () {
    $mine = ($this->makeDemande)($this->requester, ['statut' => 'cloturee', 'objet_modif' => 'Ma réception']);
    $other = ($this->makeDemande)($this->other, ['statut' => 'cloturee', 'objet_modif' => 'Autre réception']);
    $this->get(route('demandeur.receptions'))->assertOk()->assertSee('Ma réception')->assertDontSee('Autre réception');
    $this->post(route('demandes.receptionner', $other), ['visa_demandeur' => 'Test'])->assertForbidden();
    $this->post(route('demandes.receptionner', $mine), ['visa_demandeur' => 'Test', 'commentaire' => 'Bien reçu'])->assertRedirect(route('demandes.show', $mine));
    expect($mine->fresh()->statut)->toBe('cloturee_receptionnee')->and(DB::table('demandes_validations')->count())->toBe(1);
    $this->post(route('demandes.receptionner', $mine), ['visa_demandeur' => 'Test'])->assertForbidden();
    expect(DB::table('demandes_validations')->count())->toBe(1)->and($other->fresh()->statut)->toBe('cloturee');
});

it('prevents removing an attachment after submission', function () {
    Storage::fake('public');
    $demande = ($this->makeDemande)($this->requester, ['statut' => 'soumise']);
    Storage::disk('public')->put('demandes/test.pdf', 'test');
    $piece = DemandePieceJointe::create(['demande_id' => $demande->id, 'uploaded_by' => $this->requester->id, 'chemin_fichier' => 'demandes/test.pdf', 'type_document' => 'pdf']);
    $this->delete(route('pieces.destroy', $piece))->assertForbidden();
    expect($piece->fresh())->not->toBeNull();
    Storage::disk('public')->assertExists('demandes/test.pdf');
});
