<?php

namespace App\Support;

final class DmaStatus
{
    public const GROUPS = [
        'brouillons' => ['brouillon'],
        'validation' => ['soumise', 'en_attente', 'en_attente_validation', 'validee_exploitation', 'validee_dts', 'validee_structure_specialisee'],
        'travaux' => ['validee_controle_avancee', 'en_cours_traitement'],
        'reception' => ['terminee_agent', 'cloturee'],
        'cloturees' => ['cloturee_receptionnee'],
        'refusees' => ['refusee', 'refusee_exploitation', 'refusee_dts', 'refusee_structure_specialisee', 'rejetee_structure_specialisee', 'refusee_controle_avancee'],
    ];

    public const GROUP_LABELS = [
        'brouillons' => 'Brouillons',
        'validation' => 'En validation',
        'travaux' => 'À traiter / en travaux',
        'reception' => 'À clôturer / réceptionner',
        'cloturees' => 'Clôturées et réceptionnées',
        'refusees' => 'Refusées',
        'autres' => 'Statut à vérifier',
    ];

    public static function group(string $status): string
    {
        foreach (self::GROUPS as $group => $statuses) {
            if (in_array($status, $statuses, true)) {
                return $group;
            }
        }

        return 'autres';
    }

    public static function summary(array $counts): array
    {
        $summary = array_fill_keys(array_keys(self::GROUP_LABELS), 0);
        foreach ($counts as $status => $count) {
            $summary[self::group((string) $status)] += (int) $count;
        }

        return $summary;
    }

    public static function label(string $status): string
    {
        return match ($status) {
            'brouillon' => 'Brouillon',
            'soumise', 'en_attente', 'en_attente_validation' => 'Avis exploitation attendu',
            'validee_exploitation' => 'Avis DTS attendu',
            'validee_dts' => 'Avis des structures attendu',
            'validee_structure_specialisee' => 'Avis contrôle avancé attendu',
            'validee_controle_avancee' => 'Prise en charge technique attendue',
            'en_cours_traitement' => 'Travaux en cours',
            'terminee_agent' => 'Travaux terminés · réception du demandeur attendue',
            'cloturee' => 'Réception du demandeur attendue',
            'cloturee_receptionnee' => 'Clôturée et réceptionnée',
            'refusee_exploitation' => 'Refusée par l’exploitation',
            'refusee_dts' => 'Refusée par la DTS',
            'refusee_structure_specialisee', 'rejetee_structure_specialisee' => 'Refusée par une structure',
            'refusee_controle_avancee' => 'Refusée par le contrôle avancé',
            'refusee' => 'Refusée',
            default => 'Statut à vérifier : '.str_replace('_', ' ', $status),
        };
    }
}
