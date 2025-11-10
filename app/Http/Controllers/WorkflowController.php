<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Demande;
use App\Models\DemandeValidation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class WorkflowController extends Controller
{
    /**
     * Enregistre une validation ou un refus dans la table demandes_validations
     */
    private function enregistrerValidation(Demande $demande, string $role, string $decision, Request $request, string $nouveauStatut = null)
    {
        DemandeValidation::create([
            'demande_id'      => $demande->id,
            'role'            => $role,
            'user_id'         => Auth::id(),
            'decision'        => $decision,
            'visa'            => $request->visa ?? Auth::user()->name,
            'commentaire'     => $request->commentaire,
            'date_validation' => now(),
        ]);

        if ($nouveauStatut) {
            $demande->update(['statut' => $nouveauStatut]);
        }
    }

    // ===================== EXPLOITATION =====================
    public function validerExploitation(Request $request, Demande $demande)
    {
        Gate::authorize('validerExploitation', $demande);

        $this->enregistrerValidation($demande, 'exploitant', 'accord', $request, 'validee_exploitation');
        return back()->with('success', 'Demande validée par Exploitation.');
    }

    public function rejeterExploitation(Request $request, Demande $demande)
    {
        Gate::authorize('validerExploitation', $demande);

        $this->enregistrerValidation($demande, 'exploitant', 'refus', $request, 'refusee_exploitation');
        return back()->with('error', 'Demande refusée par Exploitation.');
    }

    // ===================== DTS =====================
   public function validerDTS(Request $request, Demande $demande)
{
    Gate::authorize('validerDTS', $demande);

    $demande->statut = 'validee_dts';
    $demande->save();

    $structures = $demande->structuresSpecialisees;

    if ($structures->isNotEmpty()) {
        // Envoi vers les structures sélectionnées
        foreach ($structures as $structure) {
            // On enregistre une ligne de validation "en attente"
            $this->enregistrerValidation($demande, 'dts', 'accord', $request, 'validee_dts');
            $demande->validations()->create([
                'user_id' => $structure->id,
                'role' => 'structure_specialisee',
                'decision' => null,
                'visa' => null,
                'commentaire' => null,
                'date_validation' => null,
            ]);
        }

        return back()->with('success', 'Demande validée par la DTS et envoyée aux structures spécialisées sélectionnées.');
    } else {
        // Aucune structure : envoi direct au contrôle avancé
         $this->enregistrerValidation($demande, 'dts', 'accord', $request, 'validee_dts');
        $demande->validations()->create([
            'validator_id' => auth()->id(),
            'user_id' => Auth::id(),
            'role' => 'dts',
            'decision' => 'accord',
            'visa' => $request->visa ?? Auth::user()->name,
            'commentaire' => $request->commentaire ?? '',
            'date_validation' => now(),
        ]);

        $demande->update(['statut' => 'validee_controle_avance']);

        return back()->with('success', 'Demande validée par la DTS et envoyée au Contrôle Avancé.');
    }
}


    public function rejeterDts(Request $request, Demande $demande)
    {
        Gate::authorize('validerDts', $demande);

        $this->enregistrerValidation($demande, 'dts', 'refus', $request, 'refusee_dts');
        return back()->with('error', 'Demande refusée par la DTS.');
    }

    // ===================== STRUCTURES SPÉCIALISÉES =====================
public function validerOuRejeterStructure(Request $request, Demande $demande)
{
    Gate::authorize('validerStructureSpecialisee', $demande);

    $validated = $request->validate([
        'decision' => 'required|in:accord,refus',
        'visa' => 'required|string|max:255',
        'commentaire' => 'nullable|string',
    ]);

    // 1️⃣ Enregistrer la décision de cette structure
    $demande->validations()->create([
        'user_id' => Auth::id(),
        'role' => 'structure_specialisee',
        'decision' => $validated['decision'],
        'visa' => $validated['visa'],
        'commentaire' => $validated['commentaire'],
        'date_validation' => now(),
    ]);

    // 2️⃣ Vérifier si une structure a refusé
    $refus = $demande->validations()
        ->where('role', 'structure_specialisee')
        ->where('decision', 'refus')
        ->exists();

    if ($refus) {
        $demande->update(['statut' => 'refusee_structure_specialisee']);
        return back()->with('error', 'Une structure spécialisée a refusé : demande rejetée.');
    }

    // 3️⃣ Vérifier si toutes les structures ont validé
    $total = $demande->structuresSpecialisees()->count();
    $validees = $demande->validations()
        ->where('role', 'structure_specialisee')
        ->where('decision', 'accord')
        ->count();

    if ($validees === $total && $total > 0) {
        $demande->update(['statut' => 'validee_structure_specialisee']);
        return back()->with('success', 'Toutes les structures spécialisées ont validé : envoi au Contrôle Avancé.');
    }

    // 4️⃣ Sinon, on attend les autres structures
    return back()->with('info', 'Validation enregistrée. En attente des autres structures spécialisées.');
}

    // ===================== CONTRÔLE AVANCÉ =====================
    public function validerControle(Request $request, Demande $demande)
    {
        Gate::authorize('validerControle', $demande);

        $this->enregistrerValidation($demande, 'controle_avancee', 'accord', $request, 'validee_controle_avancee');

        // Attribuer un numéro DMA unique
        if (!$demande->numero_dma) {
            $demande->update(['numero_dma' => 'DMA-' . strtoupper(uniqid())]);
        }

        return back()->with('success', 'Demande validée et numéro DMA attribué.');
    }

    public function rejeterControle(Request $request, Demande $demande)
    {
        Gate::authorize('validerControle', $demande);

        $this->enregistrerValidation($demande, 'controle_avancee', 'refus', $request, 'refusee_controle_avancee');
        return back()->with('error', 'Demande refusée par le Contrôle avancé.');
    }

    // ===================== SERVICE TECHNIQUE =====================
    public function traiterAgent(Request $request, Demande $demande)
    {
        Gate::authorize('traiter', $demande);

        $demande->update([
            'statut'           => 'en_cours_traitement',
            'date_debut'       => $request->date_debut,
            'date_fin'         => $request->date_fin,
            'travaux_realises' => $request->travaux_realises,
        ]);

        DemandeValidation::create([
            'demande_id'      => $demande->id,
            'role'            => 'service_technique',
            'user_id'         => Auth::id(),
            'decision'        => 'accord',
            'visa'            => $request->visa ?? Auth::user()->name,
            'commentaire'     => $request->commentaire,
            'date_validation' => now(),
        ]);

        return back()->with('success', 'Travaux du service technique enregistrés.');
    }

    // ===================== RÉCEPTION =====================
    public function cloturerReception(Request $request, Demande $demande)
    {
        Gate::authorize('cloturer', $demande);

        // Visa du demandeur
        if ($request->filled('visa_demandeur')) {
            DemandeValidation::create([
                'demande_id'      => $demande->id,
                'role'            => 'reception_demandeur',
                'user_id'         => Auth::id(),
                'decision'        => 'accord',
                'visa'            => $request->visa_demandeur,
                'commentaire'     => $request->commentaire,
                'date_validation' => now(),
            ]);
        }

        // Visa du responsable d’intervention
        if ($request->filled('visa_responsable')) {
            DemandeValidation::create([
                'demande_id'      => $demande->id,
                'role'            => 'reception_responsable',
                'user_id'         => Auth::id(),
                'decision'        => 'accord',
                'visa'            => $request->visa_responsable,
                'commentaire'     => $request->commentaire,
                'date_validation' => now(),
            ]);
        }

        // Vérifier si les deux validations réception sont présentes
        $visas = $demande->validations()->whereIn('role',['reception_demandeur','reception_responsable'])->count();
        if ($visas >= 2) {
            $demande->update(['statut' => 'cloturee_receptionnee']);
        }

        return back()->with('success', 'Réception enregistrée.');
    }
}
