<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        $this->jsonize('suppliers', ['name' => 'required', 'address' => 'nullable']);
        $this->jsonize('warehouses', ['name' => 'required', 'address' => 'nullable', 'city' => 'nullable', 'country' => 'nullable']);
        $this->jsonize('currencies', ['name' => 'required']);
        $this->jsonize('reviews', ['title' => 'nullable', 'comment' => 'nullable']);
        $this->jsonize('product_images', ['alt_text' => 'nullable']);
        $this->jsonize('shipping_rates', ['estimated_delivery_time' => 'nullable']);
    }

    public function down(): void
    {
        if (! in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        $this->stringify('shipping_rates', ['estimated_delivery_time' => 'VARCHAR(255) NULL']);
        $this->stringify('product_images', ['alt_text' => 'VARCHAR(255) NULL']);
        $this->stringify('reviews', ['title' => 'VARCHAR(255) NULL', 'comment' => 'TEXT NULL']);
        $this->stringify('currencies', ['name' => 'VARCHAR(255) NOT NULL']);
        $this->stringify('warehouses', ['name' => 'VARCHAR(255) NOT NULL', 'address' => 'TEXT NULL', 'city' => 'VARCHAR(255) NULL', 'country' => 'VARCHAR(255) NULL']);
        $this->stringify('suppliers', ['name' => 'VARCHAR(255) NOT NULL', 'address' => 'TEXT NULL']);
    }

    /**
     * @param  array<string, string>  $columns
     */
    private function jsonize(string $table, array $columns): void
    {
        foreach ($columns as $column => $mode) {
            if (! Schema::hasColumn($table, $column)) {
                continue;
            }

            DB::statement("UPDATE {$table} SET {$column} = CASE WHEN {$column} IS NULL THEN NULL ELSE JSON_OBJECT('ar', {$column}, 'en', {$column}) END WHERE {$column} IS NULL OR JSON_VALID({$column}) = 0");

            $nullable = $mode === 'nullable' ? 'NULL' : 'NOT NULL';

            DB::statement("ALTER TABLE {$table} MODIFY {$column} JSON {$nullable}");
        }
    }

    /**
     * @param  array<string, string>  $columns
     */
    private function stringify(string $table, array $columns): void
    {
        foreach ($columns as $column => $definition) {
            if (! Schema::hasColumn($table, $column)) {
                continue;
            }

            DB::statement("ALTER TABLE {$table} MODIFY {$column} TEXT NULL");
            DB::statement("UPDATE {$table} SET {$column} = COALESCE(JSON_UNQUOTE(JSON_EXTRACT({$column}, '$.en')), JSON_UNQUOTE(JSON_EXTRACT({$column}, '$.ar')), {$column}) WHERE {$column} IS NOT NULL");
            DB::statement("ALTER TABLE {$table} MODIFY {$column} {$definition}");
        }
    }
};
