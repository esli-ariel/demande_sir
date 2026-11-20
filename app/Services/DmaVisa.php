<?php

namespace App\Services;

use App\Exceptions\VisaPasswordMismatch;
use App\Models\Demande;
use App\Models\DemandeValidation;
use App\Models\StructureSpecialisee;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

final class DmaVisa
{
    private const ACTIONS = [
        'exploitation_approve'=>['exploitant','accord','validerExploitation'],
        'exploitation_refuse'=>['exploitant','refus','validerExploitation'],
        'dts_approve'=>['dts','accord','validerDts'],
        'dts_refuse'=>['dts','refus','validerDts'],
        'structure_approve'=>['structure_specialisee','accord','validerStructureSpecialisee'],
        'structure_refuse'=>['structure_specialisee','refus','validerStructureSpecialisee'],
        'control_approve'=>['controle_avancee','accord','validerControle'],
        'control_refuse'=>['controle_avancee','refus','validerControle'],
        'control_final'=>['controle_avancee','accord','cloturer'],
        'work_start'=>['service_technique','accord','traiter'],
        'work_finish'=>['service_technique','accord','terminerTravaux'],
        'requester_receipt'=>['demandeur','accord','receptionner'],
    ];

    public function validate(Request $request, array $businessRules = [], string $confirmation = 'visa_confirmation'): array
    {
        if (! in_array($confirmation,['visa_confirmation','confirmation_reception'],true)) throw new \LogicException('Confirmation de visa invalide.');
        $this->ensureNotLimited($request);
        $data = $request->validate(array_merge($businessRules, [
            'password'=>['bail','required','string','max:4096','not_regex:/\x00/'],
            $confirmation=>['bail','required','boolean','accepted'],
        ]));
        // Confirmation credentials are never returned to business persistence.
        unset($data['password'], $data[$confirmation]);
        return $data;
    }

    public function run(Request $request, Closure $operation, string $confirmation = 'visa_confirmation'): mixed
    {
        if (DB::transactionLevel() !== 0) {
            throw new \LogicException('Le visa doit ouvrir sa propre transaction.');
        }
        $this->validate($request,[],$confirmation);
        try {
            $result = DB::transaction(function () use ($request, $operation) {
                $actor = User::lockForUpdate()->findOrFail($request->user()->id);
                abort_if($actor->deactivated_at !== null, 403);
                $this->ensureNotLimited($request);
                if (! Hash::check($request->input('password'), $actor->password)) {
                    throw new VisaPasswordMismatch();
                }
                return $operation($actor);
            });
        } catch (VisaPasswordMismatch $e) {
            // Outside the rolled-back SQL transaction: database cache must retain failures.
            RateLimiter::hit($this->key($request), 60);
            throw ValidationException::withMessages(['password'=>'Le mot de passe de confirmation est incorrect.']);
        }
        RateLimiter::clear($this->key($request));
        return $result;
    }

    public function record(Demande $demande, User $actor, string $action, string $statusAfter, ?string $comment = null, ?StructureSpecialisee $structure = null): DemandeValidation
    {
        if (DB::transactionLevel() === 0 || ! isset(self::ACTIONS[$action])) {
            throw new \LogicException('Action de visa invalide ou hors transaction.');
        }
        [$role,$decision,$ability] = self::ACTIONS[$action];
        abort_unless($actor->hasRole($role), 403);
        Gate::forUser($actor)->authorize($ability, $demande);
        if ($role === 'structure_specialisee') {
            abort_unless($structure && \App\Support\StaffWorkspace::structuresForDecision($actor,$demande)->whereKey($structure->id)->exists(), 403);
        } else {
            abort_if($structure !== null, 403);
        }
        $name = trim($actor->prenom.' '.$actor->name);
        $time = now('UTC');
        return DemandeValidation::create([
            'demande_id'=>$demande->id, 'user_id'=>$actor->id, 'structure_id'=>$structure?->id,
            'role'=>$role, 'decision'=>$decision, 'visa'=>mb_substr($name,0,255),
            'signer_name'=>$name, 'signer_structure_name'=>$structure?->nom,
            'signed_at'=>$time, 'visa_action'=>$action,
            'status_before'=>$demande->statut, 'status_after'=>$statusAfter,
            'commentaire'=>$comment, 'date_validation'=>$time->toDateString(),
        ]);
    }

    private function key(Request $request): string
    {
        return 'dma-visa-password:'.$request->user()->id;
    }

    private function ensureNotLimited(Request $request): void
    {
        if (RateLimiter::tooManyAttempts($this->key($request),5)) {
            abort(429,'Trop de mots de passe incorrects. Réessayez dans '.RateLimiter::availableIn($this->key($request)).' secondes.');
        }
    }
}
