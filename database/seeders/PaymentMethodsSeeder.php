<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodsSeeder extends Seeder
{
    use SeedsTranslations;

    public function run(): void
    {
        PaymentMethod::updateOrCreate(['slug' => 'cod'], [
            'name' => $this->tr('Cash on Delivery', 'الدفع عند الاستلام'),
            'type' => 'cod',
            'description' => $this->tr('Pay when your order is delivered.', 'ادفع عند استلام الطلب.'),
            'image' => 'demo/payments/cod.png',
            'wallet_url' => null,
            'barcode_image' => null,
            'settings' => [],
            'fee' => 0,
            'is_active' => true,
        ]);

        PaymentMethod::updateOrCreate(['slug' => 'bank-transfer'], [
            'name' => $this->tr('Bank Transfer', 'تحويل بنكي'),
            'type' => 'bank_transfer',
            'description' => $this->tr('Transfer to the store account and upload receipt.', 'حوّل إلى حساب المتجر ثم ارفع إشعار الدفع.'),
            'image' => 'demo/payments/bank.png',
            'wallet_url' => 'IBAN: TR00 0000 0000 0000 0000 0000 00',
            'barcode_image' => 'demo/payments/bank-qr.png',
            'settings' => ['bank_name' => 'AlBaik Demo Bank', 'iban' => 'TR00 0000 0000 0000 0000 0000 00'],
            'fee' => 0,
            'is_active' => true,
        ]);

        PaymentMethod::updateOrCreate(['slug' => 'manual-wallet'], [
            'name' => $this->tr('Manual Wallet', 'محفظة يدوية'),
            'type' => 'manual',
            'description' => $this->tr('Pay to wallet then upload receipt.', 'ادفع إلى المحفظة ثم ارفع صورة الإشعار.'),
            'image' => 'demo/payments/wallet.png',
            'wallet_url' => 'https://wallet.example/pay/albaik-electronics',
            'barcode_image' => 'demo/payments/wallet-qr.png',
            'settings' => ['instructions' => 'Upload receipt or contact support.'],
            'fee' => 1.50,
            'is_active' => true,
        ]);

        PaymentMethod::updateOrCreate(['slug' => 'wallet'], [
            'name' => $this->tr('Pay with wallet', 'الدفع من المحفظة'),
            'type' => 'wallet',
            'description' => $this->tr('Pay instantly from your store wallet balance.', 'ادفع مباشرة من رصيد محفظتك داخل المتجر.'),
            'image' => null,
            'wallet_url' => null,
            'barcode_image' => null,
            'settings' => [],
            'fee' => 0,
            'is_active' => true,
        ]);
    }
}
