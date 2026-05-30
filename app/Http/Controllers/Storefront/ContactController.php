<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\ContactRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function create(): View
    {
        return view('storefront.contact');
    }

    public function store(ContactRequest $request): RedirectResponse
    {
        Log::info('Storefront contact message received.', $request->validated());

        return back()->with('status', __('Your message has been received.'));
    }
}
