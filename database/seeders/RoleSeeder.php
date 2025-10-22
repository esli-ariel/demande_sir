<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RoleSeeder extends Seeder
{
public function run(): void
{
    $roles = [
        'demandeur',
        'exploitant',
        'dts',
        'structure_specialisee',
        'controle_avancee',
        'chef_structure',
        'service_technique',
        'admin'
    ];

Role::firstOrCreate(['name' => 'demandeur']);
Role::firstOrCreate(['name' => 'exploitant']);
Role::firstOrCreate(['name' => 'dts']);
Role::firstOrCreate(['name' => 'structure_specialisee']);
Role::firstOrCreate(['name' => 'controle_avancee']);
Role::firstOrCreate(['name' => 'service_technique']);
Role::firstOrCreate(['name' => 'reception']);
Role::firstOrCreate(['name' => 'admin']);
Role::firstOrCreate(['name' => 'chef_structure']);

}


}

