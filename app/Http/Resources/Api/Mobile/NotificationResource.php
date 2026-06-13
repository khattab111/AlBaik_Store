<?php

namespace App\Http\Resources\Api\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = is_array($this->data) ? $this->data : [];

        return [
            'id' => $this->id,
            'type' => Str::afterLast($this->type, '\\'),
            'title' => $data['title'] ?? $this->fallbackTitle($data),
            'message' => $data['message'] ?? $data['body'] ?? $this->fallbackMessage($data),
            'data' => $data,
            'action_url' => $data['action_url'] ?? null,
            'is_read' => (bool) $this->read_at,
            'read_at' => $this->read_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }

    private function fallbackTitle(array $data): string
    {
        if (isset($data['order_number'])) {
            return __('Order update');
        }

        return __('Notification');
    }

    private function fallbackMessage(array $data): ?string
    {
        if (isset($data['order_number'], $data['status'])) {
            return __('Order :order is now :status.', [
                'order' => $data['order_number'],
                'status' => $data['status'],
            ]);
        }

        return null;
    }
}
