<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql' || ! Schema::hasTable('users')) {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY role ENUM('Admin','Guru','Siswa') NOT NULL DEFAULT 'Siswa'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql' || ! Schema::hasTable('users')) {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY role ENUM('Admin','Guru') NOT NULL DEFAULT 'Guru'");
    }
};
