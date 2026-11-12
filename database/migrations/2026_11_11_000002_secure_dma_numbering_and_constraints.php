<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const STATUSES = [
        'brouillon','soumise','validee_exploitation','refusee_exploitation',
        'validee_dts','refusee_dts','validee_structure_specialisee','refusee_structure_specialisee',
        'validee_controle_avancee','refusee_controle_avancee','en_cours_traitement',
        'terminee_agent','cloturee_receptionnee','cloturee',
    ];

    public function up(): void
    {
        // Fail before changing the schema if existing data needs a business decision.
        if (DB::table('demandes')->select('numero_dma')->whereNotNull('numero_dma')->groupBy('numero_dma')->havingRaw('COUNT(*) > 1')->exists()
            || DB::table('demandes')->whereNull('statut')->exists()
            || DB::table('demande_structure_specialisee')->select('demande_id','structure_id')->groupBy('demande_id','structure_id')->havingRaw('COUNT(*) > 1')->exists()) {
            throw new RuntimeException('Numéros/affectations dupliqués ou statuts absents : qualifier les données avant migration. Aucune correction automatique.');
        }
        if (! Schema::hasTable('dma_number_counters')) {
            Schema::create('dma_number_counters', function (Blueprint $table) {
                $table->unsignedSmallInteger('year')->primary();
                $table->unsignedBigInteger('last_value');
            });
        }
        $maxima = [];
        DB::table('demandes')->select('id','numero_dma')->orderBy('id')->chunkById(500, function ($rows) use (&$maxima) {
            foreach ($rows as $row) {
                if (preg_match('~^MS([0-9]{4})/([0-9]+)$~D', $row->numero_dma ?? '', $match)) {
                    $year = (int) $match[1];
                    $maxima[$year] = max($maxima[$year] ?? 0, (int) $match[2]);
                }
            }
        });
        foreach ($maxima as $year => $value) {
            // Never reset an existing counter on a retry, even after a numbered row was deleted.
            $current = (int) (DB::table('dma_number_counters')->where('year',$year)->value('last_value') ?? 0);
            DB::table('dma_number_counters')->updateOrInsert(['year'=>$year],['last_value'=>max($current,$value)]);
        }
        if (! Schema::hasIndex('demandes','demandes_numero_dma_unique')) {
            Schema::table('demandes', fn (Blueprint $table) => $table->unique('numero_dma'));
        }
        if (! Schema::hasIndex('demande_structure_specialisee','demande_structure_specialisee_pair_unique')) {
            Schema::table('demande_structure_specialisee', fn (Blueprint $table) => $table->unique(['demande_id','structure_id'],'demande_structure_specialisee_pair_unique'));
        }
        // The composite unique index now serves this FK. Older databases may retain
        // an explicit redundant index whereas a fresh MySQL install drops its automatic one.
        if (Schema::hasIndex('demande_structure_specialisee','demande_structure_specialisee_demande_id_foreign')) {
            Schema::table('demande_structure_specialisee', fn (Blueprint $table) => $table->dropIndex('demande_structure_specialisee_demande_id_foreign'));
        }
        Schema::table('demandes', fn (Blueprint $table) => $table->enum('statut',self::STATUSES)->nullable(false)->default('brouillon')->change());
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::unprepared('DROP TRIGGER IF EXISTS trg_before_insert_demandes');
            DB::unprepared(<<<'SQL'
CREATE TRIGGER trg_before_insert_demandes
BEFORE INSERT ON demandes
FOR EACH ROW
BEGIN
    DECLARE current_year SMALLINT UNSIGNED;
    DECLARE sequence_value BIGINT UNSIGNED;
    IF NEW.numero_dma IS NULL OR NEW.numero_dma = '' THEN
        SET current_year = YEAR(UTC_TIMESTAMP());
        INSERT INTO dma_number_counters (`year`, `last_value`) VALUES (current_year, 1)
        ON DUPLICATE KEY UPDATE `last_value` = `last_value` + 1;
        SELECT `last_value` INTO sequence_value FROM dma_number_counters WHERE `year` = current_year;
        SET NEW.numero_dma = CONCAT('MS', current_year, '/', IF(sequence_value < 10000, LPAD(sequence_value, 4, '0'), CAST(sequence_value AS CHAR)));
    ELSEIF NEW.numero_dma REGEXP '^MS[0-9]{4}/[0-9]+$' THEN
        INSERT INTO dma_number_counters (`year`, `last_value`)
        VALUES (CAST(SUBSTRING(NEW.numero_dma, 3, 4) AS UNSIGNED), CAST(SUBSTRING(NEW.numero_dma, 8) AS UNSIGNED))
        ON DUPLICATE KEY UPDATE `last_value` = GREATEST(`last_value`, CAST(SUBSTRING(NEW.numero_dma, 8) AS UNSIGNED));
    END IF;
END
SQL);
        }
    }

    public function down(): void
    {
        throw new RuntimeException('Retour automatique refusé : préserver l’unicité et les compteurs DMA. Préparer une migration corrective ou une restauration contrôlée.');
    }
};
