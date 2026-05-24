<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function index(): View
    {
        $locale = app()->getLocale();

        return view('categories.index', [
            'categories' => Category::where('status', true)->withCount('products')->orderBy("name->{$locale}")->paginate(24),
        ]);
    }

    public function show(Category $category): View
    {
        abort_unless($category->status, 404);
        $category = Cache::remember("storefront.category.{$category->id}.v1", now()->addMinutes(10), fn () => $category);

        return view('categories.show', [
            'category' => $category,
            'products' => $category->products()->with(['images', 'brand', 'category', 'reviews'])->where('status', true)->latest()->paginate(12),
        ]);
    }
}
