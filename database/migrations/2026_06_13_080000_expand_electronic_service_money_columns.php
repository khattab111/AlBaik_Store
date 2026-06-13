<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['price', 'cost'] as $column) {
            DB::statement("ALTER TABLE electronic_services MODIFY {$column} DECIMAL(24,6) NOT NULL DEFAULT 0");
        }

        foreach (['min_amount', 'max_amount'] as $column) {
            DB::statement("ALTER TABLE electronic_services MODIFY {$column} DECIMAL(24,6) NULL");
        }

        foreach (['provider_cost_price', 'wholesale_price'] as $column) {
            DB::statement("ALTER TABLE electronic_services MODIFY {$column} DECIMAL(24,6) NOT NULL DEFAULT 0");
        }

        foreach (['amount', 'cost'] as $column) {
            DB::statement("ALTER TABLE electronic_service_orders MODIFY {$column} DECIMAL(24,6) NOT NULL DEFAULT 0");
        }

        foreach (['provider_cost_at_order', 'selling_price_at_order', 'profit_at_order', 'total'] as $column) {
            DB::statement("ALTER TABLE electronic_service_orders MODIFY {$column} DECIMAL(24,6) NOT NULL DEFAULT 0");
        }
    }

    public function down(): void
    {
        // Keep the wider precision to avoid truncating synced provider prices.
    }
};
