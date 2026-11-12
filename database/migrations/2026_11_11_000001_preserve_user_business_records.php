<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const REFERENCES = [
        'demandes' => 'user_id',
        'demandes_validations' => 'user_id',
        'validations' => 'validator_id',
        'demandes_pieces_jointes' => 'uploaded_by',
    ];

    public function up(): void
    {
        // MySQL DDL is not transactional: allow a safe retry after an interrupted run.
        if (! Schema::hasColumn('users', 'deactivated_at')) {
            Schema::table('users', fn (Blueprint $table) => $table->timestamp('deactivated_at')->nullable());
        }
        if (! Schema::hasIndex('users', ['deactivated_at'])) {
            Schema::table('users', fn (Blueprint $table) => $table->index('deactivated_at'));
        }
        foreach (self::REFERENCES as $tableName => $column) {
            Schema::table($tableName, function (Blueprint $table) use ($column) {
                $table->dropForeign([$column]);
                $table->foreign($column)->references('id')->on('users')->restrictOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (DB::table('users')->whereNotNull('deactivated_at')->exists()) {
            throw new RuntimeException('Rollback refusé : des comptes sont désactivés. Conserver leur blocage avant tout retour de version.');
        }
        foreach (self::REFERENCES as $tableName => $column) {
            Schema::table($tableName, function (Blueprint $table) use ($column, $tableName) {
                $table->dropForeign([$column]);
                $foreign = $table->foreign($column)->references('id')->on('users');
                // Revert to the canonical migrations, not the drifted local attachment FK.
                $tableName === 'demandes_pieces_jointes' ? $foreign->restrictOnDelete() : $foreign->cascadeOnDelete();
            });
        }
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('deactivated_at'));
    }
};
