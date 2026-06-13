<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('electronic_service_providers', function (Blueprint $table): void {
            if (! Schema::hasColumn('electronic_service_providers', 'provider_type')) {
                $table->string('provider_type')->default('manual')->after('slug');
            }
            if (! Schema::hasColumn('electronic_service_providers', 'gateway_class')) {
                $table->string('gateway_class')->nullable()->after('provider_type');
            }
            if (! Schema::hasColumn('electronic_service_providers', 'base_url')) {
                $table->string('base_url')->nullable()->after('gateway_class');
            }
            if (! Schema::hasColumn('electronic_service_providers', 'auth_type')) {
                $table->string('auth_type')->default('no_auth')->after('base_url');
            }
            if (! Schema::hasColumn('electronic_service_providers', 'auth_config')) {
                $table->longText('auth_config')->nullable()->after('auth_type');
            }
            if (! Schema::hasColumn('electronic_service_providers', 'endpoints_config')) {
                $table->json('endpoints_config')->nullable()->after('auth_config');
            }
            if (! Schema::hasColumn('electronic_service_providers', 'request_mapping')) {
                $table->json('request_mapping')->nullable()->after('endpoints_config');
            }
            if (! Schema::hasColumn('electronic_service_providers', 'response_mapping')) {
                $table->json('response_mapping')->nullable()->after('request_mapping');
            }
            if (! Schema::hasColumn('electronic_service_providers', 'status_mapping')) {
                $table->json('status_mapping')->nullable()->after('response_mapping');
            }
            if (! Schema::hasColumn('electronic_service_providers', 'default_profit_type')) {
                $table->string('default_profit_type')->default('percentage')->after('status_mapping');
            }
            if (! Schema::hasColumn('electronic_service_providers', 'default_profit_value')) {
                $table->decimal('default_profit_value', 10, 2)->default(20)->after('default_profit_type');
            }
            if (! Schema::hasColumn('electronic_service_providers', 'default_wholesale_profit_type')) {
                $table->string('default_wholesale_profit_type')->default('percentage')->after('default_profit_value');
            }
            if (! Schema::hasColumn('electronic_service_providers', 'default_wholesale_profit_value')) {
                $table->decimal('default_wholesale_profit_value', 10, 2)->default(10)->after('default_wholesale_profit_type');
            }
            if (! Schema::hasColumn('electronic_service_providers', 'auto_sync_services')) {
                $table->boolean('auto_sync_services')->default(false)->after('default_wholesale_profit_value');
            }
            if (! Schema::hasColumn('electronic_service_providers', 'auto_sync_prices')) {
                $table->boolean('auto_sync_prices')->default(true)->after('auto_sync_services');
            }
            if (! Schema::hasColumn('electronic_service_providers', 'last_sync_at')) {
                $table->timestamp('last_sync_at')->nullable()->after('auto_sync_prices');
            }
        });

        Schema::table('electronic_services', function (Blueprint $table): void {
            if (! Schema::hasColumn('electronic_services', 'provider_service_id')) {
                $table->string('provider_service_id')->nullable()->after('electronic_service_provider_id');
            }
            if (! Schema::hasColumn('electronic_services', 'image')) {
                $table->string('image')->nullable()->after('description');
            }
            if (! Schema::hasColumn('electronic_services', 'provider_cost_price')) {
                $table->decimal('provider_cost_price', 24, 6)->default(0)->after('service_type');
            }
            if (! Schema::hasColumn('electronic_services', 'retail_profit_type')) {
                $table->string('retail_profit_type')->default('percentage')->after('provider_cost_price');
            }
            if (! Schema::hasColumn('electronic_services', 'retail_profit_value')) {
                $table->decimal('retail_profit_value', 10, 2)->default(20)->after('retail_profit_type');
            }
            if (! Schema::hasColumn('electronic_services', 'wholesale_profit_type')) {
                $table->string('wholesale_profit_type')->default('percentage')->after('retail_profit_value');
            }
            if (! Schema::hasColumn('electronic_services', 'wholesale_profit_value')) {
                $table->decimal('wholesale_profit_value', 10, 2)->default(10)->after('wholesale_profit_type');
            }
            if (! Schema::hasColumn('electronic_services', 'wholesale_price')) {
                $table->decimal('wholesale_price', 24, 6)->default(0)->after('price');
            }
            if (! Schema::hasColumn('electronic_services', 'required_fields')) {
                $table->json('required_fields')->nullable()->after('fields_schema');
            }
            if (! Schema::hasColumn('electronic_services', 'metadata')) {
                $table->json('metadata')->nullable()->after('required_fields');
            }
            if (! Schema::hasColumn('electronic_services', 'is_available')) {
                $table->boolean('is_available')->default(true)->after('metadata');
            }
            if (! Schema::hasColumn('electronic_services', 'is_visible')) {
                $table->boolean('is_visible')->default(true)->after('is_available');
            }

            if (Schema::hasColumn('electronic_services', 'provider_service_id')) {
                $table->unique(['electronic_service_provider_id', 'provider_service_id'], 'electronic_provider_service_unique');
            }
        });

        Schema::table('electronic_service_orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('electronic_service_orders', 'order_uuid')) {
                $table->uuid('order_uuid')->nullable()->unique()->after('order_number');
            }
            if (! Schema::hasColumn('electronic_service_orders', 'provider_order_id')) {
                $table->string('provider_order_id')->nullable()->after('provider_reference');
            }
            if (! Schema::hasColumn('electronic_service_orders', 'execution_type')) {
                $table->string('execution_type')->default('manual')->after('provider_order_id');
            }
            if (! Schema::hasColumn('electronic_service_orders', 'input_data')) {
                $table->json('input_data')->nullable()->after('customer_inputs');
            }
            if (! Schema::hasColumn('electronic_service_orders', 'quantity')) {
                $table->unsignedInteger('quantity')->default(1)->after('input_data');
            }
            if (! Schema::hasColumn('electronic_service_orders', 'provider_cost_at_order')) {
                $table->decimal('provider_cost_at_order', 24, 6)->default(0)->after('quantity');
            }
            if (! Schema::hasColumn('electronic_service_orders', 'selling_price_at_order')) {
                $table->decimal('selling_price_at_order', 24, 6)->default(0)->after('provider_cost_at_order');
            }
            if (! Schema::hasColumn('electronic_service_orders', 'profit_at_order')) {
                $table->decimal('profit_at_order', 24, 6)->default(0)->after('selling_price_at_order');
            }
            if (! Schema::hasColumn('electronic_service_orders', 'total')) {
                $table->decimal('total', 24, 6)->default(0)->after('profit_at_order');
            }
            if (! Schema::hasColumn('electronic_service_orders', 'provider_status')) {
                $table->string('provider_status')->nullable()->after('status');
            }
            if (! Schema::hasColumn('electronic_service_orders', 'provider_response')) {
                $table->json('provider_response')->nullable()->after('provider_status');
            }
            if (! Schema::hasColumn('electronic_service_orders', 'failure_reason')) {
                $table->text('failure_reason')->nullable()->after('provider_response');
            }
            if (! Schema::hasColumn('electronic_service_orders', 'processed_by')) {
                $table->foreignId('processed_by')->nullable()->after('failure_reason')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        // Columns are kept intentionally to avoid losing provider configuration and order audit data.
    }
};
