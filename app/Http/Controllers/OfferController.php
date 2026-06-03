<?php

namespace App\Http\Controllers;

use App\Http\Requests\Storefront\OfferFilterRequest;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\FlashOffer;
use App\Models\Product;
use App\Presenters\FlashOfferPresenter;
use App\Repositories\CartRepository;
use App\Services\GuestCartService;
use App\Services\FlashOfferService;
use App\Services\OfferCartService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function index(OfferFilterRequest $request, FlashOfferService $flashOffers, FlashOfferPresenter $presenter): View
    {
        $locale = app()->getLocale();
        $filters = $request->filters();
        $perPage = (int) ($filters['per_page'] ?? 12);
        $products = Product::with(['brand', 'category', 'images', 'reviews', 'priceTiers', 'flashOfferItems.flashOffer.items.product.images'])
            ->where('status', true)
            ->whereHas('flashOfferItems.flashOffer', fn ($offer) => $offer->currentlyValid());

        $products->when($filters['category'] ?? null, fn ($builder, $categorySlug) => $builder->whereHas('category', fn ($category) => $category->where('slug', $categorySlug)));
        $products->when($filters['brand'] ?? null, fn ($builder, $brandSlug) => $builder->whereHas('brand', fn ($brand) => $brand->where('slug', $brandSlug)));
        $products->when($filters['type'] ?? null, fn ($builder, $type) => $builder->whereHas('flashOfferItems.flashOffer', fn ($offer) => $offer->currentlyValid()->where('type', $type)));

        match ($filters['sort'] ?? 'latest') {
            'price_desc' => $products->orderByDesc('retail_price'),
            'price_asc' => $products->orderBy('retail_price'),
            'ending_soon' => $products->whereHas('flashOfferItems.flashOffer', fn ($offer) => $offer->currentlyValid()->whereNotNull('ends_at'))->latest(),
            'highest_discount',
            'best_selling' => $products->latest(),
            default => $products->latest(),
        };

        $activeFlashOffers = $flashOffers->getActiveOffers();
        $presentedFlashOffers = $activeFlashOffers->map(fn ($offer) => $presenter->forOffer($offer));
        $filteredPresentedOffers = $presentedFlashOffers
            ->when($filters['type'] ?? null, fn ($offers, $type) => $offers->filter(fn ($offer) => $offer['type'] === $type))
            ->values();

        $products = $products->paginate($perPage)->withQueryString();
        $presentedProductOffers = $products->getCollection()
            ->map(function (Product $product) use ($presenter, $flashOffers): ?array {
                $offer = $product->flashOfferItems
                    ->pluck('flashOffer')
                    ->filter()
                    ->first(fn (FlashOffer $offer): bool => $flashOffers->isOfferValid($offer));

                if (! $offer) {
                    return null;
                }

                return array_merge($presenter->forProduct($offer, $product), [
                    'product' => $product,
                ]);
            })
            ->filter()
            ->values();

        return view('offers.index', [
            'products' => $products,
            'presentedProductOffers' => $presentedProductOffers,
            'categories' => Category::where('status', true)->orderBy("name->{$locale}")->get(),
            'brands' => Brand::where('status', true)->orderBy("name->{$locale}")->get(),
            'filters' => $filters,
            'flashOffers' => $activeFlashOffers,
            'presentedFlashOffers' => $filteredPresentedOffers,
            'allPresentedFlashOffers' => $presentedFlashOffers,
            'pageBanners' => Banner::activeNow()->forPlacement(Banner::PLACEMENT_OFFERS_TOP)->orderBy('sort_order')->get(),
        ]);
    }

    public function show(FlashOffer $flashOffer, FlashOfferService $flashOffers, FlashOfferPresenter $presenter): View
    {
        abort_unless($flashOffers->isOfferValid($flashOffer), 404);

        $flashOffer->load(['items.product.images', 'items.product.brand', 'items.product.reviews']);

        return view('offers.show', [
            'offer' => $flashOffer,
            'details' => $presenter->forOffer($flashOffer),
        ]);
    }

    public function addToCart(
        Request $request,
        FlashOffer $flashOffer,
        FlashOfferService $flashOffers,
        OfferCartService $offerCart,
        CartRepository $carts,
        GuestCartService $guestCart,
    ): RedirectResponse|JsonResponse {
        abort_unless($flashOffers->isOfferValid($flashOffer), 404);

        $data = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $quantity = (int) ($data['quantity'] ?? 1);

        if ($request->user()) {
            $cart = $carts->findForUser($request->user()->id);
            $offerCart->addOfferToCart($cart, $flashOffer, $quantity);

            $cartCount = $cart->items()->sum('quantity');
        } else {
            $guestCart->addOffer($flashOffer, $quantity);

            $cartCount = $guestCart->count();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('Offer added to cart.'),
                'cart_count' => $cartCount,
            ]);
        }

        return redirect()->route('cart.index')->with('status', __('Offer added to cart.'));
    }
}
