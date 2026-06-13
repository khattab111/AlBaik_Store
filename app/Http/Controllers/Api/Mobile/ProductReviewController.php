<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Api\Mobile\Concerns\RespondsToMobile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Mobile\ProductReviewRequest;
use App\Http\Resources\Api\Mobile\ProductReviewResource;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\JsonResponse;

class ProductReviewController extends Controller
{
    use RespondsToMobile;

    public function store(ProductReviewRequest $request, Product $product): JsonResponse
    {
        abort_unless($product->status, 404);

        $order = $request->eligibleOrder($product);

        $review = ProductReview::query()->create([
            'product_id' => $product->id,
            'user_id' => $request->user()->id,
            'order_id' => $order?->id,
            'rating' => (int) $request->validated('rating'),
            'title' => $request->validated('title'),
            'comment' => $request->validated('comment'),
            'status' => ProductReview::STATUS_PENDING,
        ]);

        foreach ($request->file('images', []) as $index => $image) {
            $review->images()->create([
                'path' => $image->store('product-reviews', 'public'),
                'sort_order' => $index,
            ]);
        }

        return $this->success(
            new ProductReviewResource($review->load('user')),
            __('Your review has been submitted and is waiting for approval'),
            201,
        );
    }
}
