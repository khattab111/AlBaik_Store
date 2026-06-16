<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        $notNullColumns = [
            'electronic_services' => ['price', 'cost', 'provider_cost_price', 'wholesale_price'],
            'electronic_service_orders' => ['amount', 'cost', 'provider_cost_at_order', 'selling_price_at_order', 'profit_at_order', 'total'],
        ];

        $nullableColumns = [
            'electronic_services' => ['min_amount', 'max_amount'],
        ];

        if ($driver === 'mysql') {
            foreach ($notNullColumns as $table => $columns) {
                foreach ($columns as $column) {
                    if (Schema::hasColumn($table, $column)) {
                        DB::statement("ALTER TABLE {$table} MODIFY {$column} DECIMAL(24,6) NOT NULL DEFAULT 0");
                    }
                }
            }

            foreach ($nullableColumns as $table => $columns) {
                foreach ($columns as $column) {
                    if (Schema::hasColumn($table, $column)) {
                        DB::statement("ALTER TABLE {$table} MODIFY {$column} DECIMAL(24,6) NULL");
                    }
                }
            }

            return;
        }

        if ($driver === 'pgsql') {
            foreach ($notNullColumns as $table => $columns) {
                foreach ($columns as $column) {
                    if (Schema::hasColumn($table, $column)) {
                        DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} TYPE NUMERIC(24,6) USING {$column}::numeric");
                        DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} SET DEFAULT 0");
                        DB::statement("UPDATE {$table} SET {$column} = 0 WHERE {$column} IS NULL");
                        DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} SET NOT NULL");
                    }
                }
            }

            foreach ($nullableColumns as $table => $columns) {
                foreach ($columns as $column) {
                    if (Schema::hasColumn($table, $column)) {
                        DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} TYPE NUMERIC(24,6) USING {$column}::numeric");
                        DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} DROP NOT NULL");
                    }
                }
            }
        }
    }

    public function down(): void
    {
        //
    }
};
