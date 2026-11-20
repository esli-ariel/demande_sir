<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['signer_name'=>511,'signer_structure_name'=>255,'visa_action'=>40,'status_before'=>80,'status_after'=>80] as $column=>$length) {
            if (! Schema::hasColumn('demandes_validations',$column)) {
                Schema::table('demandes_validations',fn(Blueprint $table)=>$table->string($column,$length)->nullable());
            }
        }
        if (! Schema::hasColumn('demandes_validations','signed_at')) {
            Schema::table('demandes_validations',fn(Blueprint $table)=>$table->dateTime('signed_at')->nullable());
        }
        Schema::table('demandes_validations',function (Blueprint $table) {
            $table->dropForeign(['demande_id']);
            $table->foreign('demande_id')->references('id')->on('demandes')->restrictOnDelete();
        });
        $driver=DB::connection()->getDriverName();
        foreach (['update','delete'] as $event) {
            DB::unprepared('DROP TRIGGER IF EXISTS dma_signed_visa_no_'.$event);
            if ($driver==='mysql') {
                DB::unprepared('CREATE TRIGGER dma_signed_visa_no_'.$event.' BEFORE '.strtoupper($event).' ON demandes_validations FOR EACH ROW BEGIN IF OLD.signed_at IS NOT NULL THEN SIGNAL SQLSTATE \'45000\' SET MESSAGE_TEXT = \'Un visa nominatif enregistre ne peut pas etre modifie ou supprime\'; END IF; END');
            } elseif ($driver==='sqlite') {
                DB::unprepared('CREATE TRIGGER dma_signed_visa_no_'.$event.' BEFORE '.strtoupper($event).' ON demandes_validations WHEN OLD.signed_at IS NOT NULL BEGIN SELECT RAISE(ABORT, \'Un visa nominatif enregistre ne peut pas etre modifie ou supprime\'); END');
            }
        }
        // No identity or timestamp is backfilled for historical free-text visas.
    }

    public function down(): void
    {
        if (DB::table('demandes_validations')->whereNotNull('signed_at')->exists()) {
            throw new RuntimeException('Rollback refusé : des visas nominatifs doivent être conservés.');
        }
        foreach (['update','delete'] as $event) DB::unprepared('DROP TRIGGER IF EXISTS dma_signed_visa_no_'.$event);
        Schema::table('demandes_validations',function (Blueprint $table) {
            $table->dropForeign(['demande_id']);
            $table->foreign('demande_id')->references('id')->on('demandes')->cascadeOnDelete();
            $table->dropColumn(['signer_name','signer_structure_name','visa_action','status_before','status_after','signed_at']);
        });
    }
};
