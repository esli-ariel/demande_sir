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
    Schema::create('demandes_validations', function (Blueprint $table) {
        $table->id();
        $table->foreignId('demande_id')->constrained()->onDelete('cascade');
        $table->string('role');
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->enum('decision', ['accord','refus']);
        $table->string('visa')->nullable();
        $table->text('commentaire')->nullable();
        $table->date('date_validation')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demandes_validations');
    }
};
