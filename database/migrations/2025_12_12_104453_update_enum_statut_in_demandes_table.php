<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const STATUSES = [
        'brouillon', 'soumise',
        'validee_exploitation', 'refusee_exploitation',
        'validee_dts', 'refusee_dts',
        'validee_structure_specialisee', 'refusee_structure_specialisee',
        'validee_controle_avancee', 'refusee_controle_avancee',
        'en_cours_traitement', 'terminee_agent', 'cloturee_receptionnee',
    ];

    public function up(): void
    {
        Schema::table('demandes', function (Blueprint $table) {
            $table->enum('statut', [...self::STATUSES, 'cloturee'])->default('brouillon')->change();
        });
    }

    public function down(): void
    {
        Schema::table('demandes', function (Blueprint $table) {
            $table->enum('statut', self::STATUSES)->default('brouillon')->change();
        });
    }
};
