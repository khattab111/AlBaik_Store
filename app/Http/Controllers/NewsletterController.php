<?php

namespace App\Http\Controllers;

use App\Http\Requests\Storefront\NewsletterRequest;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;

class NewsletterController extends Controller
{
    public function store(NewsletterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $subscriber = NewsletterSubscriber::firstOrCreate(
            ['email' => $data['email']],
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
