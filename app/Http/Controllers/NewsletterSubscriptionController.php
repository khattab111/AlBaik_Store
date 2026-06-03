<?php

namespace App\Http\Controllers;

use App\Http\Requests\Storefront\NewsletterRequest;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NewsletterSubscriptionController extends Controller
{
    public function store(NewsletterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $subscriber = NewsletterSubscriber::firstOrNew(['email' => $data['email']]);
        $subscriber->fill([
            'name' => $data['name'] ?? $subscriber->name,
            'phone' => $data['phone'] ?? $subscriber->phone,
            'locale' => app()->getLocale(),
            'source' => $data['source'] ?? NewsletterSubscriber::SOURCE_HOMEPAGE,
            'status' => NewsletterSubscriber::STATUS_ACTIVE,
            'unsubscribed_at' => null,
        ]);
        $subscriber->save();

        return back()->with('status', __('You have successfully subscribed to the newsletter'));
    }

    public function unsubscribe(string $token): View
    {
        $subscriber = NewsletterSubscriber::where('unsubscribe_token', $token)->firstOrFail();
        $subscriber->unsubscribe();

        return view('newsletter.unsubscribed', [
            'subscriber' => $subscriber,
        ]);
    }
}
