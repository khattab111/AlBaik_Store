<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('electronic_service_categories')) {
            Schema::create('electronic_service_categories', function (Blueprint $table): void {
                $table->id();
                $table->json('name');
                $table->string('slug')->unique();
                $table->json('description')->nullable();
                $table->string('icon')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('electronic_service_providers')) {
            Schema::create('electronic_service_providers', function (Blueprint $table): void {
                $table->id();
                $table->json('name');
                $table->string('slug')->unique();
                $table->string('type')->default('manual');
                $table->string('status')->default('active');
                $table->string('contact_name')->nullable();
                $table->string('contact_email')->nullable();
                $table->string('contact_phone')->nullable();
                $table->json('settings')->nullable();
                $table->text('admin_note')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('electronic_services')) {
            Schema::create('electronic_services', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('electronic_service_category_id')->constrained()->cascadeOnDelete();
                $table->foreignId('electronic_service_provider_id')->nullable()->constrained()->nullOnDelete();
                $table->json('name');
                $table->string('slug')->unique();
                $table->json('description')->nullable();
                $table->json('instructions')->nullable();
                $table->string('service_type')->default('manual');
                $table->decimal('price', 24, 6)->default(0);
                $table->decimal('cost', 24, 6)->default(0);
                $table->decimal('min_amount', 24, 6)->nullable();
                $table->decimal('max_amount', 24, 6)->nullable();
                $table->json('fields_schema')->nullable();
                $table->boolean('requires_admin_review')->default(true);
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('electronic_service_orders')) {
            Schema::create('electronic_service_orders', function (Blueprint $table): void {
                $table->id();
                $table->string('order_number')->unique();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('electronic_service_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('electronic_service_provider_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('wallet_transaction_id')->nullable()->constrained()->nullOnDelete();
                $table->json('service_snapshot');
                $table->json('customer_inputs')->nullable();
                $table->decimal('amount', 24, 6);
                $table->decimal('cost', 24, 6)->default(0);
                $table->string('status')->default('pending');
                $table->string('payment_status')->default('paid');
                $table->string('provider_reference')->nullable();
                $table->text('admin_note')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status']);
                $table->index(['electronic_service_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('electronic_service_orders');
        Schema::dropIfExists('electronic_services');
        Schema::dropIfExists('electronic_service_providers');
        Schema::dropIfExists('electronic_service_categories');
    }
};
