<?php

namespace Database\Seeders;

use App\Models\ElectronicService;
use App\Models\ElectronicServiceCategory;
use App\Models\ElectronicServiceProvider;
use Illuminate\Database\Seeder;

class ElectronicServicesSeeder extends Seeder
{
    public function run(): void
    {
        $provider = ElectronicServiceProvider::updateOrCreate(
            ['slug' => 'albaik-manual-team'],
            [
                'name' => ['ar' => 'فريق البيك', 'en' => 'AlBaik Team'],
                'type' => ElectronicServiceProvider::TYPE_MANUAL,
                'status' => ElectronicServiceProvider::STATUS_ACTIVE,
                'contact_email' => 'support@albaik.test',
            ],
        );

        $topups = ElectronicServiceCategory::updateOrCreate(
            ['slug' => 'account-topups'],
            [
                'name' => ['ar' => 'شحن الحسابات', 'en' => 'Account Top-ups'],
                'description' => ['ar' => 'خدمات شحن رصيد وحسابات رقمية.', 'en' => 'Wallet and digital account charging services.'],
                'icon' => '⚡',
                'sort_order' => 1,
                'is_active' => true,
            ],
        );

        $support = ElectronicServiceCategory::updateOrCreate(
            ['slug' => 'software-support'],
            [
                'name' => ['ar' => 'دعم برمجي', 'en' => 'Software Support'],
                'description' => ['ar' => 'خدمات ضبط وتهيئة للأجهزة والحسابات.', 'en' => 'Setup and configuration services for devices and accounts.'],
                'icon' => '🛠',
                'sort_order' => 2,
                'is_active' => true,
            ],
        );

        ElectronicService::updateOrCreate(
            ['slug' => 'mobile-balance-topup'],
            [
                'electronic_service_category_id' => $topups->id,
                'electronic_service_provider_id' => $provider->id,
                'name' => ['ar' => 'شحن رصيد موبايل', 'en' => 'Mobile Balance Top-up'],
                'description' => ['ar' => 'أرسل رقم الهاتف والشبكة وسيتم تنفيذ الشحن بعد مراجعة الطلب.', 'en' => 'Send the phone number and carrier, and the team will process the top-up after review.'],
                'instructions' => ['ar' => 'تأكد من صحة الرقم قبل إرسال الطلب.', 'en' => 'Please double-check the phone number before submitting.'],
                'service_type' => ElectronicService::TYPE_MANUAL,
                'price' => 10,
                'cost' => 9,
                'fields_schema' => [
                    ['name' => 'phone', 'label' => 'Phone number', 'type' => 'tel', 'required' => true],
                    ['name' => 'carrier', 'label' => 'Carrier', 'type' => 'select', 'options' => 'Syriatel, MTN, Other', 'required' => true],
                    ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea', 'required' => false],
                ],
                'requires_admin_review' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
        );

        ElectronicService::updateOrCreate(
            ['slug' => 'phone-software-setup'],
            [
                'electronic_service_category_id' => $support->id,
                'electronic_service_provider_id' => $provider->id,
                'name' => ['ar' => 'تهيئة هاتف وبرامج أساسية', 'en' => 'Phone Software Setup'],
                'description' => ['ar' => 'خدمة تهيئة الهاتف وتثبيت التطبيقات الأساسية عن بعد أو عند زيارة المتجر.', 'en' => 'Phone setup and essential app configuration service, remotely or in-store.'],
                'instructions' => ['ar' => 'اكتب موديل الهاتف وأي تطبيقات مطلوبة.', 'en' => 'Add the phone model and any requested apps.'],
                'service_type' => ElectronicService::TYPE_MANUAL,
                'price' => 15,
                'cost' => 0,
                'fields_schema' => [
                    ['name' => 'device_model', 'label' => 'Device model', 'type' => 'text', 'required' => true],
                    ['name' => 'request_details', 'label' => 'Request details', 'type' => 'textarea', 'required' => true],
                ],
                'requires_admin_review' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],
        );
    }
}
