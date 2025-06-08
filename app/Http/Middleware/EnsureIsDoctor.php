<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsDoctor
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->is_doctor) {
            return redirect()->route('dashboard')
                ->with('error', 'Доступ только для врачей');
        }

        return $next($request);
    }
}