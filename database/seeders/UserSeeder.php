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

       // dts
        $dts = User::firstOrCreate(
            ['email' => 'dts@example.com'],
            [
                'name' => 'dts Test',
                'prenom' => 'dts',
                'password' => Hash::make('password123'),
            ]
        );
        $dts->assignRole('dts');

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
       
        // Exploitant

    $responsable = User::firstOrCreate(
        ['email' => 'responsable@example.com'],
        ['name' => 'Responsable Exploitation',
            'prenom' => 'Responsable exploitation',
        'password' => Hash::make('password123')
        ]
    );
    $responsable->assignRole('exploitant');

     //structure_specialisee

    $structure_specialisee = User::firstOrCreate(
        ['email' => 'structure@example.com'],
        ['name' => 'structure_specialisee',
            'prenom' => 'structure_specialisee',
            'password' => Hash::make('password123')
        ]
    );
    $structure_specialisee->assignRole('structure_specialisee');
    
     //controle_avancee
    $controle_avancee = User::firstOrCreate(
        ['email' => 'controle_avancee@example.com'],
        ['name' => 'controle_avancee',
            'prenom' => 'controle_avancee',
        'password' => Hash::make('password123')
        ]
    );
    $controle_avancee->assignRole('controle_avancee');

        //chef_structure
    $chef_structure = User::firstOrCreate(
        ['email' => 'chef@example.com'],
        ['name' => 'chef_structure',
            'prenom' => 'chef_structure',
        'password' => Hash::make('password123')
        ]
    );
    $chef_structure->assignRole('chef_structure');


    }
}

