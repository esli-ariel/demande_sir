<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        DB::unprepared('

            CREATE TRIGGER trg_before_insert_demandes
            BEFORE INSERT ON demandes
            FOR EACH ROW
            BEGIN
                DECLARE annee CHAR(4);
                DECLARE ordre INT;
                DECLARE prefixe VARCHAR(10);

                SET annee = YEAR(CURDATE());
                SET prefixe = "MS";

                SELECT COUNT(*) + 1 INTO ordre
                FROM demandes
                WHERE YEAR(date_creation) = annee;

                SET NEW.numero_dma = CONCAT(prefixe, annee, "/", LPAD(ordre, 4, "0"));
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        DB::unprepared('DROP TRIGGER IF EXISTS trg_before_insert_demandes;');
    }
};
