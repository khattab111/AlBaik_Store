@props(['banners' => collect()])

@if ($banners->isNotEmpty())
    <section class="store-section py-6">
        <div class="grid gap-4 {{ $banners->count() > 1 ? 'lg:grid-cols-2' : '' }}">
            @foreach ($banners as $banner)
                @php
                    $image = $banner->image && file_exists(public_path('storage/'.$banner->image))
                        ? asset('storage/'.$banner->image)
                        : asset('images/storefront/hero-phone.svg');
                    $title = $banner->localized('title', __('Special offer'));
                    $subtitle = $banner->localized('subtitle', __('Discover selected products and limited store campaigns.'));
                    $eyebrow = $banner->localized('eyebrow', __('Featured'));
                    $primaryText = $banner->localized('primary_button_text', __('Shop Now'));
                    $secondaryText = $banner->localized('secondary_button_text', __('View Offers'));
                @endphp

                <article
                    class="store-panel overflow-hidden p-0"
                    style="background-color: {{ $banner->background_color ?: 'var(--store-surface)' }}; color: {{ $banner->text_color ?: 'var(--store-text)' }}"
                >
                    <div class="grid min-h-72 items-center gap-5 p-5 sm:p-7 lg:grid-cols-[minmax(0,1fr)_260px]">
                        <div class="min-w-0">
                            <p class="store-safe-text text-xs font-black uppercase tracking-normal text-amber-600">{{ $eyebrow }}</p>
                            <h2 class="store-safe-text mt-3 text-2xl font-black leading-tight sm:text-3xl">{{ $title }}</h2>
                            <p class="store-safe-text mt-3 max-w-xl text-sm font-bold leading-7 opacity-75">{{ $subtitle }}</p>
                            <div class="mt-5 flex flex-wrap gap-3">
                                <a href="{{ $banner->url ?: route('products.index') }}" class="store-button-primary w-full sm:w-auto">{{ $primaryText }}</a>
                                @if ($banner->secondary_url)
                                    <a href="{{ $banner->secondary_url }}" class="store-button-secondary w-full sm:w-auto">{{ $secondaryText }}</a>
                                @endif
                            </div>
                        </div>
                        <img src="{{ $image }}" alt="{{ $title }}" loading="lazy" decoding="async" class="mx-auto max-h-64 w-full object-contain">
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endif
