<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Api\Mobile\Concerns\RespondsToMobile;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Mobile\BannerResource;
use App\Http\Resources\Api\Mobile\BrandResource;
use App\Http\Resources\Api\Mobile\CategoryResource;
use App\Http\Resources\Api\Mobile\ElectronicServiceResource;
use App\Http\Resources\Api\Mobile\FlashOfferResource;
use App\Http\Resources\Api\Mobile\ProductResource;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ElectronicService;
use App\Models\FlashOffer;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    use RespondsToMobile;

    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $audience = $user?->isWholesaleCustomer() ? FlashOffer::AUDIENCE_WHOLESALE : FlashOffer::AUDIENCE_RETAIL;

        return $this->success([
            'banners' => BannerResource::collection(Banner::query()->activeNow()->forPlacement([
                Banner::PLACEMENT_HOME_HERO,
                Banner::PLACEMENT_HOME_AFTER_HERO,
            ])->orderBy('sort_order')->take(8)->get()),
            'featured_categories' => CategoryResource::collection(Category::query()
                ->where('status', true)
                ->whereNull('parent_id')
                ->withCount(['products' => fn ($query) => $query->where('status', true)])
                ->orderBy('id')
                ->take(12)
                ->get()),
            'featured_brands' => BrandResource::collection(Brand::query()
                ->where('status', true)
                ->withCount(['products' => fn ($query) => $query->where('status', true)])
                ->orderByDesc('products_count')
                ->take(12)
                ->get()),
            'featured_products' => ProductResource::collection(Product::query()
                ->where('status', true)
                ->where('is_featured', true)
                ->with(['brand', 'category', 'images', 'priceTiers'])
                ->latest('id')
                ->take(12)
                ->get()),
            'active_offers' => FlashOfferResource::collection(FlashOffer::query()
                ->currentlyValid()
                ->forAudience($audience)
                ->orderByDesc('priority')
                ->take(12)
                ->get()),
            'featured_services' => ElectronicServiceResource::collection(ElectronicService::query()
                ->where('is_active', true)
                ->where('is_visible', true)
                ->where('is_available', true)
                ->with('category')
                ->orderBy('sort_order')
                ->take(12)
                ->get()),
            'wallet_balance' => $user?->wallet ? (float) $user->wallet->balance : null,
            'unread_notifications_count' => $user?->unreadNotifications()->count() ?? 0,
        ]);
    }
}
