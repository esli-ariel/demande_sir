<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE demandes 
            MODIFY statut ENUM(
                'brouillon','soumise',
                'validee_exploitation','refusee_exploitation',
                'validee_dts','refusee_dts',
                'validee_structure_specialisee','refusee_structure_specialisee',
                'validee_controle_avancee','refusee_controle_avancee',
                'en_cours_traitement','terminee_agent',
                'cloturee_receptionnee','cloturee'
            ) DEFAULT 'brouillon'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE demandes 
            MODIFY statut ENUM(
                'brouillon','soumise',
                'validee_exploitation','refusee_exploitation',
                'validee_dts','refusee_dts',
                'validee_structure_specialisee','refusee_structure_specialisee',
                'validee_controle_avancee','refusee_controle_avancee',
                'en_cours_traitement','terminee_agent',
                'cloturee_receptionnee'
            ) DEFAULT 'brouillon'
        ");
    }
};
