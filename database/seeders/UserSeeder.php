<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Crée le rôle admin si pas encore créé
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Crée un utilisateur admin par défaut
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Admin',
                'prenom' => 'Admin',
                'password' => Hash::make('password123'), 
            ]
        );

        // Assigner le rôle admin
       $admin->assignRole('admin');

       // Responsable
        $responsable = User::firstOrCreate(
            ['email' => 'responsable@example.com'],
            [
                'name' => 'Responsable Test',
                'prenom' => 'Responsable',
                'password' => Hash::make('password123'),
            ]
        );
        $responsable->assignRole('responsable_S');

        // Service technique
        $service = User::firstOrCreate(
            ['email' => 'service@example.com'],
            [
                'name' => 'Service Technique Test',
                'prenom' => 'Service',
                'password' => Hash::make('password123'),
            ]
        );
        $service->assignRole('service_technique');

        // Demandeur
        $demandeur = User::firstOrCreate(
            ['email' => 'demandeur@example.com'],
            [
                'name' => 'Demandeur Test',
                'prenom' => 'Demandeur',
                'password' => Hash::make('password123'),
            ]
        );
        $demandeur->assignRole('demandeur');
    
    }
}

