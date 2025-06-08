<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfRegistrar
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
{
    if (Auth::check() && Auth::user()->hasRole('registrar')) {
        // Если пользователь УЖЕ на dashboard, пропускаем редирект
        if (!$request->routeIs('registrar.dashboard')) {
            return redirect()->route('registrar.dashboard');
        }
    }

    return $next($request);
}
}
