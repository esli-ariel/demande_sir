<?php

namespace App\Support;

final class DmaWorkflow
{
    public static function steps(string $status, bool $hasStructures): array
    {
        $position = match ($status) {
            'brouillon' => 0,
            'soumise', 'en_attente', 'en_attente_validation', 'refusee_exploitation' => 1,
            'validee_exploitation', 'refusee_dts' => 2,
            'validee_dts', 'refusee_structure_specialisee', 'rejetee_structure_specialisee' => 3,
            'validee_structure_specialisee', 'refusee_controle_avancee' => 4,
            'validee_controle_avancee', 'en_cours_traitement' => 5,
            'terminee_agent', 'cloturee' => 6,
            'cloturee_receptionnee' => 7,
            default => null,
        };
        $refused = DmaStatus::group($status) === 'refusees';
        $labels = ['Soumission', 'Exploitation', 'DTS', 'Structures spécialisées', 'Contrôle avancé', 'Travaux', $status === 'cloturee_receptionnee' ? 'Réception finale' : 'Réception du demandeur'];
        $steps = [];
        foreach ($labels as $index => $label) {
            $state = match (true) {
                $position === null => 'unknown',
                $index === 3 && ! $hasStructures && $position !== 3 => 'skipped',
                $index < $position => 'complete',
                $index === $position && $refused => 'refused',
                $index > $position && $refused => 'blocked',
                $index === $position => 'current',
                default => 'upcoming',
            };
            $detail = match ($state) {
                'complete' => 'Terminée',
                'skipped' => 'Non requise',
                'refused' => 'Refusée',
                'blocked' => 'Parcours arrêté',
                'unknown' => 'À vérifier',
                'current' => match ($status) {
                    'brouillon' => 'Brouillon à envoyer',
                    'validee_controle_avancee' => 'À démarrer',
                    'en_cours_traitement' => 'En cours',
                    'terminee_agent', 'cloturee' => 'À clôturer',
                    default => 'Avis attendu',
                },
                default => 'À venir',
            };
            $steps[] = compact('label', 'state', 'detail');
        }
        return $steps;
    }
}
