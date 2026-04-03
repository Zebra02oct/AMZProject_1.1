<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('presensi')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            if (Schema::hasColumn('presensi', 'keterangan')) {
                DB::statement("ALTER TABLE presensi MODIFY keterangan ENUM('tanpa_keterangan','sakit','izin') NOT NULL DEFAULT 'tanpa_keterangan'");
            } else {
                Schema::table('presensi', function (Blueprint $table) {
                    $table->enum('keterangan', ['tanpa_keterangan', 'sakit', 'izin'])
                        ->default('tanpa_keterangan')
                        ->after('status');
                });
            }

            return;
        }

        // Fallback for other drivers: try to use change() (requires doctrine/dbal)
        try {
            if (Schema::hasColumn('presensi', 'keterangan')) {
                Schema::table('presensi', function (Blueprint $table) {
                    $table->enum('keterangan', ['tanpa_keterangan', 'sakit', 'izin'])
                        ->default('tanpa_keterangan')
                        ->change();
                });
            } else {
                Schema::table('presensi', function (Blueprint $table) {
                    $table->enum('keterangan', ['tanpa_keterangan', 'sakit', 'izin'])
                        ->default('tanpa_keterangan')
                        ->after('status');
                });
            }
        } catch (\Throwable $e) {
            // If change() is not available or fails, developer intervention may be required.
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('presensi')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // Normalize any existing 'izin' values before altering enum
            DB::table('presensi')->where('keterangan', 'izin')->update(['keterangan' => 'tanpa_keterangan']);
            DB::statement("ALTER TABLE presensi MODIFY keterangan ENUM('tanpa_keterangan','sakit') NOT NULL DEFAULT 'tanpa_keterangan'");
            return;
        }

        try {
            DB::table('presensi')->where('keterangan', 'izin')->update(['keterangan' => 'tanpa_keterangan']);
            Schema::table('presensi', function (Blueprint $table) {
                $table->enum('keterangan', ['tanpa_keterangan', 'sakit'])
                    ->default('tanpa_keterangan')
                    ->change();
            });
        } catch (\Throwable $e) {
            // ignore
        }
    }
};
