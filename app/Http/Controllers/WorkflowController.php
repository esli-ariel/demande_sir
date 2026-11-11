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
    private function enregistrerValidation(Demande $demande, string $role, string $decision, Request $request, ?string $nouveauStatut = null)
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
        return $this->decide($request, $demande, 'validerExploitation', 'exploitant', 'accord', 'validee_exploitation');
    }

    public function rejeterExploitation(Request $request, Demande $demande)
    {
        return $this->decide($request, $demande, 'validerExploitation', 'exploitant', 'refus', 'refusee_exploitation');
    }

    private function decide(Request $request, Demande $demande, string $ability, string $role, string $decision, string $status)
    {
        Gate::authorize($ability, $demande);
        $data = $request->validate([
            'visa' => 'required|string|max:255',
            'commentaire' => ($decision === 'refus' ? 'required' : 'nullable').'|string|max:5000',
        ]);
        \Illuminate\Support\Facades\DB::transaction(function () use ($demande, $ability, $role, $decision, $status, $data) {
            $locked = Demande::lockForUpdate()->findOrFail($demande->id);
            Gate::authorize($ability, $locked);
            DemandeValidation::create([
                'demande_id' => $locked->id, 'user_id' => Auth::id(), 'role' => $role,
                'decision' => $decision, 'visa' => $data['visa'], 'commentaire' => $data['commentaire'] ?? null,
                'date_validation' => now(),
            ]);
            $updates = ['statut'=>$status];
            if ($role === 'controle_avancee' && $status === 'validee_controle_avancee' && ! $locked->numero_dma) {
                $updates['numero_dma'] = 'DMA-'.\Illuminate\Support\Str::ulid();
            }
            $locked->update($updates);
            app(\App\Services\DmaNotifications::class)->transition($locked, Auth::user(), $data['commentaire'] ?? null);
        });

        return back()->with('success', $role === 'reception' ? 'DMA clôturée définitivement. Le demandeur a été notifié.' : ($decision === 'accord' ? 'Avis favorable enregistré.' : 'Refus enregistré.'));
    }

    // ===================== DTS =====================
    public function validerDts(Request $request, Demande $demande)
    {
        $status = $demande->structuresSpecialisees()->exists() ? 'validee_dts' : 'validee_structure_specialisee';
        return $this->decide($request, $demande, 'validerDts', 'dts', 'accord', $status);
    }

    public function rejeterDts(Request $request, Demande $demande)
    {
        return $this->decide($request, $demande, 'validerDts', 'dts', 'refus', 'refusee_dts');
    }

    // ===================== STRUCTURES SPÉCIALISÉES =====================
    public function validerOuRejeterStructure(Request $request, Demande $demande)
    {
        Gate::authorize('validerStructureSpecialisee', $demande);
        $data = $request->validate([
            'structure_id' => 'required|integer|exists:structures_specialisees,id',
            'decision' => 'required|in:accord,refus', 'visa' => 'required|string|max:255',
            'commentaire' => ($request->input('decision') === 'refus' ? 'required' : 'nullable').'|string|max:5000',
        ]);
        \Illuminate\Support\Facades\DB::transaction(function () use ($demande, $data) {
            $locked = Demande::lockForUpdate()->findOrFail($demande->id);
            Gate::authorize('validerStructureSpecialisee', $locked);
            abort_unless(\App\Support\StaffWorkspace::structuresForDecision(Auth::user(), $locked)->whereKey($data['structure_id'])->exists(), 403);
            DemandeValidation::create([
                'demande_id'=>$locked->id,'user_id'=>Auth::id(),'structure_id'=>$data['structure_id'],
                'role'=>'structure_specialisee','decision'=>$data['decision'],'visa'=>$data['visa'],
                'commentaire'=>$data['commentaire'] ?? null,'date_validation'=>now(),
            ]);
            $previousStatus = $locked->statut;
            if ($data['decision'] === 'refus') {
                $locked->update(['statut'=>'refusee_structure_specialisee']);
            } else {
                $selected = $locked->structuresSpecialisees()->pluck('structures_specialisees.id');
                $approved = DemandeValidation::where('demande_id',$locked->id)->where('role','structure_specialisee')->where('decision','accord')->whereIn('structure_id',$selected)->distinct()->count('structure_id');
                if ($selected->count() > 0 && $approved === $selected->count()) {
                    $locked->update(['statut'=>'validee_structure_specialisee']);
                }
            }
            if ($locked->statut !== $previousStatus) {
                app(\App\Services\DmaNotifications::class)->transition($locked, Auth::user(), $data['commentaire'] ?? null);
            } else {
                $structure = \App\Models\StructureSpecialisee::findOrFail($data['structure_id']);
                app(\App\Services\DmaNotifications::class)->structureDecision($locked, Auth::user(), $structure->nom, $data['commentaire'] ?? null);
            }
        });
        return back()->with('success','Avis de la structure enregistré.');
    }

    // ===================== CONTRÔLE AVANCÉ =====================
    public function validerControle(Request $request, Demande $demande)
    {
        return $this->decide($request, $demande, 'validerControle', 'controle_avancee', 'accord', 'validee_controle_avancee');
    }

    public function rejeterControle(Request $request, Demande $demande)
    {
        return $this->decide($request, $demande, 'validerControle', 'controle_avancee', 'refus', 'refusee_controle_avancee');
    }

    public function cloturer(Request $request, Demande $demande)
    {
        Gate::authorize('cloturer', $demande);
        $request->validate(['analyse'=>'required|string|max:5000']);
        $request->merge(['commentaire'=>$request->input('analyse')]);
        $this->decide($request, $demande, 'cloturer', 'controle_avancee', 'accord', 'cloturee');
        return back()->with('success','Vérification des travaux enregistrée. Le dossier est prêt pour réception.');
    }

    // ===================== SERVICE TECHNIQUE =====================
    public function traiterAgent(Request $request, Demande $demande)
    {
        return $this->travaux($request, $demande, false);
    }

    public function terminerTravaux(Request $request, Demande $demande)
    {
        return $this->travaux($request, $demande, true);
    }

    private function travaux(Request $request, Demande $demande, bool $finish)
    {
        $ability = $finish ? 'terminerTravaux' : 'traiter';
        Gate::authorize($ability, $demande);
        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $demande, $ability, $finish) {
            $locked = Demande::lockForUpdate()->findOrFail($demande->id);
            Gate::authorize($ability, $locked);
            $rules = ['visa'=>'required|string|max:255'];
            if ($finish) {
                abort_unless($locked->date_debut, 422);
                $rules += ['date_fin'=>'required|date|after_or_equal:'.$locked->date_debut, 'travaux_realises'=>'required|string|max:5000'];
            } else {
                $rules['date_debut'] = 'required|date';
            }
            $data = $request->validate($rules);
            $visa = $data['visa']; unset($data['visa']);
            $data['statut'] = $finish ? 'terminee_agent' : 'en_cours_traitement';
            $locked->update($data);
            DemandeValidation::create(['demande_id'=>$locked->id,'user_id'=>Auth::id(),'role'=>'service_technique', 'decision'=>'accord','visa'=>$visa,'commentaire'=>$finish ? $data['travaux_realises'] : 'Début des travaux : '.$data['date_debut'], 'date_validation'=>now()]);
            app(\App\Services\DmaNotifications::class)->transition($locked, Auth::user(), $data['travaux_realises'] ?? null);
        });
        return back()->with('success', $finish ? 'Travaux terminés. Le dossier est transmis pour réception.' : 'Travaux démarrés.');
    }

    // ===================== RÉCEPTION =====================
    public function cloturerReception(Request $request, Demande $demande)
    {
        return $this->decide($request, $demande, 'receptionner', 'reception', 'accord', 'cloturee_receptionnee');
    }
    public function reception(Request $request, Demande $demande) { return $this->cloturerReception($request, $demande); }
}
