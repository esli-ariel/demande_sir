<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Demande;
use App\Models\DemandeValidation;
use App\Models\User;
use App\Services\DmaVisa;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;

class WorkflowController extends Controller
{
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
        $visa = app(DmaVisa::class);
        $data = $visa->validate($request, [
            'commentaire' => ($decision === 'refus' ? 'required' : 'nullable').'|string|max:5000',
        ]);
        $visa->run($request, function (User $actor) use ($demande, $ability, $role, $decision, $status, $data, $visa) {
            $locked = Demande::lockForUpdate()->findOrFail($demande->id);
            Gate::forUser($actor)->authorize($ability, $locked);
            $action = match ($ability) {
                'validerExploitation' => $decision === 'accord' ? 'exploitation_approve' : 'exploitation_refuse',
                'validerDts' => $decision === 'accord' ? 'dts_approve' : 'dts_refuse',
                'validerControle' => $decision === 'accord' ? 'control_approve' : 'control_refuse',
                'cloturer' => 'control_final',
            };
            // Determine the optional specialist stage under the dossier lock.
            if ($ability === 'validerDts' && $decision === 'accord') {
                $status = $locked->structuresSpecialisees()->exists() ? 'validee_dts' : 'validee_structure_specialisee';
            }
            $visa->record($locked,$actor,$action,$status,$data['commentaire'] ?? null);
            $updates = ['statut'=>$status];
            if ($role === 'controle_avancee' && $status === 'validee_controle_avancee' && ! $locked->numero_dma) {
                $updates['numero_dma'] = 'DMA-'.\Illuminate\Support\Str::ulid();
            }
            $locked->update($updates);
            app(\App\Services\DmaNotifications::class)->transition($locked, $actor, $data['commentaire'] ?? null);
        });

        return back()->with('success', $decision === 'accord' ? 'Avis favorable enregistré.' : 'Refus enregistré.');
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
        $visa = app(DmaVisa::class);
        $data = $visa->validate($request, [
            'structure_id' => 'required|integer|exists:structures_specialisees,id',
            'decision' => 'required|in:accord,refus',
            'commentaire' => ($request->input('decision') === 'refus' ? 'required' : 'nullable').'|string|max:5000',
        ]);
        $visa->run($request, function (User $actor) use ($demande, $data, $visa) {
            $locked = Demande::lockForUpdate()->findOrFail($demande->id);
            Gate::forUser($actor)->authorize('validerStructureSpecialisee', $locked);
            abort_unless(\App\Support\StaffWorkspace::structuresForDecision($actor, $locked)->whereKey($data['structure_id'])->exists(), 403);
            $structure = \App\Models\StructureSpecialisee::lockForUpdate()->findOrFail($data['structure_id']);
            $previousStatus = $locked->statut;
            $status = $previousStatus;
            if ($data['decision'] === 'refus') {
                $status = 'refusee_structure_specialisee';
            } else {
                $selected = $locked->structuresSpecialisees()->pluck('structures_specialisees.id');
                $approved = DemandeValidation::where('demande_id',$locked->id)->where('role','structure_specialisee')->where('decision','accord')->whereIn('structure_id',$selected)->distinct()->count('structure_id');
                if ($selected->count() > 0 && $approved + 1 === $selected->count()) $status = 'validee_structure_specialisee';
            }
            $visa->record($locked,$actor,$data['decision'] === 'accord' ? 'structure_approve' : 'structure_refuse',$status,$data['commentaire'] ?? null,$structure);
            $locked->update(['statut'=>$status]);
            if ($status !== $previousStatus) {
                app(\App\Services\DmaNotifications::class)->transition($locked,$actor,$data['commentaire'] ?? null);
            } else {
                app(\App\Services\DmaNotifications::class)->structureDecision($locked,$actor,$structure->nom,$data['commentaire'] ?? null);
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
        $validated = $request->validate(['analyse'=>'required|string|max:5000']);
        $request->merge(['commentaire'=>$validated['analyse']]);
        $this->decide($request, $demande, 'cloturer', 'controle_avancee', 'accord', 'cloturee');
        return back()->with('success','Vérification des travaux enregistrée. Le dossier est prêt pour réception par son demandeur.');
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
        $rules = [];
        if ($finish) {
            $rules += ['date_fin'=>'required|date_format:Y-m-d|after_or_equal:1000-01-01|before_or_equal:9999-12-31', 'travaux_realises'=>'required|string|max:5000'];
        } else {
            $rules['date_debut'] = 'required|date_format:Y-m-d|after_or_equal:1000-01-01|before_or_equal:9999-12-31';
        }
        $visa = app(DmaVisa::class);
        $data = $visa->validate($request,$rules);
        $visa->run($request, function (User $actor) use ($data, $demande, $ability, $finish, $visa) {
            $locked = Demande::lockForUpdate()->findOrFail($demande->id);
            Gate::forUser($actor)->authorize($ability, $locked);
            if ($finish) {
                abort_unless($locked->date_debut, 422);
                validator($data, ['date_fin'=>'after_or_equal:'.$locked->date_debut])->validate();
            }
            $updates = Arr::only($data,['date_debut','date_fin','travaux_realises']);
            $updates['statut'] = $finish ? 'terminee_agent' : 'en_cours_traitement';
            $visa->record($locked,$actor,$finish ? 'work_finish' : 'work_start',$updates['statut'],$finish ? $data['travaux_realises'] : 'Début des travaux : '.$data['date_debut']);
            $locked->update($updates);
            app(\App\Services\DmaNotifications::class)->transition($locked,$actor,$data['travaux_realises'] ?? null);
        });
        return back()->with('success', $finish ? 'Travaux terminés. Le demandeur peut réceptionner et clôturer sa DMA.' : 'Travaux démarrés.');
    }

    // ===================== RÉCEPTION =====================
    public function cloturerReception(Request $request, Demande $demande)
    {
        abort(403, 'La clôture définitive appartient au demandeur propriétaire, depuis Mes réceptions.');
    }
}
