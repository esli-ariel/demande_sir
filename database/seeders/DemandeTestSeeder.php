<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Demande;

class DemandeTestSeeder extends Seeder
{
    public function run(): void
    {
        // Récupération ou création d’utilisateurs avec les rôles
        $demandeur   = User::firstOrCreate(
            ['email' => 'demandeur@test.com'],
            ['name' => 'Demandeur Test',
            'prenom' => 'Demandeur',
            'password' => bcrypt('password')]
        );
        $demandeur->syncRoles(['demandeur']);

        $exploitant  = User::firstOrCreate(
            ['email' => 'exploitant@test.com'],
            ['name' => 'Exploitant Test',
            'prenom' => 'Exploitant',
            'password' => bcrypt('password')]
        );
        $exploitant->syncRoles(['exploitant']);

        $dts  = User::firstOrCreate(
            ['email' => 'dts@test.com'],
            ['name' => 'DTS Test',
            'prenom' => 'DTS',
             'password' => bcrypt('password')]
        );
        $dts->syncRoles(['dts']);

        $specialisee  = User::firstOrCreate(
            ['email' => 'spec@test.com'],
            ['name' => 'Structure Spécialisée Test',
            'prenom' => 'Spécialisée',
            'password' => bcrypt('password')]
        );
        $specialisee->syncRoles(['structure_specialisee']);

        $controle  = User::firstOrCreate(
            ['email' => 'controle@test.com'],
            ['name' => 'Contrôle Avancé Test',
            'prenom' => 'Contrôle',
             'password' => bcrypt('password')]
        );
        $controle->syncRoles(['controle_avancee']);

        $technique  = User::firstOrCreate(
            ['email' => 'tech@test.com'],
            ['name' => 'Service Technique Test',
            'prenom' => 'Technique',
            'password' => bcrypt('password')]
        );
        $technique->syncRoles(['service_technique']);

        

        // Création de demandes à différents statuts
        $statuts = [
            'brouillon','soumise',
                'validee_exploitation','refusee_exploitation',
                'validee_dts','refusee_dts',
                'validee_structure_specialisee','refusee_structure_specialisee',
                'validee_controle_avancee','refusee_controle_avancee',
                'en_cours_traitement','terminee_agent','cloturee','cloturee_receptionnee'
        ];

        foreach ($statuts as $statut) {
            Demande::create([
                'user_id'             => $demandeur->id,
                'nom'                 => $demandeur->name,
                'structure'       => 'Structure Démo',
                'date_creation'       => now(),
                'unite_concernee'     => 'Unité Test',
                'repere'              => 'R-001',
                'fonction'            => 'Fonction Démo',
                'motif'               => 'Motif test pour statut ' . $statut,
                'objet_modif'         => 'Objet de la demande ' . $statut,
                'situation_existante' => 'Situation actuelle test',
                'situation_souhaitee' => 'Situation souhaitée test',
                'statut'              => $statut,
            ]);
        }
    }
}

