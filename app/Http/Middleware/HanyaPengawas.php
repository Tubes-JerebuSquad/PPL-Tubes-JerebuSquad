<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HanyaPengawas
{
    public function handle(Request $request, Closure $next): Response
    {
        $pengguna = $request->user();

        abort_unless(
            $pengguna && $pengguna->isAktif() && $pengguna->isPengawas(),
            403,
            'Akses ditolak.'
        );

        return $next($request);
    }
}
