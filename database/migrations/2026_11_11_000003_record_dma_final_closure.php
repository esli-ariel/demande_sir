<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Legacy dossiers keep an unknown closure date rather than an invented one.
        if (! Schema::hasColumn('demandes', 'closed_at')) {
            Schema::table('demandes', fn (Blueprint $table) => $table->dateTime('closed_at')->nullable());
        }
        if (! Schema::hasIndex('demandes', 'demandes_archive_date_index')) {
            Schema::table('demandes', fn (Blueprint $table) => $table->index(['statut','closed_at','id'], 'demandes_archive_date_index'));
        }
    }

    public function down(): void
    {
        if (DB::table('demandes')->whereNotNull('closed_at')->exists()) {
            throw new RuntimeException('Rollback refusé : préserver les dates de clôture déjà enregistrées.');
        }
        Schema::table('demandes', function (Blueprint $table) {
            $table->dropIndex('demandes_archive_date_index');
            $table->dropColumn('closed_at');
        });
    }
};
