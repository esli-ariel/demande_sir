<?php

namespace App\Services;

use App\Models\Demande;
use App\Models\User;
use App\Notifications\DemandeStatutChangeNotification;
use App\Support\DmaStatus;
use Illuminate\Support\Facades\Gate;

final class DmaNotifications
{
    public function transition(Demande $demande, ?User $actor, ?string $commentaire = null): void
    {
        $recipients = collect();
        if ($demande->user) {
            $title = in_array($demande->statut,['terminee_agent','cloturee'],true)
                ? 'Confirmez la réception pour clôturer votre DMA' : 'Mise à jour de votre DMA';
            $recipients->put($demande->user->id, ['user'=>$demande->user, 'title'=>$title]);
        }
        $roles = match ($demande->statut) {
            'soumise' => ['exploitant'],
            'validee_exploitation' => ['dts', 'chef_structure'],
            'validee_dts' => ['structure_specialisee'],
            'validee_structure_specialisee' => ['controle_avancee'],
            'validee_controle_avancee' => ['service_technique'],
            'terminee_agent' => ['reception', 'controle_avancee'],
            'cloturee' => ['reception'],
            'cloturee_receptionnee' => ['reception','service_technique','controle_avancee'],
            default => [],
        };
        $users = User::whereHas('roles', fn ($query) => $query->whereIn('name', $roles))->get();
        foreach ($users as $user) {
            if ($demande->statut === 'validee_dts' && ! \App\Support\StaffWorkspace::structuresForDecision($user, $demande)->exists()) {
                continue;
            }
            $title = match (true) {
                $demande->statut === 'cloturee_receptionnee' => 'Une DMA a été clôturée par son demandeur',
                in_array($demande->statut,['terminee_agent','cloturee'],true) && $user->hasRole('reception') => 'Une DMA attend la réception de son demandeur',
                $demande->statut === 'validee_exploitation' && $user->hasRole('chef_structure') && ! $user->hasRole('dts') => 'Une DMA entre dans votre suivi',
                default => 'Une DMA attend votre intervention',
            };
            $recipients->put($user->id, ['user'=>$user, 'title'=>$title]);
        }
        foreach ($recipients as ['user'=>$user, 'title'=>$title]) {
            if ($user->id !== $actor?->id && Gate::forUser($user)->allows('view', $demande)) {
                $user->notify(new DemandeStatutChangeNotification($demande, DmaStatus::label($demande->statut), $commentaire, $title, $actor?->name));
            }
        }
    }

    public function structureDecision(Demande $demande, User $actor, string $structure, ?string $commentaire): void
    {
        if ($demande->user && $demande->user_id !== $actor->id) {
            $demande->user->notify(new DemandeStatutChangeNotification($demande, 'Avis favorable de la structure '.$structure, $commentaire, 'Un avis a été enregistré sur votre DMA', $actor->name));
        }
    }
}
