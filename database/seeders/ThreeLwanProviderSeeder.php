<?php

namespace Database\Seeders;

use App\Models\ElectronicServiceProvider;
use App\Services\Providers\Gateways\ThreeLwanGateway;
use Illuminate\Database\Seeder;

class ThreeLwanProviderSeeder extends Seeder
{
    public function run(): void
    {
        ElectronicServiceProvider::query()->updateOrCreate(
            ['slug' => '3lwan-store'],
            [
                'name' => [
                    'ar' => 'مزود 3lwan Store',
                    'en' => '3lwan Store Provider',
                ],
                'type' => ElectronicServiceProvider::TYPE_API,
                'provider_type' => ElectronicServiceProvider::TYPE_CUSTOM_GATEWAY,
                'gateway_class' => ThreeLwanGateway::class,
                'base_url' => env('THREELWAN_BASE_URL', 'https://3lwan-store.com'),
                'auth_type' => ElectronicServiceProvider::AUTH_API_KEY_HEADER,
                'auth_config' => [
                    'header_name' => 'api-token',
                    'api_key' => env('THREELWAN_API_TOKEN'),
                ],
                'endpoints_config' => [
                    'balance' => [
                        'url' => '/client/api/profile',
                        'method' => 'GET',
                    ],
                    'services' => [
                        'url' => '/client/api/products',
                        'method' => 'GET',
                    ],
                    'create_order' => [
                        'url' => '/client/api/newOrder/{provider_service_id}/params',
                        'method' => 'POST',
                    ],
                    'status' => [
                        'url' => '/client/api/check?orders=[{provider_order_id}]',
                        'method' => 'GET',
                    ],
                ],
                'request_mapping' => [
                    'qty' => '{quantity}',
                    'playerId' => '{input.playerId}',
                    'order_uuid' => '{uuid}',
                ],
                'response_mapping' => [
                    'provider_service_id' => 'id',
                    'name' => 'name',
                    'cost_price' => 'price',
                    'category' => 'category_name',
                    'available' => 'available',
                    'params' => 'params',
                    'description' => 'category_name',
                    'image' => 'category_img',
                    'provider_order_id' => 'data.order_id',
                    'create_order.status' => 'data.status',
                    'status.status' => 'data.0.status',
                ],
                'status_mapping' => [
                    'accept' => 'processing',
                    'wait' => 'processing',
                    'reject' => 'failed',
                    'completed' => 'completed',
                    'complete' => 'completed',
                    'done' => 'completed',
                    'success' => 'completed',
                    'failed' => 'failed',
                    'fail' => 'failed',
                    'canceled' => 'cancelled',
                    'cancelled' => 'cancelled',
                ],
                'default_profit_type' => ElectronicServiceProvider::PROFIT_PERCENTAGE,
                'default_profit_value' => 15,
                'default_wholesale_profit_type' => ElectronicServiceProvider::PROFIT_PERCENTAGE,
                'default_wholesale_profit_value' => 8,
                'auto_sync_services' => true,
                'auto_sync_prices' => false,
                'status' => ElectronicServiceProvider::STATUS_ACTIVE,
                'admin_note' => 'Requires THREELWAN_API_TOKEN in .env before testing balance, syncing services, or creating orders.',
                'settings' => [
                    'timeout' => 30,
                    'docs_url' => 'https://3lwan-store.com/api-docs',
                    'provider_notes' => 'All requests require api-token header. New orders require a unique UUIDv4 order_uuid.',
                ],
            ],
        );
    }
}
