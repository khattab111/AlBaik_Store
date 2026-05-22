<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Contracts\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('categories.index', [
            'categories' => Category::where('status', true)->withCount('products')->orderBy('name')->paginate(24),
        ]);
    }

    public function show(Category $category): View
    {
        abort_unless($category->status, 404);

        return view('categories.show', [
            'category' => $category,
            'products' => $category->products()->with(['images', 'brand', 'category'])->where('status', true)->latest()->paginate(12),
        ]);
    }
}
