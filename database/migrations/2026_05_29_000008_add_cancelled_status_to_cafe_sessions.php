<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE cafe_sessions MODIFY status ENUM('open', 'closed', 'cancelled') NOT NULL DEFAULT 'open'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE cafe_sessions MODIFY status ENUM('open', 'closed') NOT NULL DEFAULT 'open'");
        }
    }
};
