<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsPembeli
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return redirect()->guest(route('login'))
                ->with('gagal', 'Masuk dulu buat mulai belanja.');
        }

        if ($request->user()->isAdmin()) {
            abort(403, 'Halaman belanja khusus pembeli. Akun admin dipakai buat mengelola toko.');
        }

        return $next($request);
    }
}
