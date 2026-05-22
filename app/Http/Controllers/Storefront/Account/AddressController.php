<?php

namespace App\Http\Controllers\Storefront\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\AddressRequest;
use App\Models\Address;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request): View
    {
        return view('storefront.account.addresses', [
            'addresses' => $request->user()->addresses()->latest()->get(),
        ]);
    }

    public function store(AddressRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_default'] = (bool) ($data['is_default'] ?? false);

        if ($data['is_default']) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $request->user()->addresses()->create($data);

        return back()->with('status', __('Address added.'));
    }

    public function destroy(Request $request, Address $address): RedirectResponse
    {
        abort_unless($address->user_id === $request->user()->id, 403);
        $address->delete();

        return back()->with('status', __('Address deleted.'));
    }
}
