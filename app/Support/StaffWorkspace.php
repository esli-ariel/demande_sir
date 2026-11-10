<?php

namespace App\Support;

use App\Models\Demande;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class StaffWorkspace
{
    public const ROLES = [
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

        return Demande::query()->where('statut', '!=', 'brouillon');
    }
}
