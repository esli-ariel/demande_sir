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
    Schema::create('demandes_pieces_jointes', function (Blueprint $table) {
        $table->id();
        $table->foreignId('demande_id')->constrained('demandes')->onDelete('cascade');
        $table->string('chemin_fichier');
        $table->string('type_document')->nullable();

        $table->foreignId('uploaded_by')->constrained('users')->onDelete('restrict');
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demandes_pieces_jointes');
    }
};
