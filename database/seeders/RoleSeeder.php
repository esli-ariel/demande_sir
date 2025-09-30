<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'responsable_S']);
        Role::firstOrCreate(['name' => 'service_technique']);
        Role::firstOrCreate(['name' => 'user']);
        Role::firstOrCreate(['name' => 'demandeur']);
        Role::firstOrCreate(['name' => 'structure_specialise']);

         $user = User::find(1); // l’utilisateur avec ID 1
    $user->assignRole('admin');
    }

   
}

