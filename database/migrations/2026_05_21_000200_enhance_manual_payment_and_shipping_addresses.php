<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->string('whatsapp')->nullable()->after('phone');
            $table->string('town')->nullable()->after('city');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_phone')->nullable()->after('notes');
            $table->string('customer_whatsapp')->nullable()->after('customer_phone');
            $table->string('shipping_country')->nullable()->after('customer_whatsapp');
            $table->string('shipping_city')->nullable()->after('shipping_country');
            $table->string('shipping_town')->nullable()->after('shipping_city');
            $table->string('shipping_street')->nullable()->after('shipping_town');
        });

        Schema::table('shipping_zones', function (Blueprint $table) {
            $table->string('town')->nullable()->after('city');
        });

        Schema::table('shipping_rules', function (Blueprint $table) {
            $table->string('calculation_type')->default('fixed')->after('shipping_zone_id');
            $table->decimal('cost_per_kg', 12, 2)->default(0)->after('cost');
            $table->boolean('is_free')->default(false)->after('cost_per_kg');
        });
    }

    public function down(): void
    {
        Schema::table('shipping_rules', function (Blueprint $table) {
            $table->dropColumn(['calculation_type', 'cost_per_kg', 'is_free']);
        });

        Schema::table('shipping_zones', function (Blueprint $table) {
            $table->dropColumn('town');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'customer_phone',
                'customer_whatsapp',
                'shipping_country',
                'shipping_city',
                'shipping_town',
                'shipping_street',
            ]);
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn(['whatsapp', 'town']);
        });
    }
};
