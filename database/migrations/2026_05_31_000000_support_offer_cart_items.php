<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('flash_offers', function (Blueprint $table) {
            if (! Schema::hasColumn('flash_offers', 'offer_scope')) {
                $table->string('offer_scope')->default('product')->after('type');
            }

            if (! Schema::hasColumn('flash_offers', 'free_shipping_scope')) {
                $table->string('free_shipping_scope')->default('none')->after('free_shipping');
            }
        });

        Schema::table('cart_items', function (Blueprint $table) {
            if (! Schema::hasColumn('cart_items', 'item_type')) {
                $table->string('item_type')->default('product')->after('cart_id');
            }

            if (! Schema::hasColumn('cart_items', 'offer_id')) {
                $table->foreignId('offer_id')->nullable()->after('product_id')->constrained('flash_offers')->nullOnDelete();
            }

            if (! Schema::hasColumn('cart_items', 'title')) {
                $table->string('title')->nullable()->after('offer_id');
            }

            if (! Schema::hasColumn('cart_items', 'original_total_price')) {
                $table->decimal('original_total_price', 12, 2)->nullable()->after('unit_price');
            }

            if (! Schema::hasColumn('cart_items', 'savings_amount')) {
                $table->decimal('savings_amount', 12, 2)->nullable()->after('original_total_price');
            }

            if (! Schema::hasColumn('cart_items', 'components_snapshot')) {
                $table->json('components_snapshot')->nullable()->after('savings_amount');
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_items', 'item_type')) {
                $table->string('item_type')->default('product')->after('order_id');
            }

            if (! Schema::hasColumn('order_items', 'offer_id')) {
                $table->foreignId('offer_id')->nullable()->after('product_id')->constrained('flash_offers')->nullOnDelete();
            }

            if (! Schema::hasColumn('order_items', 'offer_title')) {
                $table->string('offer_title')->nullable()->after('offer_id');
            }

            if (! Schema::hasColumn('order_items', 'offer_type')) {
                $table->string('offer_type')->nullable()->after('offer_title');
            }

            if (! Schema::hasColumn('order_items', 'offer_price')) {
                $table->decimal('offer_price', 12, 2)->nullable()->after('offer_type');
            }

            if (! Schema::hasColumn('order_items', 'original_total_price')) {
                $table->decimal('original_total_price', 12, 2)->nullable()->after('offer_price');
            }

            if (! Schema::hasColumn('order_items', 'savings_amount')) {
                $table->decimal('savings_amount', 12, 2)->nullable()->after('original_total_price');
            }

            if (! Schema::hasColumn('order_items', 'components_snapshot')) {
                $table->json('components_snapshot')->nullable()->after('savings_amount');
            }
        });

        $this->makeProductNullable('cart_items');
        $this->makeProductNullable('order_items');
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            foreach (['components_snapshot', 'savings_amount', 'original_total_price', 'offer_price', 'offer_type', 'offer_title'] as $column) {
                if (Schema::hasColumn('order_items', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (Schema::hasColumn('order_items', 'offer_id')) {
                $table->dropConstrainedForeignId('offer_id');
            }

            if (Schema::hasColumn('order_items', 'item_type')) {
                $table->dropColumn('item_type');
            }
        });

        Schema::table('cart_items', function (Blueprint $table) {
            foreach (['components_snapshot', 'savings_amount', 'original_total_price', 'title'] as $column) {
                if (Schema::hasColumn('cart_items', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (Schema::hasColumn('cart_items', 'offer_id')) {
                $table->dropConstrainedForeignId('offer_id');
            }

            if (Schema::hasColumn('cart_items', 'item_type')) {
                $table->dropColumn('item_type');
            }
        });

        Schema::table('flash_offers', function (Blueprint $table) {
            foreach (['free_shipping_scope', 'offer_scope'] as $column) {
                if (Schema::hasColumn('flash_offers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function makeProductNullable(string $table): void
    {
        try {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropForeign([$table === 'cart_items' ? 'product_id' : 'product_id']));
        } catch (Throwable) {
            //
        }

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->foreignId('product_id')->nullable()->change();
        });

        Schema::table($table, function (Blueprint $blueprint) use ($table) {
            $blueprint->foreign('product_id', $table.'_product_id_foreign')->references('id')->on('products')->cascadeOnDelete();
        });
    }
};
