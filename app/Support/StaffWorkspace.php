<?php

namespace App\Support;

use App\Models\Demande;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class StaffWorkspace
{
    public const ROLES = [
        'reception' => ['label'=>'Réception', 'readonly'=>true, 'pending'=>['terminee_agent','cloturee']],
        'chef_structure' => ['label'=>'Chef de structure', 'readonly'=>true, 'pending'=>['validee_exploitation','validee_dts','validee_structure_specialisee','validee_controle_avancee','en_cours_traitement','terminee_agent','cloturee']],
        'service_technique' => ['label'=>'Service technique', 'pending'=>['validee_controle_avancee','en_cours_traitement']],
        'controle_avancee' => ['label' => 'Contrôle avancé', 'pending' => ['validee_structure_specialisee', 'terminee_agent'], 'ability' => 'validerControle', 'approve' => 'demandes.valider_controle', 'reject' => 'demandes.rejeter_controle'],
        'structure_specialisee' => ['label' => 'Structure spécialisée', 'pending' => ['validee_dts'], 'ability' => 'validerStructureSpecialisee', 'approve' => 'demandes.validerStructure', 'reject' => 'demandes.validerStructure'],
        'dts' => ['label' => 'Direction technique · DTS', 'pending' => ['validee_exploitation'], 'ability' => 'validerDts', 'approve' => 'demandes.valider_dts', 'reject' => 'demandes.rejeter_dts'],
        'exploitant' => ['label' => 'Exploitation', 'pending' => ['soumise'], 'ability' => 'validerExploitation', 'approve' => 'demandes.validerExploitation', 'reject' => 'demandes.rejeterExploitation'],
    ];

    public static function modernRoles(): array
    {
        return ['admin', 'demandeur', ...array_keys(self::ROLES)];
    }

    public static function role(?User $user): ?string
    {
        if (! $user || $user->hasAnyRole(['admin', 'demandeur'])) {
            return null;
        }
        foreach (array_keys(self::ROLES) as $role) {
            if ($user->hasRole($role)) {
                return $role;
            }
        }

        return null;
    }

    public static function query(User $user): Builder
    {
        abort_unless(self::role($user), 403);

        $query = Demande::query()->where('statut', '!=', 'brouillon');
        if (self::role($user) === 'reception') {
            $query->whereIn('statut',['terminee_agent','cloturee','cloturee_receptionnee']);
        }
        if (self::role($user) === 'chef_structure') {
            $query->whereIn('statut', ['validee_exploitation','validee_dts','refusee_dts','validee_structure_specialisee','refusee_structure_specialisee','rejetee_structure_specialisee','validee_controle_avancee','refusee_controle_avancee','en_cours_traitement','terminee_agent','cloturee','cloturee_receptionnee']);
        }
        if (self::role($user) === 'structure_specialisee') {
            $query->whereIn('statut', ['validee_dts','validee_structure_specialisee','refusee_structure_specialisee','validee_controle_avancee','refusee_controle_avancee','en_cours_traitement','terminee_agent','cloturee','cloturee_receptionnee'])
                ->whereHas('structuresSpecialisees.users', fn ($users) => $users->where('users.id', $user->id));
        }
        return $query;
    }
    public static function pending(Builder $query, User $user): Builder
    {
        $query->whereIn('statut', self::ROLES[self::role($user)]['pending']);
        if (self::role($user) === 'structure_specialisee') {
            $query->whereHas('structuresSpecialisees', function ($structures) use ($user) {
                $structures->whereHas('users', fn ($users) => $users->where('users.id', $user->id))
                    ->whereDoesntHave('decisions', fn ($decisions) => $decisions->whereColumn('demandes_validations.demande_id', 'demandes.id'));
            });
        }
        return $query;
    }

    public static function structuresForDecision(User $user, Demande $demande): Builder
    {
        return \App\Models\StructureSpecialisee::query()
            ->whereHas('users', fn ($users) => $users->where('users.id', $user->id))
            ->whereHas('demandes', fn ($requests) => $requests->where('demandes.id', $demande->id))
            ->whereNotIn('id', \App\Models\DemandeValidation::where('demande_id', $demande->id)->whereNotNull('structure_id')->select('structure_id'));
    }
}
