<?php

namespace App\Policies;

use App\Models\Demande;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DemandePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, Demande $demande): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Demande $demande): bool
    {
        // Le demandeur peut voir ses propres demandes
    if ($user->id === $demande->user_id) {
        return true;
    }

    // Les rôles de supervision peuvent voir toutes les demandes
    if ($user->hasAnyRole([
        'exploitant',
        'dts',
        'structure_specialisee',
        'controle_avancee',
        'service_technique',
        'reception',
        'admin'
    ])) {
        return true;
    }

    return false;

    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
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
        return $user->hasRole('structure_specialisee') && $demande->statut === 'validee_dts';
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
        return $user->hasRole('reception') && $demande->statut === 'en_cours_traitement';
    }

    /**
     * Détermine si un demandeur peut modifier sa demande
     */
    public function update(User $user, Demande $demande): bool
    {
        return $user->id === $demande->user_id && $demande->statut === 'brouillon';
    }

    /**
     * Détermine si un demandeur peut supprimer sa demande
     */
    public function delete(User $user, Demande $demande): bool
    {
        return $user->id === $demande->user_id && $demande->statut === 'brouillon';
    }
}
