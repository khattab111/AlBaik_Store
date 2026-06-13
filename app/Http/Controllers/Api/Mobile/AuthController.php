<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Api\Mobile\Concerns\RespondsToMobile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Mobile\ForgotPasswordRequest;
use App\Http\Requests\Api\Mobile\LoginRequest;
use App\Http\Requests\Api\Mobile\RegisterRequest;
use App\Http\Requests\Api\Mobile\ResetPasswordRequest;
use App\Http\Resources\Api\Mobile\UserResource;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use RespondsToMobile;

    public function register(RegisterRequest $request, WalletService $wallets): JsonResponse
    {
        $data = $request->validated();

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'mobile' => $data['phone'],
            'password' => Hash::make($data['password']),
            'type' => 'customer',
            'status' => true,
        ]);

        $wallets->getOrCreateWallet($user);
        $token = $user->createToken($request->input('device_name', 'mobile'))->plainTextToken;

        return $this->success([
            'token' => $token,
            'user' => new UserResource($user->load(['wallet', 'addresses.city'])),
            'customer_type' => $user->isWholesaleCustomer() ? 'wholesale' : 'retail',
            'wholesale_status' => null,
            'wallet_balance' => (float) $user->wallet?->balance,
        ], __('Account created successfully.'), 201);
    }

    public function login(LoginRequest $request, WalletService $wallets): JsonResponse
    {
        $data = $request->validated();
        $login = $data['login'];

        $user = User::query()
            ->where('email', $login)
            ->orWhere('mobile', $login)
            ->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'login' => [__('The provided credentials are incorrect.')],
            ]);
        }

        if (! $user->status) {
            return $this->failure(__('Your account is disabled.'), null, 403);
        }

        $wallets->getOrCreateWallet($user);
        $token = $user->createToken($data['device_name'] ?? 'mobile')->plainTextToken;

        return $this->success([
            'token' => $token,
            'user' => new UserResource($user->load(['wallet', 'addresses.city'])),
            'customer_type' => $user->isWholesaleCustomer() ? 'wholesale' : 'retail',
            'wholesale_status' => $user->isWholesaleCustomer() ? 'approved' : null,
            'wallet_balance' => (float) $user->wallet?->balance,
        ], __('Logged in successfully.'));
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return $this->success(null, __('Logged out successfully.'));
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success(new UserResource($request->user()->load(['wallet', 'addresses.city'])));
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $status = Password::sendResetLink($request->only('email'));

        if ($status !== Password::RESET_LINK_SENT) {
            return $this->failure(__($status), null, 422);
        }

        return $this->success(null, __($status));
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->validated(),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            return $this->failure(__($status), null, 422);
        }

        return $this->success(null, __($status));
    }
}
