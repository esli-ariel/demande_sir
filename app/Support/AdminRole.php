<?php

namespace App\Support;

final class AdminRole
{
    public static function label(string $role): string
    {
        return match ($role) {
            'admin' => 'Administrateur',
            'demandeur' => 'Demandeur',
            'exploitant' => 'Exploitation',
            'dts' => 'DTS',
            'structure_specialisee' => 'Structure spécialisée',
            'controle_avancee' => 'Contrôle avancé',
            'service_technique' => 'Service technique',
            'chef_structure' => 'Chef de structure',
            'reception' => 'Réception',
            default => ucfirst(str_replace('_', ' ', $role)),
        };
    }
}
