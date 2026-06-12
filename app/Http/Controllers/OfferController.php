<?php

namespace App\Http\Controllers;

use App\Http\Requests\Storefront\OfferFilterRequest;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\FlashOffer;
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
        $offers = FlashOffer::query()
            ->with(['items.product.images', 'items.product.brand', 'items.product.category', 'items.product.reviews'])
            ->currentlyValid()
            ->forAudience(FlashOffer::AUDIENCE_RETAIL);

        $offers->when($filters['category'] ?? null, fn ($builder, $categorySlug) => $builder
            ->whereHas('items.product.category', fn ($category) => $category->where('slug', $categorySlug)));
        $offers->when($filters['brand'] ?? null, fn ($builder, $brandSlug) => $builder
            ->whereHas('items.product.brand', fn ($brand) => $brand->where('slug', $brandSlug)));
        $offers->when($filters['type'] ?? null, fn ($builder, $type) => $builder->where('type', $type));

        match ($filters['sort'] ?? 'latest') {
            'price_desc' => $offers->orderByDesc('fixed_price')->orderByDesc('discount_value'),
            'price_asc' => $offers->orderBy('fixed_price')->orderBy('discount_value'),
            'ending_soon' => $offers->orderByRaw('ends_at IS NULL')->orderBy('ends_at'),
            'highest_discount' => $offers->orderByDesc('discount_value'),
            'best_selling' => $offers->orderByDesc('sold_quantity'),
            default => $offers->latest(),
        };

        $activeFlashOffers = $flashOffers->getActiveOffers(FlashOffer::AUDIENCE_RETAIL);
        $presentedFlashOffers = $activeFlashOffers->map(fn ($offer) => $presenter->forOffer($offer));
        $filteredPresentedOffers = $presentedFlashOffers
            ->when($filters['type'] ?? null, fn ($offers, $type) => $offers->filter(fn ($offer) => $offer['type'] === $type))
            ->values();

        $offersPaginator = $offers->paginate($perPage)->withQueryString();
        $presentedOffers = $offersPaginator->getCollection()
            ->map(fn (FlashOffer $offer): array => array_merge($presenter->forOffer($offer), [
                'product' => $offer->items->pluck('product')->filter()->first(),
            ]))
            ->values();

        return view('offers.index', [
            'offersPaginator' => $offersPaginator,
            'presentedOffers' => $presentedOffers,
            'categories' => Category::where('status', true)->orderBy("name->{$locale}")->get(),
            'brands' => Brand::where('status', true)->orderBy("name->{$locale}")->get(),
            'filters' => $filters,
            'flashOffers' => $activeFlashOffers,
            'presentedFlashOffers' => $filteredPresentedOffers,
            'allPresentedFlashOffers' => $presentedFlashOffers,
            'pageBanners' => Banner::activeNow()->forPlacement(Banner::PLACEMENT_OFFERS_TOP)->orderBy('sort_order')->get(),
        ]);
    }

    public function show(FlashOffer $flashOffer, Request $request, FlashOfferService $flashOffers, FlashOfferPresenter $presenter): View
    {
        abort_unless($flashOffers->isOfferValid($flashOffer, $this->audienceForRequest($request)), 404);

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
        abort_unless($flashOffers->isOfferValid($flashOffer, $this->audienceForRequest($request)), 404);

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

    private function audienceForRequest(Request $request): string
    {
        return $request->user()?->isWholesaleCustomer()
            ? FlashOffer::AUDIENCE_WHOLESALE
            : FlashOffer::AUDIENCE_RETAIL;
    }
}
