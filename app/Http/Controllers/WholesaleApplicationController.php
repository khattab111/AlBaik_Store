<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWholesaleApplicationRequest;
use App\Models\WholesaleApplication;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class WholesaleApplicationController extends Controller
{
    public function create(): View
    {
        return view('pages.join-us');
    }

    public function store(StoreWholesaleApplicationRequest $request): RedirectResponse
    {
        WholesaleApplication::create([
            ...$request->validated(),
            'status' => WholesaleApplication::STATUS_PENDING,
        ]);

        return back()->with('status', __('Your partnership request has been received and is pending review.'));
    }
}
