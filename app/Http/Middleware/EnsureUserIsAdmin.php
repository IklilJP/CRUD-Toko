<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return redirect()->guest(route('home'))
                ->with('gagal', 'Silakan masuk dulu buat mengelola data.');
        }

        if (! $request->user()->isAdmin()) {
            abort(403, 'Halaman ini khusus admin.');
        }

        return $next($request);
    }
}
