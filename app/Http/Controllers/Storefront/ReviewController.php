<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\ReviewRequest;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{
    public function store(ReviewRequest $request, Product $product): RedirectResponse
    {
        abort_unless($product->status, 404);

        Review::updateOrCreate(
            ['product_id' => $product->id, 'user_id' => $request->user()->id],
            [
                'rating' => $request->integer('rating'),
                'title' => $request->input('title'),
                'comment' => $request->input('comment'),
                'images' => [],
                'is_published' => false,
            ]
        );

        return back()->with('status', __('Review submitted and waiting for moderation.'));
    }
}
