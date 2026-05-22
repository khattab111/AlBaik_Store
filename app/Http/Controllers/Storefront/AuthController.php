<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\CustomerLoginRequest;
use App\Http\Requests\Storefront\CustomerRegisterRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function loginForm(): View
    {
        return view('storefront.auth.login');
    }

    public function login(CustomerLoginRequest $request): RedirectResponse
    {
        $credentials = $request->only(['email', 'password']);

        if (! Auth::attempt($credentials, (bool) $request->boolean('remember'))) {
            return back()->withErrors(['email' => __('Invalid login credentials.')])->onlyInput('email');
        }

        if (! $request->user()->status) {
            Auth::logout();

            return back()->withErrors(['email' => __('This account is disabled.')])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('account.dashboard'));
    }

    public function registerForm(): View
    {
        return view('storefront.auth.register');
    }

    public function register(CustomerRegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => (string) $request->string('name'),
            'email' => (string) $request->string('email'),
            'mobile' => $request->input('mobile'),
            'password' => Hash::make((string) $request->string('password')),
            'type' => 'customer',
            'status' => true,
        ]);

        $user->assignRole(Role::findOrCreate('Customer'));
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('account.dashboard');
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('home');
    }
}
