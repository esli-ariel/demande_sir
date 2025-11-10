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
        Schema::create('demande_structure_specialisee', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demande_id')->constrained()->onDelete('cascade');
            $table->foreignId('structure_id')->constrained('structures_specialisees')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demande_structure_specialisee');
    }
};
