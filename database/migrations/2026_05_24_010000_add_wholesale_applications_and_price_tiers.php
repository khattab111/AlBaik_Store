<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wholesale_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('full_name');
            $table->string('email')->index();
            $table->string('phone', 50);
            $table->string('whatsapp', 50)->nullable();
            $table->string('business_name');
            $table->string('business_type');
            $table->string('city');
            $table->text('address');
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->string('status')->default('pending')->index();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('product_price_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('type')->default('retail')->index();
            $table->unsignedInteger('min_quantity')->default(1);
            $table->decimal('price', 12, 2);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['product_id', 'type', 'min_quantity']);
        });

        Schema::table('cart_items', function (Blueprint $table) {
            if (! Schema::hasColumn('cart_items', 'price_type')) {
                $table->string('price_type')->default('retail')->after('unit_price');
            }

            if (! Schema::hasColumn('cart_items', 'applied_tier_id')) {
                $table->foreignId('applied_tier_id')->nullable()->after('price_type')->constrained('product_price_tiers')->nullOnDelete();
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_items', 'price_type')) {
                $table->string('price_type')->default('retail')->after('unit_price');
            }

            if (! Schema::hasColumn('order_items', 'applied_tier_id')) {
                $table->foreignId('applied_tier_id')->nullable()->after('price_type')->constrained('product_price_tiers')->nullOnDelete();
            }

            if (! Schema::hasColumn('order_items', 'subtotal')) {
                $table->decimal('subtotal', 12, 2)->default(0)->after('applied_tier_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'applied_tier_id')) {
                $table->dropConstrainedForeignId('applied_tier_id');
            }

            $columns = array_values(array_filter([
                Schema::hasColumn('order_items', 'price_type') ? 'price_type' : null,
                Schema::hasColumn('order_items', 'subtotal') ? 'subtotal' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });

        Schema::table('cart_items', function (Blueprint $table) {
            if (Schema::hasColumn('cart_items', 'applied_tier_id')) {
                $table->dropConstrainedForeignId('applied_tier_id');
            }

            if (Schema::hasColumn('cart_items', 'price_type')) {
                $table->dropColumn('price_type');
            }
        });

        Schema::dropIfExists('product_price_tiers');
        Schema::dropIfExists('wholesale_applications');
    }
};
