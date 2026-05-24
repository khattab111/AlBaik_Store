<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
        ]);

        $subscriber = NewsletterSubscriber::firstOrCreate(
            ['email' => mb_strtolower($data['email'])],
            ['locale' => app()->getLocale()]
        );

        return back()->with(
            'status',
            $subscriber->wasRecentlyCreated
                ? __('You have subscribed to the latest offers.')
                : __('This email is already subscribed.')
        );
    }
}
