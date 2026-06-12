<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'is_wholesale_available')) {
                $table->boolean('is_wholesale_available')
                    ->default(false)
                    ->after('wholesale_minimum_quantity')
                    ->index();
            }
        });

        if (Schema::hasColumn('products', 'wholesale_price') && Schema::hasColumn('products', 'is_wholesale_available')) {
            DB::table('products')
                ->whereNotNull('wholesale_price')
                ->where('wholesale_price', '>', 0)
                ->update(['is_wholesale_available' => true]);
        }

        Schema::table('flash_offers', function (Blueprint $table): void {
            if (! Schema::hasColumn('flash_offers', 'audience')) {
                $table->string('audience', 20)
                    ->default('retail')
                    ->after('offer_scope')
                    ->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('flash_offers', function (Blueprint $table): void {
            if (Schema::hasColumn('flash_offers', 'audience')) {
                $table->dropColumn('audience');
            }
        });

        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'is_wholesale_available')) {
                $table->dropColumn('is_wholesale_available');
            }
        });
    }
};
