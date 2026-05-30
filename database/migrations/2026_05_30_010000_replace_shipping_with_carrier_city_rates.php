<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->string('slug')->unique();
            $table->string('country');
            $table->string('code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->nullable();
            $table->timestamps();
        });

        Schema::create('shipping_carriers', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->string('tracking_url')->nullable();
            $table->string('api_endpoint')->nullable();
            $table->string('api_key')->nullable();
            $table->string('status')->default('active');
            $table->unsignedInteger('sort_order')->nullable();
            $table->timestamps();
        });

        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_carrier_id')->constrained('shipping_carriers')->cascadeOnDelete();
            $table->foreignId('city_id')->constrained('cities')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->decimal('base_cost', 12, 2)->default(0);
            $table->decimal('cost_per_kg', 12, 2)->default(0);
            $table->decimal('min_weight', 10, 3)->nullable();
            $table->decimal('max_weight', 10, 3)->nullable();
            $table->decimal('free_shipping_threshold', 12, 2)->nullable();
            $table->string('estimated_delivery_time')->nullable();
            $table->decimal('remote_area_fee', 12, 2)->nullable();
            $table->unsignedInteger('sort_order')->nullable();
            $table->timestamps();
            $table->unique(['shipping_carrier_id', 'city_id']);
        });

        Schema::table('addresses', function (Blueprint $table) {
            if (! Schema::hasColumn('addresses', 'city_id')) {
                $table->foreignId('city_id')->nullable()->after('country')->constrained('cities')->nullOnDelete();
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'length')) {
                $table->decimal('length', 10, 3)->nullable()->after('weight');
            }

            if (! Schema::hasColumn('products', 'width')) {
                $table->decimal('width', 10, 3)->nullable()->after('length');
            }

            if (! Schema::hasColumn('products', 'height')) {
                $table->decimal('height', 10, 3)->nullable()->after('width');
            }

            if (! Schema::hasColumn('products', 'requires_shipping')) {
                $table->boolean('requires_shipping')->default(true)->after('height');
            }

            if (! Schema::hasColumn('products', 'free_shipping')) {
                $table->boolean('free_shipping')->default(false)->after('requires_shipping');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'shipping_method_id')) {
                $table->dropConstrainedForeignId('shipping_method_id');
            }

            if (! Schema::hasColumn('orders', 'shipping_city_id')) {
                $table->foreignId('shipping_city_id')->nullable()->after('billing_address_id')->constrained('cities')->nullOnDelete();
            }

            if (! Schema::hasColumn('orders', 'shipping_city_name')) {
                $table->string('shipping_city_name')->nullable()->after('shipping_city_id');
            }

            if (! Schema::hasColumn('orders', 'shipping_carrier_id')) {
                $table->foreignId('shipping_carrier_id')->nullable()->after('shipping_city_name')->constrained('shipping_carriers')->nullOnDelete();
            }

            if (! Schema::hasColumn('orders', 'shipping_carrier_name')) {
                $table->string('shipping_carrier_name')->nullable()->after('shipping_carrier_id');
            }

            if (! Schema::hasColumn('orders', 'shipping_weight')) {
                $table->decimal('shipping_weight', 10, 3)->nullable()->after('shipping_cost');
            }

            if (! Schema::hasColumn('orders', 'shipping_delivery_time')) {
                $table->string('shipping_delivery_time')->nullable()->after('shipping_weight');
            }

            if (! Schema::hasColumn('orders', 'shipping_address_text')) {
                $table->text('shipping_address_text')->nullable()->after('shipping_delivery_time');
            }

            if (! Schema::hasColumn('orders', 'is_free_shipping')) {
                $table->boolean('is_free_shipping')->default(false)->after('shipping_address_text');
            }
        });

        Schema::dropIfExists('shipping_rules');
        Schema::dropIfExists('shipping_zones');
        Schema::dropIfExists('shipping_methods');
    }

    public function down(): void
    {
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('zone')->nullable();
            $table->string('type')->default('flat_rate');
            $table->decimal('cost', 12, 2)->default(0);
            $table->decimal('free_shipping_minimum', 12, 2)->nullable();
            $table->json('rules')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('town')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('shipping_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_method_id')->constrained('shipping_methods')->cascadeOnDelete();
            $table->foreignId('shipping_zone_id')->nullable()->constrained('shipping_zones')->nullOnDelete();
            $table->string('calculation_type')->default('fixed');
            $table->unsignedInteger('min_quantity')->nullable();
            $table->unsignedInteger('max_quantity')->nullable();
            $table->decimal('min_weight', 10, 3)->nullable();
            $table->decimal('max_weight', 10, 3)->nullable();
            $table->decimal('min_subtotal', 12, 2)->nullable();
            $table->decimal('cost', 12, 2)->default(0);
            $table->decimal('cost_per_kg', 12, 2)->default(0);
            $table->boolean('is_free')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('shipping_method_id')->nullable()->after('currency_id')->constrained('shipping_methods')->nullOnDelete();
            $table->dropConstrainedForeignId('shipping_city_id');
            $table->dropConstrainedForeignId('shipping_carrier_id');
            $table->dropColumn([
                'shipping_city_name',
                'shipping_carrier_name',
                'shipping_weight',
                'shipping_delivery_time',
                'shipping_address_text',
                'is_free_shipping',
            ]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['length', 'width', 'height', 'requires_shipping', 'free_shipping']);
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('city_id');
        });

        Schema::dropIfExists('shipping_rates');
        Schema::dropIfExists('shipping_carriers');
        Schema::dropIfExists('cities');
    }
};
