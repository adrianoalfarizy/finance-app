<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // MySQL: ubah enum untuk menambah value saving & debt
        DB::statement("
            ALTER TABLE accounts
            MODIFY COLUMN type ENUM('cash','bank','ewallet','saving','debt')
            NOT NULL DEFAULT 'cash'
        ");
    }

    public function down(): void
    {
        // Rollback ke semula (tanpa saving & debt)
        DB::statement("
            ALTER TABLE accounts
            MODIFY COLUMN type ENUM('cash','bank','ewallet')
            NOT NULL DEFAULT 'cash'
        ");
    }
};
