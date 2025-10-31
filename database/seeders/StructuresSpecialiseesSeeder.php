<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class StructuresSpecialiseesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

    
        $role = Role::firstOrCreate(['name' => 'structure_specialisee']);

        $structures = [
            ['nom' => 'DP-Mécanique', 'prenom' => 'Responsable Mécanique'],
            ['nom' => 'DP-Elec/Instrum', 'prenom' => 'Responsable Elec/Instrum'],
            ['nom' => 'DGA-Inspection', 'prenom' => 'Chef Inspection'],
            ['nom' => 'DGA-Sécurité', 'prenom' => 'Chef Sécurité'],
            ['nom' => 'DTS-Méthode Ctrl', 'prenom' => 'Resp Méthode Ctrl'],
            ['nom' => 'DTS-LABAN', 'prenom' => 'Resp Laban'],
            ['nom' => 'DTS-Process Ctrl', 'prenom' => 'Resp Process Ctrl'],
            ['nom' => 'DTS-Ingénierie', 'prenom' => 'Resp Ingénierie'],
            ['nom' => 'Autres', 'prenom' => 'Spécialiste Autre'],
        ];

        foreach ($structures as $structure) {
            $email = strtolower(str_replace([' ', '/', '-'], '_', $structure['nom'])) . '@sir.ci';

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $structure['nom'],
                    'prenom' => $structure['prenom'],
                    'password' => bcrypt('password123')
                ]
            );

            $user->assignRole($role);
        }
    }

    
}

