<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Set waktu_scan to waktu if not already set
        // Use Laravel query builder for SQLite compatibility
        $presensiRecords = DB::table('presensi')
            ->whereNull('waktu_scan')
            ->get();

        foreach ($presensiRecords as $record) {
            DB::table('presensi')
                ->where('id', $record->id)
                ->update([
                    'waktu_scan' => $record->tanggal.' '.$record->waktu,
                ]);
        }

        // Link presensi to presensi_sessions based on qr_session
        // Use PHP loop instead of MySQL-style UPDATE with subquery for SQLite compatibility
        $presensiRecords = DB::table('presensi')
            ->whereNull('session_id')
            ->whereNotNull('qr_session_id')
            ->get();

        foreach ($presensiRecords as $presensi) {
            // Get the kelas_id from qr_sessions
            $kelasId = DB::table('qr_sessions')
                ->where('id', $presensi->qr_session_id)
                ->value('kelas_id');

            if ($kelasId) {
                // Find the presensi_session
                $session = DB::table('presensi_sessions')
                    ->where('kelas_id', $kelasId)
                    ->where('is_active', false)
                    ->first();

                if ($session) {
                    DB::table('presensi')
                        ->where('id', $presensi->id)
                        ->update(['session_id' => $session->id]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is non-destructive - only fills in missing data
    }
};
