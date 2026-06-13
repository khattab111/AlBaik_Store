<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\ReviewRequest;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function store(ReviewRequest $request, Product $product): RedirectResponse
    {
        abort_unless($product->status, 404);

        DB::transaction(function () use ($request, $product): void {
            $review = ProductReview::create([
                'product_id' => $product->id,
                'user_id' => $request->user()->id,
                'order_id' => $request->eligibleOrder($product)?->id,
                'rating' => $request->integer('rating'),
                'title' => $request->input('title'),
                'comment' => $request->input('comment'),
                'status' => ProductReview::STATUS_PENDING,
            ]);

            foreach ($request->file('images', []) as $index => $image) {
                $review->images()->create([
                    'path' => $image->store('product-reviews', 'public'),
                    'sort_order' => $index,
                ]);
            }
        });

        return back()->with('status', __('Your review has been submitted and is waiting for approval'));
    }
}
