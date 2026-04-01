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
        // Tambah kolom ke tabel presensi_sessions
        Schema::table('presensi_sessions', function (Blueprint $table) {
            $table->enum('tipe_sesi', ['harian', 'mapel'])->default('harian')->after('id');
            $table->foreignId('mapel_id')->nullable()->after('tipe_sesi')->constrained('mapels')->onDelete('cascade');
        });

        // Tambah kolom ke tabel presensi
        Schema::table('presensi', function (Blueprint $table) {
            $table->enum('tipe_sesi', ['harian', 'mapel'])->default('harian')->after('id');
            $table->foreignId('mapel_id')->nullable()->after('tipe_sesi')->constrained('mapels')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback untuk tabel 'presensi_sessions'
        Schema::table('presensi_sessions', function (Blueprint $table) {
            $table->dropForeign(['mapel_id']);
            $table->dropColumn(['tipe_sesi', 'mapel_id']);
        });

        // Rollback untuk tabel 'presensi'
        Schema::table('presensi', function (Blueprint $table) {
            $table->dropForeign(['mapel_id']);
            $table->dropColumn(['tipe_sesi', 'mapel_id']);
        });
    }
};