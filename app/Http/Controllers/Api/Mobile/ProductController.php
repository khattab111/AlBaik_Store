<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Api\Mobile\Concerns\RespondsToMobile;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Mobile\ProductDetailResource;
use App\Http\Resources\Api\Mobile\ProductResource;
use App\Models\FlashOffer;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use RespondsToMobile;

    public function index(Request $request): JsonResponse
    {
        $query = Product::query()
            ->where('status', true)
            ->with(['brand', 'category', 'images', 'priceTiers']);

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('name->ar', 'like', "%{$search}%")
                    ->orWhere('name->en', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($category = $request->string('category')->trim()->toString()) {
            $query->whereHas('category', fn (Builder $builder) => $builder->where('slug', $category)->orWhereKey($category));
        }

        if ($brand = $request->string('brand')->trim()->toString()) {
            $query->whereHas('brand', fn (Builder $builder) => $builder->where('slug', $brand)->orWhereKey($brand));
        }

        if ($request->filled('min_price')) {
            $query->where('retail_price', '>=', (float) $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('retail_price', '<=', (float) $request->input('max_price'));
        }

        if ($request->boolean('only_offers')) {
            $query->whereHas('flashOfferItems.offer', fn (Builder $builder) => $builder->currentlyValid());
        }

        match ($request->input('sort', 'latest')) {
            'price_asc' => $query->orderBy('retail_price'),
            'price_desc' => $query->orderByDesc('retail_price'),
            'rating' => $query->orderByDesc('average_rating')->orderByDesc('reviews_count'),
            'best_selling' => $query->withCount('orderItems')->orderByDesc('order_items_count'),
            default => $query->latest('id'),
        };

        return $this->success($this->paginated(
            $query->paginate((int) $request->input('per_page', 12)),
            ProductResource::class
        ));
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $product = Product::query()
            ->where('slug', $slug)
            ->where('status', true)
            ->with([
                'brand',
                'category',
                'images',
                'priceTiers',
                'approvedProductReviews' => fn ($query) => $query->with('user')->latest()->take(8),
            ])
            ->firstOrFail();

        $related = Product::query()
            ->where('status', true)
            ->whereKeyNot($product->id)
            ->where(function (Builder $builder) use ($product): void {
                $builder->where('category_id', $product->category_id)
                    ->orWhere('brand_id', $product->brand_id);
            })
            ->with(['brand', 'category', 'images', 'priceTiers'])
            ->take(8)
            ->get();

        $audience = $request->user()?->isWholesaleCustomer()
            ? FlashOffer::AUDIENCE_WHOLESALE
            : FlashOffer::AUDIENCE_RETAIL;

        $offers = FlashOffer::query()
            ->currentlyValid()
            ->forAudience($audience)
            ->whereHas('items', fn (Builder $builder) => $builder->where('product_id', $product->id))
            ->orderByDesc('priority')
            ->take(6)
            ->get();

        $product->setRelation('relatedProducts', $related);
        $product->setRelation('activeOffers', $offers);

        return $this->success(new ProductDetailResource($product));
    }
}
