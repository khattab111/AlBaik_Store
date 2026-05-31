<?php

namespace App\Http\Controllers\Storefront\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\AddressRequest;
use App\Models\City;
use App\Models\UserAddress;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request): View
    {
        return view('storefront.account.addresses', [
            'addresses' => $request->user()->addresses()->with('city')->where('is_active', true)->latest()->get(),
            'cities' => City::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function store(AddressRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_default'] = (bool) ($data['is_default'] ?? false);
        $data['is_active'] = true;

        if ($data['is_default']) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $request->user()->addresses()->create($data);

        return back()->with('status', __('Address added.'));
    }

    public function update(AddressRequest $request, UserAddress $address): RedirectResponse
    {
        abort_unless($address->user_id === $request->user()->id, 403);
        $data = $request->validated();
        $data['is_default'] = (bool) ($data['is_default'] ?? false);

        if ($data['is_default']) {
            $request->user()->addresses()->whereKeyNot($address->id)->update(['is_default' => false]);
        }

        $address->update($data);

        return back()->with('status', __('Address updated.'));
    }

    public function makeDefault(Request $request, UserAddress $address): RedirectResponse
    {
        abort_unless($address->user_id === $request->user()->id, 403);
        $request->user()->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true, 'is_active' => true]);

        return back()->with('status', __('Default address updated.'));
    }

    public function destroy(Request $request, UserAddress $address): RedirectResponse
    {
        abort_unless($address->user_id === $request->user()->id, 403);
        $address->update(['is_active' => false, 'is_default' => false]);

        return back()->with('status', __('Address deleted.'));
    }
}
