<?php

namespace Tests\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

function resetWorkspaceDatabase(): User
{
    config([
        'database.default' => 'sqlite',
        'database.connections.sqlite.database' => ':memory:',
        'cache.default' => 'array',
        'session.driver' => 'array',
        'hashing.bcrypt.rounds' => 4,
    ]);
    DB::purge('sqlite');
    expect(DB::connection()->getDatabaseName())->toBe(':memory:');
    foreach ([
        '0001_01_01_000000_create_users_table.php',
        '2025_09_29_234425_create_demandes_table.php',
        '2025_09_30_000527_create_validations_table.php',
        '2025_09_30_151910_create_permission_tables.php',
        '2025_10_02_082301_create_demandes_pieces_jointes_table.php',
        '2025_11_06_090709_create_notifications_table.php',
        '2025_10_02_081753_create_demandes_validations_table.php',
        '2025_11_01_180445_create_structure_specialisees_table.php',
        '2025_11_01_182350_create_demande_structure_specialisee_table.php',
        '2025_12_12_104453_update_enum_statut_in_demandes_table.php',
        '2026_11_10_000001_link_specialist_accounts_and_decisions.php',
        '2026_11_11_000001_preserve_user_business_records.php',
        '2026_11_11_000002_secure_dma_numbering_and_constraints.php',
        '2026_11_11_000003_record_dma_final_closure.php',
    ] as $migration) {
        (require database_path('migrations/'.$migration))->up();
    }
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    foreach (['admin', 'demandeur', 'dts'] as $role) {
        Role::create(['name' => $role, 'guard_name' => 'web']);
    }
    $admin = User::create(['name' => 'Admin', 'prenom' => 'Test', 'email' => 'admin-test@example.invalid', 'password' => 'test-password']);
    $admin->assignRole('admin');
    return $admin;
}
