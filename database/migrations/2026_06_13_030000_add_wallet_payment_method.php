<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payment_methods')) {
            return;
        }

        $exists = DB::table('payment_methods')->where('slug', 'wallet')->exists();

        if ($exists) {
            DB::table('payment_methods')->where('slug', 'wallet')->update([
                'type' => 'wallet',
                'is_active' => true,
                'updated_at' => now(),
            ]);

            return;
        }

        DB::table('payment_methods')->insert([
            'name' => json_encode(['ar' => 'الدفع من المحفظة', 'en' => 'Pay with wallet'], JSON_UNESCAPED_UNICODE),
            'slug' => 'wallet',
            'description' => json_encode(['ar' => 'ادفع مباشرة من رصيد محفظتك داخل المتجر.', 'en' => 'Pay instantly from your store wallet balance.'], JSON_UNESCAPED_UNICODE),
            'type' => 'wallet',
            'settings' => json_encode([]),
            'fee' => 0,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        if (Schema::hasTable('payment_methods')) {
            DB::table('payment_methods')->where('slug', 'wallet')->delete();
        }
    }
};
