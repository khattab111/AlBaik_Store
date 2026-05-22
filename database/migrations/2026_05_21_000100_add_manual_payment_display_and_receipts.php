<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->string('image')->nullable()->after('description');
            $table->string('wallet_url')->nullable()->after('image');
            $table->string('barcode_image')->nullable()->after('wallet_url');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('receipt_image')->nullable()->after('transaction_reference');
            $table->timestamp('submitted_at')->nullable()->after('receipt_image');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['receipt_image', 'submitted_at']);
        });

        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn(['image', 'wallet_url', 'barcode_image']);
        });
    }
};
