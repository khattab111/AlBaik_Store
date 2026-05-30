<?php

namespace App\Http\Controllers;

use App\Http\Requests\Storefront\ContactRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function create(): View
    {
        return view('pages.contact');
    }

    public function store(ContactRequest $request): RedirectResponse
    {
        Log::info('Store contact message received.', $request->validated());

        return back()->with('status', __('Your message has been received.'));
    }
}
