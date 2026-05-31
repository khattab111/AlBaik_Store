<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->string('recipient_name');
            $table->string('phone', 50);
            $table->foreignId('city_id')->constrained('cities')->restrictOnDelete();
            $table->string('address_line');
            $table->string('building_number')->nullable();
            $table->string('floor')->nullable();
            $table->string('apartment')->nullable();
            $table->string('landmark')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['user_id', 'is_default', 'is_active']);
        });

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'shipping_recipient_name')) {
                $table->string('shipping_recipient_name')->nullable()->after('customer_whatsapp');
            }

            if (! Schema::hasColumn('orders', 'shipping_phone')) {
                $table->string('shipping_phone', 50)->nullable()->after('shipping_recipient_name');
            }

            if (! Schema::hasColumn('orders', 'shipping_address_line')) {
                $table->string('shipping_address_line')->nullable()->after('shipping_city_name');
            }

            if (! Schema::hasColumn('orders', 'shipping_building_number')) {
                $table->string('shipping_building_number')->nullable()->after('shipping_address_line');
            }

            if (! Schema::hasColumn('orders', 'shipping_floor')) {
                $table->string('shipping_floor')->nullable()->after('shipping_building_number');
            }

            if (! Schema::hasColumn('orders', 'shipping_apartment')) {
                $table->string('shipping_apartment')->nullable()->after('shipping_floor');
            }

            if (! Schema::hasColumn('orders', 'shipping_landmark')) {
                $table->string('shipping_landmark')->nullable()->after('shipping_apartment');
            }

            if (! Schema::hasColumn('orders', 'shipping_notes')) {
                $table->text('shipping_notes')->nullable()->after('shipping_landmark');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_recipient_name',
                'shipping_phone',
                'shipping_address_line',
                'shipping_building_number',
                'shipping_floor',
                'shipping_apartment',
                'shipping_landmark',
                'shipping_notes',
            ]);
        });

        Schema::dropIfExists('user_addresses');
    }
};
