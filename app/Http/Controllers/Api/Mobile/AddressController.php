<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Api\Mobile\Concerns\RespondsToMobile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Mobile\AddressRequest;
use App\Http\Resources\Api\Mobile\AddressResource;
use App\Models\UserAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    use RespondsToMobile;

    public function index(Request $request): JsonResponse
    {
        $addresses = $request->user()
            ->addresses()
            ->with('city')
            ->where('is_active', true)
            ->latest('is_default')
            ->latest('id')
            ->get();

        return $this->success(AddressResource::collection($addresses));
    }

    public function store(AddressRequest $request): JsonResponse
    {
        $address = DB::transaction(function () use ($request): UserAddress {
            if ($request->boolean('is_default') || ! $request->user()->addresses()->exists()) {
                $request->user()->addresses()->update(['is_default' => false]);
            }

            return $request->user()->addresses()->create($this->payload($request));
        });

        return $this->success(new AddressResource($address->load('city')), __('Address saved successfully.'), 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $address = $request->user()->addresses()->with('city')->whereKey($id)->firstOrFail();

        return $this->success(new AddressResource($address));
    }

    public function update(AddressRequest $request, int $id): JsonResponse
    {
        $address = DB::transaction(function () use ($request, $id): UserAddress {
            $address = $request->user()->addresses()->whereKey($id)->firstOrFail();

            if ($request->boolean('is_default')) {
                $request->user()->addresses()->whereKeyNot($address->id)->update(['is_default' => false]);
            }

            $address->update($this->payload($request));

            return $address;
        });

        return $this->success(new AddressResource($address->load('city')), __('Address updated successfully.'));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $address = $request->user()->addresses()->whereKey($id)->firstOrFail();
        $address->update(['is_active' => false, 'is_default' => false]);

        return $this->success(null, __('Address deleted successfully.'));
    }

    public function setDefault(Request $request, int $id): JsonResponse
    {
        $address = DB::transaction(function () use ($request, $id): UserAddress {
            $address = $request->user()->addresses()->whereKey($id)->where('is_active', true)->firstOrFail();
            $request->user()->addresses()->whereKeyNot($address->id)->update(['is_default' => false]);
            $address->update(['is_default' => true]);

            return $address;
        });

        return $this->success(new AddressResource($address->load('city')), __('Default address updated successfully.'));
    }

    private function payload(AddressRequest $request): array
    {
        $data = $request->validated();

        return [
            'label' => $data['label'] ?? null,
            'recipient_name' => $data['full_name'],
            'phone' => $data['phone'],
            'city_id' => $data['city_id'],
            'address_line' => trim(($data['area'] ?? '').' '.$data['street']),
            'building_number' => $data['building'] ?? null,
            'floor' => $data['floor'] ?? null,
            'apartment' => $data['apartment'] ?? null,
            'notes' => $data['notes'] ?? null,
            'is_default' => (bool) ($data['is_default'] ?? false),
            'is_active' => true,
        ];
    }
}
