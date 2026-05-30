<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flash_offers', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->string('slug')->unique();
            $table->json('description')->nullable();
            $table->string('type')->index();
            $table->string('status')->default('draft')->index();
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable()->index();
            $table->unsignedInteger('priority')->default(0)->index();
            $table->string('discount_type')->nullable();
            $table->decimal('discount_value', 12, 2)->nullable();
            $table->decimal('fixed_price', 12, 2)->nullable();
            $table->unsignedInteger('max_quantity')->nullable();
            $table->unsignedInteger('sold_quantity')->default(0);
            $table->boolean('free_shipping')->default(false);
            $table->decimal('min_order_amount', 12, 2)->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_per_user')->nullable();
            $table->timestamps();
        });

        Schema::create('flash_offer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flash_offer_id')->constrained('flash_offers')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('original_price', 12, 2)->nullable();
            $table->decimal('offer_price', 12, 2)->nullable();
            $table->boolean('is_free_item')->default(false);
            $table->timestamps();

            $table->index(['flash_offer_id', 'product_id']);
        });

        Schema::table('cart_items', function (Blueprint $table) {
            if (! Schema::hasColumn('cart_items', 'applied_flash_offer_id')) {
                $table->foreignId('applied_flash_offer_id')->nullable()->after('applied_tier_id')->constrained('flash_offers')->nullOnDelete();
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_items', 'applied_flash_offer_id')) {
                $table->foreignId('applied_flash_offer_id')->nullable()->after('applied_tier_id')->constrained('flash_offers')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'applied_flash_offer_id')) {
                $table->dropConstrainedForeignId('applied_flash_offer_id');
            }
        });

        Schema::table('cart_items', function (Blueprint $table) {
            if (Schema::hasColumn('cart_items', 'applied_flash_offer_id')) {
                $table->dropConstrainedForeignId('applied_flash_offer_id');
            }
        });

        Schema::dropIfExists('flash_offer_items');
        Schema::dropIfExists('flash_offers');
    }
};
