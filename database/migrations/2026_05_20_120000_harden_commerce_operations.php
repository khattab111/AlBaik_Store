<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'mobile')) {
                $table->string('mobile', 30)->nullable()->after('email');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('brand_id')->constrained('suppliers')->nullOnDelete();
            $table->decimal('weight', 10, 3)->default(0)->after('stock_quantity');
            $table->unsignedInteger('low_stock_threshold')->default(5)->after('weight');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->unsignedInteger('reserved_stock')->default(0)->after('stock');
            $table->unsignedInteger('low_stock_threshold')->default(5)->after('reserved_stock');
        });

        Schema::table('shipping_methods', function (Blueprint $table) {
            $table->json('rules')->nullable()->after('free_shipping_minimum');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_number')->unique()->nullable()->after('id');
            $table->decimal('payment_fee', 12, 2)->default(0)->after('discount_amount');
            $table->timestamp('paid_at')->nullable()->after('notes');
            $table->timestamp('shipped_at')->nullable()->after('paid_at');
            $table->timestamp('delivered_at')->nullable()->after('shipped_at');
            $table->timestamp('cancelled_at')->nullable()->after('delivered_at');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
            $table->string('driver')->default('manual');
            $table->string('status')->default('pending');
            $table->decimal('amount', 12, 2);
            $table->string('transaction_reference')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group')->default('general');
            $table->string('key')->unique();
            $table->json('value')->nullable();
            $table->string('type')->default('string');
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });

        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('image')->nullable();
            $table->string('url')->nullable();
            $table->string('placement')->default('home');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('payments');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['order_number', 'payment_fee', 'paid_at', 'shipped_at', 'delivered_at', 'cancelled_at']);
        });

        Schema::table('shipping_methods', function (Blueprint $table) {
            $table->dropColumn('rules');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['reserved_stock', 'low_stock_threshold']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('supplier_id');
            $table->dropColumn(['weight', 'low_stock_threshold']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('mobile');
        });

        Schema::dropIfExists('suppliers');
    }
};
