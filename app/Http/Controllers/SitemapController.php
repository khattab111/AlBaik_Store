<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class SitemapController extends Controller
{
    private const CHUNK_SIZE = 1000;

    public function index(): View
    {
        return view('pages.sitemap', [
            'sections' => $this->visibleSections(),
        ]);
    }

    public function xml(): Response
    {
        return $this->xmlResponse('sitemap.index', [
            'sitemaps' => $this->sitemapIndexItems(),
        ]);
    }

    public function pages(): Response
    {
        return $this->urlsetResponse($this->pageUrls());
    }

    public function products(int $page): Response
    {
        return $this->dynamicUrlsetResponse(
            Product::where('status', true)
                ->select(['slug', 'updated_at'])
                ->latest('updated_at'),
            $page,
            fn (Product $product) => $this->urlItem(
                'products.show',
                $product->slug,
                'weekly',
                '0.8',
                $product->updated_at?->toDateString()
            )
        );
    }

    public function categories(int $page): Response
    {
        return $this->dynamicUrlsetResponse(
            Category::where('status', true)
                ->select(['slug', 'updated_at'])
                ->latest('updated_at'),
            $page,
            fn (Category $category) => $this->urlItem(
                'categories.show',
                $category->slug,
                'weekly',
                '0.7',
                $category->updated_at?->toDateString()
            )
        );
    }

    public function brands(int $page): Response
    {
        return $this->dynamicUrlsetResponse(
            Brand::where('status', true)
                ->select(['slug', 'updated_at'])
                ->latest('updated_at'),
            $page,
            fn (Brand $brand) => $this->urlItem(
                'brands.show',
                $brand->slug,
                'weekly',
                '0.7',
                $brand->updated_at?->toDateString()
            )
        );
    }

    private function sitemapIndexItems(): Collection
    {
        $sitemaps = collect([
            $this->sitemapItem('sitemap.pages', [], now()->toDateString()),
        ]);

        $this->appendDynamicSitemaps($sitemaps, 'sitemap.products', Product::where('status', true));
        $this->appendDynamicSitemaps($sitemaps, 'sitemap.categories', Category::where('status', true));
        $this->appendDynamicSitemaps($sitemaps, 'sitemap.brands', Brand::where('status', true));

        return $sitemaps;
    }

    private function pageUrls(): Collection
    {
        return collect([
            $this->urlItem('home', [], 'daily', '1.0'),
            $this->urlItem('products.index', [], 'daily', '0.9'),
            $this->urlItem('offers.index', [], 'daily', '0.8'),
            $this->urlItem('categories.index', [], 'weekly', '0.8'),
            $this->urlItem('brands.index', [], 'weekly', '0.8'),
            $this->urlItem('about', [], 'monthly', '0.6'),
            $this->urlItem('contact', [], 'monthly', '0.6'),
            $this->urlItem('sitemap.index', [], 'weekly', '0.5'),
            $this->urlItem('accessibility', [], 'monthly', '0.5'),
            $this->urlItem('privacy', [], 'monthly', '0.4'),
            $this->urlItem('returns', [], 'monthly', '0.4'),
            $this->urlItem('shipping.policy', [], 'monthly', '0.4'),
            $this->urlItem('terms', [], 'monthly', '0.4'),
        ]);
    }

    private function visibleSections(): array
    {
        return [
            [
                'title' => __('Store'),
                'description' => __('Core shopping pages and current offers.'),
                'links' => [
                    ['label' => __('Home'), 'url' => route('home')],
                    ['label' => __('Products'), 'url' => route('products.index')],
                    ['label' => __('Offers'), 'url' => route('offers.index')],
                    ['label' => __('Categories'), 'url' => route('categories.index')],
                    ['label' => __('Brands'), 'url' => route('brands.index')],
                ],
            ],
            [
                'title' => __('Support'),
                'description' => __('Company information, contact, and accessibility resources.'),
                'links' => [
                    ['label' => __('About'), 'url' => route('about')],
                    ['label' => __('Contact'), 'url' => route('contact')],
                    ['label' => __('Accessibility'), 'url' => route('accessibility')],
                    ['label' => __('Privacy Policy'), 'url' => route('privacy')],
                    ['label' => __('Returns Policy'), 'url' => route('returns')],
                    ['label' => __('Shipping Policy'), 'url' => route('shipping.policy')],
                    ['label' => __('Terms'), 'url' => route('terms')],
                    ['label' => __('Sitemap'), 'url' => route('sitemap.index')],
                ],
            ],
            [
                'title' => __('Popular Categories'),
                'description' => __('Active departments from the storefront catalog.'),
                'links' => Category::where('status', true)
                    ->orderBy('name')
                    ->take(12)
                    ->get(['name', 'slug'])
                    ->map(fn (Category $category) => [
                        'label' => $category->name,
                        'url' => route('categories.show', $category->slug),
                    ])
                    ->all(),
            ],
            [
                'title' => __('Trusted brands'),
                'description' => __('Active brand collections and partner products.'),
                'links' => Brand::where('status', true)
                    ->orderBy('name')
                    ->take(16)
                    ->get(['name', 'slug'])
                    ->map(fn (Brand $brand) => [
                        'label' => $brand->name,
                        'url' => route('brands.show', $brand->slug),
                    ])
                    ->all(),
            ],
        ];
    }

    private function urlItem(string $route, mixed $parameters = [], string $changefreq = 'weekly', string $priority = '0.5', ?string $lastmod = null): array
    {
        return [
            'loc' => $this->absoluteUrl(route($route, $parameters, false)),
            'lastmod' => $lastmod ?? now()->toDateString(),
            'changefreq' => $changefreq,
            'priority' => $priority,
        ];
    }

    private function sitemapItem(string $route, mixed $parameters = [], ?string $lastmod = null): array
    {
        return [
            'loc' => $this->absoluteUrl(route($route, $parameters, false)),
            'lastmod' => $lastmod ?? now()->toDateString(),
        ];
    }

    private function appendDynamicSitemaps(Collection $sitemaps, string $route, Builder $query): void
    {
        $total = (clone $query)->count();

        if ($total < 1) {
            return;
        }

        $lastmod = $this->dateString((clone $query)->max('updated_at'));
        $lastPage = (int) ceil($total / self::CHUNK_SIZE);

        for ($page = 1; $page <= $lastPage; $page++) {
            $sitemaps->push($this->sitemapItem($route, ['page' => $page], $lastmod));
        }
    }

    private function dynamicUrlsetResponse(Builder $query, int $page, callable $urlFactory): Response
    {
        $total = (clone $query)->count();
        $lastPage = (int) ceil($total / self::CHUNK_SIZE);

        if ($page < 1 || $total < 1 || $page > $lastPage) {
            abort(404);
        }

        $urls = (clone $query)
            ->forPage($page, self::CHUNK_SIZE)
            ->get()
            ->map($urlFactory);

        return $this->urlsetResponse($urls);
    }

    private function urlsetResponse(Collection $urls): Response
    {
        return $this->xmlResponse('sitemap.xml', ['urls' => $urls]);
    }

    private function xmlResponse(string $view, array $data): Response
    {
        return response()
            ->view($view, $data, 200)
            ->header('Content-Type', 'application/xml');
    }

    private function dateString(mixed $date): string
    {
        return $date ? Carbon::parse($date)->toDateString() : now()->toDateString();
    }

    private function absoluteUrl(string $path): string
    {
        return rtrim((string) config('app.url'), '/').'/'.ltrim($path, '/');
    }
}
