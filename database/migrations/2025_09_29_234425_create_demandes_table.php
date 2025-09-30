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
            $table->string('structure');
            $table->string('unite')->nullable();
            $table->string('objet');
            $table->text('motif')->nullable();
            $table->string('repere')->nullable();
            $table->text('situation_existante')->nullable();
            $table->text('situation_souhaitee')->nullable();
            $table->enum('statut', [
                'brouillon',
                'en_attente_validation',
                'validee_responsable',
                'rejete',
                'en_attente_technique',
                'validee_finale',
                'cloturee'
            ])->default('brouillon');
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
