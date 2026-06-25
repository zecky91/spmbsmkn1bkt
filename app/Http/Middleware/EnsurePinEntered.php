<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePinEntered
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->has('ruangan_id') || !$request->session()->has('pin_entered')) {
            return redirect()->route('pin.show');
        }

        return $next($request);
    }
}
