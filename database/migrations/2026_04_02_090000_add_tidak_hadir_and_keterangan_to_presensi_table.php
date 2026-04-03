<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('presensi')) {
            $driver = DB::getDriverName();

            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE presensi MODIFY status ENUM('hadir','terlambat','tidak_hadir') NOT NULL");
            }

            Schema::table('presensi', function (Blueprint $table) {
                if (!Schema::hasColumn('presensi', 'keterangan')) {
                    $table->enum('keterangan', ['tanpa_keterangan', 'sakit'])
                        ->default('tanpa_keterangan')
                        ->after('status');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('presensi')) {
            Schema::table('presensi', function (Blueprint $table) {
                if (Schema::hasColumn('presensi', 'keterangan')) {
                    $table->dropColumn('keterangan');
                }
            });

            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE presensi MODIFY status ENUM('hadir','terlambat') NOT NULL");
            }
        }
    }
};