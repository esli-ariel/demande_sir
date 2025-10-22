<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('demandes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Demandeur
            $table->string('nom');
            $table->string('structure')->nullable();
            $table->date('date_creation')->nullable();
            $table->string('unite_concernee')->nullable();
            $table->string('repere')->nullable();
            $table->string('fonction')->nullable();
            $table->text('motif')->nullable();
            $table->text('objet_modif')->nullable();
            $table->text('situation_existante')->nullable();
            $table->text('situation_souhaitee')->nullable();

            $table->enum('statut', [
                'brouillon','soumise',
                'validee_exploitation','refusee_exploitation',
                'validee_dts','refusee_dts',
                'validee_structure_specialisee','refusee_structure_specialisee',
                'validee_controle_avancee','refusee_controle_avancee',
                'en_cours_traitement','terminee_agent','cloturee_receptionnee'
            ])->default('brouillon');

            $table->string('numero_dma')->nullable();
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->text('travaux_realises')->nullable();

            $table->timestamps();
    

        });
    }
 

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demandes');
    }
};
