<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE electronic_service_providers MODIFY auth_config LONGTEXT NULL');
    }

    public function down(): void
    {
        // Keep encrypted provider credentials intact. Converting back to JSON would
        // reject already-encrypted values on MySQL/MariaDB.
    }
};
