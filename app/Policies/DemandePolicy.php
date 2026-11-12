<?php

namespace App\Policies;

use App\Models\Demande;
use App\Models\User;

class DemandePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(\App\Support\StaffWorkspace::modernRoles());
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Demande $demande): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        if ($user->hasRole('demandeur')) {
            return $user->id === $demande->user_id;
        }
        if (\App\Support\StaffWorkspace::role($user)) {
            return \App\Support\StaffWorkspace::query($user)->whereKey($demande->id)->exists();
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'demandeur']);
    }


    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Demande $demande): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Demande $demande): bool
    {
        return false;
    }

    public function validerExploitation(User $user, Demande $demande)
{
    return $user->hasRole('exploitant')&& $demande->statut === 'soumise';
}
    public function rejeterExploitation(User $user, Demande $demande)
{
    return $user->hasRole('exploitant')&& $demande->statut === 'soumise';
}

    /**
     * Détermine si la DTS peut valider
     */
    public function validerDts(User $user, Demande $demande): bool
    {
        return $user->hasRole('dts') && $demande->statut === 'validee_exploitation';
    }

    /**
     * Détermine si une Structure spécialisée peut valider
     */
    public function validerOuRejeterStructure(User $user, Demande $demande): bool
    {
        return $this->validerStructureSpecialisee($user, $demande);
    }

    public function validerStructureSpecialisee(User $user, Demande $demande): bool
    {
        return \App\Support\StaffWorkspace::role($user) === 'structure_specialisee'
            && $demande->statut === 'validee_dts'
            && \App\Support\StaffWorkspace::structuresForDecision($user, $demande)->exists();
    }

    /**
     * Détermine si le Contrôle avancé peut valider
     */
    public function validerControle(User $user, Demande $demande): bool
    {
        return $user->hasRole('controle_avancee') && $demande->statut === 'validee_structure_specialisee';
    }

    /**
     * Détermine si le Service technique peut traiter
     */
    public function traiter(User $user, Demande $demande): bool
    {
        return $user->hasRole('service_technique') && $demande->statut === 'validee_controle_avancee';
    }

    /**
     * Détermine si la réception peut clôturer
     */
    public function cloturer(User $user, Demande $demande): bool
    {
        return $user->hasRole('controle_avancee') && $demande->statut === 'terminee_agent';
    }
    /**
     * Détermine si la réception peut clôturer
     */
    public function terminerTravaux(User $user, Demande $demande): bool
    {
        return $user->hasRole('service_technique') && $demande->statut === 'en_cours_traitement';
    }

    public function receptionner(User $user, Demande $demande): bool
    {
        return $user->hasRole('demandeur') && $user->id === $demande->user_id
            && in_array($demande->statut, ['terminee_agent','cloturee'], true);
    }

    /**
     * Détermine si un demandeur peut modifier sa demande
     */
    public function update(User $user, Demande $demande): bool
    {
        return $user->hasAnyRole(['admin','demandeur']) && $user->id === $demande->user_id && $demande->statut === 'brouillon';
    }

    /**
     * Détermine si un demandeur peut supprimer sa demande
     */
    public function delete(User $user, Demande $demande): bool
    {
        return $user->hasAnyRole(['admin','demandeur']) && $user->id === $demande->user_id && $demande->statut === 'brouillon';
    }
    public function submit(User $user, Demande $demande): bool
    {
        return $user->hasRole('demandeur') && $user->id === $demande->user_id && $demande->statut === 'brouillon';
    }
}
