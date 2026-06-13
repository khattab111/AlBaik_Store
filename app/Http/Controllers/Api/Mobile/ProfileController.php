<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Api\Mobile\Concerns\RespondsToMobile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Mobile\ChangePasswordRequest;
use App\Http\Requests\Api\Mobile\UpdateProfileRequest;
use App\Http\Resources\Api\Mobile\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    use RespondsToMobile;

    public function show(Request $request): JsonResponse
    {
        return $this->success(new UserResource($request->user()->load(['wallet', 'addresses.city'])));
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $data = $request->validated();

        $request->user()->update([
            'name' => $data['name'] ?? $request->user()->name,
            'email' => array_key_exists('email', $data) ? $data['email'] : $request->user()->email,
            'mobile' => $data['phone'] ?? $request->user()->mobile,
        ]);

        return $this->success(new UserResource($request->user()->fresh()->load(['wallet', 'addresses.city'])), __('Profile updated successfully.'));
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $request->user()->forceFill([
            'password' => Hash::make($request->validated('password')),
        ])->save();

        return $this->success(null, __('Password changed successfully.'));
    }
}
