<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('pages.about');
    }

    public function accessibility(): View
    {
        return view('pages.accessibility');
    }

    public function privacy(): View
    {
        return view('pages.privacy');
    }

    public function returns(): View
    {
        return view('pages.returns');
    }

    public function shipping(): View
    {
        return view('pages.shipping');
    }

    public function terms(): View
    {
        return view('pages.terms');
    }
}
