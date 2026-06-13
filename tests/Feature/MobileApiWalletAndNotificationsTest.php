<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WalletDepositRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MobileApiWalletAndNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_create_wallet_deposit_request_from_mobile_api(): void
    {
        $user = User::factory()->create(['status' => true]);

        Sanctum::actingAs($user);

        $this->postJson('/api/mobile/wallet/deposits', [
            'amount' => 25.50,
            'payment_method' => 'bank_transfer',
        ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', WalletDepositRequest::STATUS_PENDING)
            ->assertJsonPath('data.amount', 25.50);

        $this->assertDatabaseHas('wallet_deposit_requests', [
            'user_id' => $user->id,
            'amount' => 25.50,
            'payment_method' => 'bank_transfer',
            'status' => WalletDepositRequest::STATUS_PENDING,
        ]);

        $this->getJson('/api/mobile/wallet/deposits')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.meta.total', 1);
    }

    public function test_customer_can_list_and_mark_mobile_notifications_as_read(): void
    {
        $user = User::factory()->create(['status' => true]);

        $user->notify(new class extends Notification {
            public function via(object $notifiable): array
            {
                return ['database'];
            }

            public function toArray(object $notifiable): array
            {
                return [
                    'title' => 'Test notification',
                    'message' => 'A mobile notification was created.',
                ];
            }
        });

        Sanctum::actingAs($user);

        $this->getJson('/api/mobile/notifications/unread-count')
            ->assertOk()
            ->assertJsonPath('data.count', 1);

        $notificationId = $user->notifications()->firstOrFail()->id;

        $this->getJson('/api/mobile/notifications')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.items.0.id', $notificationId)
            ->assertJsonPath('data.items.0.is_read', false);

        $this->postJson('/api/mobile/notifications/read-all')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->getJson('/api/mobile/notifications/unread-count')
            ->assertOk()
            ->assertJsonPath('data.count', 0);
    }
}
