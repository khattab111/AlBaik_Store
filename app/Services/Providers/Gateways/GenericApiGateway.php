<?php

namespace App\Services\Providers\Gateways;

use App\Models\ElectronicServiceOrder;
use App\Models\ElectronicServiceProvider;
use App\Services\Providers\Contracts\ProviderGatewayInterface;
use App\Services\Providers\DTOs\ProviderResponse;
use App\Services\Providers\DTOs\ProviderServiceDTO;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class GenericApiGateway implements ProviderGatewayInterface
{
    public function __construct(protected ElectronicServiceProvider $provider)
    {
    }

    public function testConnection(): ProviderResponse
    {
        if (! $this->endpoint('balance.url') && ! $this->endpoint('services.url')) {
            return ProviderResponse::failure(__('No balance or services endpoint configured.'));
        }

        return $this->getBalance();
    }

    public function getBalance(): ProviderResponse
    {
        $endpoint = $this->endpoint('balance.url');

        if (! $endpoint) {
            return ProviderResponse::failure(__('Balance endpoint is not configured.'));
        }

        return $this->request(
            $this->endpoint('balance.method', 'GET'),
            $endpoint,
        );
    }

    public function syncServices(): array
    {
        $endpoint = $this->endpoint('services.url');

        if (! $endpoint) {
            return [];
        }

        $response = $this->request($this->endpoint('services.method', 'GET'), $endpoint);

        if (! $response->successful) {
            return [];
        }

        $items = $this->extractList($response->data, $this->endpoint('services.list_path'));

        return collect($items)
            ->map(fn (array $item): ProviderServiceDTO => $this->mapService($item))
            ->filter(fn (ProviderServiceDTO $service): bool => filled($service->providerServiceId) && filled($service->name))
            ->values()
            ->all();
    }

    public function createOrder(ElectronicServiceOrder $order): ProviderResponse
    {
        $endpoint = $this->endpoint('create_order.url');

        if (! $endpoint) {
            return ProviderResponse::failure(__('Create order endpoint is not configured.'));
        }

        $payload = $this->mappedRequestPayload($order);
        $endpoint = $this->replaceTemplate($endpoint, $order);

        return $this->request(
            $this->endpoint('create_order.method', 'POST'),
            $endpoint,
            $payload,
            'create_order',
        );
    }

    public function checkOrderStatus(ElectronicServiceOrder $order): ProviderResponse
    {
        $endpoint = $this->endpoint('status.url');

        if (! $endpoint) {
            return ProviderResponse::failure(__('Status endpoint is not configured.'));
        }

        $payload = [
            'provider_order_id' => $order->provider_order_id ?: $order->provider_reference,
            'order_uuid' => $order->order_uuid,
        ];

        $url = Str::of($endpoint)
            ->replace('{provider_order_id}', (string) ($order->provider_order_id ?: $order->provider_reference))
            ->replace('{uuid}', (string) $order->order_uuid)
            ->toString();

        return $this->request(
            $this->endpoint('status.method', 'GET'),
            $url,
            $payload,
            'status',
        );
    }

    protected function request(string $method, string $endpoint, array $payload = [], ?string $mappingKey = null): ProviderResponse
    {
        try {
            $method = strtoupper($method);
            $client = $this->client();
            $url = $this->url($endpoint);

            if ($method === 'GET') {
                $response = $client->get($url, $this->queryPayload($payload));
            } else {
                $response = $client->send($method, $url, ['json' => $this->bodyPayload($payload)]);
            }

            $json = $response->json();
            $data = is_array($json) ? $json : ['raw' => $response->body()];

            $providerStatus = $this->extractProviderStatus($data, $mappingKey);
            $providerOrderId = $this->extractProviderOrderId($data);
            $message = (string) (data_get($data, 'message') ?? data_get($data, 'error') ?? $response->reason());

            if (! $response->successful()) {
                return ProviderResponse::failure($message, $data, $providerStatus);
            }

            return ProviderResponse::success($message, $data, $providerStatus, $providerOrderId);
        } catch (Throwable $exception) {
            return ProviderResponse::failure($exception->getMessage());
        }
    }

    protected function client(): PendingRequest
    {
        $client = Http::timeout((int) data_get($this->provider->settings, 'timeout', 30))
            ->acceptJson()
            ->asJson();

        $authConfig = $this->provider->auth_config ?? [];

        return match ($this->provider->auth_type) {
            ElectronicServiceProvider::AUTH_API_KEY_HEADER => $client->withHeaders([
                (string) data_get($authConfig, 'header_name', 'X-API-Key') => (string) data_get($authConfig, 'api_key'),
            ]),
            ElectronicServiceProvider::AUTH_BEARER_TOKEN => $client->withToken((string) data_get($authConfig, 'token')),
            ElectronicServiceProvider::AUTH_BASIC_AUTH => $client->withBasicAuth(
                (string) data_get($authConfig, 'username'),
                (string) data_get($authConfig, 'password'),
            ),
            ElectronicServiceProvider::AUTH_CUSTOM_HEADERS => $client->withHeaders((array) data_get($authConfig, 'headers', [])),
            default => $client,
        };
    }

    protected function queryPayload(array $payload): array
    {
        $authConfig = $this->provider->auth_config ?? [];

        if ($this->provider->auth_type === ElectronicServiceProvider::AUTH_QUERY_KEY) {
            $payload[(string) data_get($authConfig, 'key_name', 'key')] = (string) data_get($authConfig, 'key_value');
        }

        return $payload;
    }

    protected function bodyPayload(array $payload): array
    {
        $authConfig = $this->provider->auth_config ?? [];

        if ($this->provider->auth_type === ElectronicServiceProvider::AUTH_BODY_KEY) {
            $payload[(string) data_get($authConfig, 'key_name', 'key')] = (string) data_get($authConfig, 'key_value');
        }

        return $payload;
    }

    protected function url(string $endpoint): string
    {
        if (Str::startsWith($endpoint, ['http://', 'https://'])) {
            return $endpoint;
        }

        return rtrim((string) $this->provider->base_url, '/').'/'.ltrim($endpoint, '/');
    }

    protected function endpoint(string $key, mixed $default = null): mixed
    {
        $config = $this->provider->endpoints_config ?? [];

        return $config[$key] ?? data_get($config, $key, $default);
    }

    protected function mapService(array $item): ProviderServiceDTO
    {
        $mapping = $this->provider->response_mapping ?? [];
        $providerServiceId = (string) $this->mappedValue($item, $mapping, 'provider_service_id', 'id');
        $name = (string) $this->mappedValue($item, $mapping, 'name', 'name');
        $category = $this->mappedValue($item, $mapping, 'category', 'category');
        $costPrice = (float) $this->mappedValue($item, $mapping, 'cost_price', 'price', 0);
        $available = filter_var($this->mappedValue($item, $mapping, 'available', 'available', true), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $requiredFields = $this->mappedValue($item, $mapping, 'params', 'params', []);

        return new ProviderServiceDTO(
            providerServiceId: $providerServiceId,
            name: $name,
            category: is_scalar($category) ? (string) $category : null,
            costPrice: $costPrice,
            available: $available ?? true,
            requiredFields: $this->normalizeFields($requiredFields),
            metadata: $item,
            description: is_scalar($this->mappedValue($item, $mapping, 'description', 'description')) ? (string) $this->mappedValue($item, $mapping, 'description', 'description') : null,
            image: is_scalar($this->mappedValue($item, $mapping, 'image', 'image')) ? (string) $this->mappedValue($item, $mapping, 'image', 'image') : null,
        );
    }

    protected function mappedValue(array $item, array $mapping, string $target, string $fallback, mixed $default = null): mixed
    {
        $path = $mapping[$target] ?? $fallback;

        return data_get($item, $path, $default);
    }

    protected function normalizeFields(mixed $fields): array
    {
        if (! is_array($fields)) {
            return [];
        }

        return collect($fields)
            ->map(function ($field, $key): ?array {
                if (is_string($field)) {
                    return ['name' => $field, 'label' => Str::headline($field), 'type' => 'text', 'required' => true];
                }

                if (! is_array($field)) {
                    return null;
                }

                $name = (string) ($field['name'] ?? $field['key'] ?? $key);

                return [
                    'name' => $name,
                    'label' => (string) ($field['label'] ?? $field['title'] ?? Str::headline($name)),
                    'type' => (string) ($field['type'] ?? 'text'),
                    'options' => is_array($field['options'] ?? null) ? implode(',', $field['options']) : ($field['options'] ?? null),
                    'required' => (bool) ($field['required'] ?? true),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    protected function mappedRequestPayload(ElectronicServiceOrder $order): array
    {
        $mapping = $this->provider->request_mapping ?? [];

        if ($mapping === []) {
            return [
                'service_id' => $order->service?->provider_service_id,
                'quantity' => $order->quantity,
                'order_uuid' => $order->order_uuid,
                'input' => $order->input_data ?: $order->customer_inputs,
            ];
        }

        return collect($mapping)
            ->map(fn ($template) => $this->replaceTemplate((string) $template, $order))
            ->all();
    }

    protected function replaceTemplate(string $template, ElectronicServiceOrder $order): string
    {
        $input = $order->input_data ?: $order->customer_inputs ?: [];
        $replacements = [
            '{provider_service_id}' => (string) $order->service?->provider_service_id,
            '{service_id}' => (string) $order->service?->provider_service_id,
            '{quantity}' => (string) $order->quantity,
            '{uuid}' => (string) $order->order_uuid,
            '{order_uuid}' => (string) $order->order_uuid,
        ];

        foreach (Arr::dot($input) as $key => $value) {
            if (is_scalar($value)) {
                $replacements['{input.'.$key.'}'] = (string) $value;
            }
        }

        return strtr($template, $replacements);
    }

    protected function extractList(array $data, ?string $path = null): array
    {
        $list = $path ? data_get($data, $path, []) : $data;

        if (isset($list['data']) && is_array($list['data'])) {
            $list = $list['data'];
        }

        return is_array($list) ? array_values(array_filter($list, 'is_array')) : [];
    }

    protected function extractProviderStatus(array $data, ?string $mappingKey = null): ?string
    {
        $mapping = $this->provider->response_mapping ?? [];
        $statusKey = trim(($mappingKey ? "{$mappingKey}." : '').'status', '.');
        $path = ($mapping[$statusKey] ?? data_get($mapping, $statusKey))
            ?? ($mapping['status'] ?? data_get($mapping, 'status'))
            ?? 'status';

        $status = data_get($data, $path);

        return is_scalar($status) ? (string) $status : null;
    }

    protected function extractProviderOrderId(array $data): ?string
    {
        $mapping = $this->provider->response_mapping ?? [];
        $path = ($mapping['provider_order_id'] ?? data_get($mapping, 'provider_order_id'))
            ?? ($mapping['order_id'] ?? data_get($mapping, 'order_id'))
            ?? 'order_id';

        $id = data_get($data, $path);

        return is_scalar($id) ? (string) $id : null;
    }
}
