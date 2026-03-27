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
        // Set waktu_scan to waktu if not already set
        DB::table('presensi')
            ->whereNull('waktu_scan')
            ->update(['waktu_scan' => DB::raw('CONCAT(tanggal, " ", waktu)')]);

        // Link presensi to presensi_sessions based on qr_session
        DB::statement(<<<'SQL'
            UPDATE presensi p
            SET p.session_id = (
                SELECT ps.id FROM presensi_sessions ps
                WHERE ps.kelas_id IN (
                    SELECT qs.kelas_id FROM qr_sessions qs
                    WHERE qs.id = p.qr_session_id
                )
                AND ps.is_active = false
                LIMIT 1
            )
            WHERE p.session_id IS NULL AND p.qr_session_id IS NOT NULL
        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is non-destructive - only fills in missing data
    }
};
