<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWholesaleCustomer
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return redirect()->guest(route('customer.login'));
        }

        abort_unless($request->user()->isWholesaleCustomer(), 403);

        return $next($request);
    }
}
