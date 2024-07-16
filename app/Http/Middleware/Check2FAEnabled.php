<?php

// In the Check2FAEnabled middleware file

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Check2FAEnabled
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && !Auth::user()->google2fa_enabled) {
            return redirect()->route('2fa.setup'); // or any other route you want to redirect to
        }

        return $next($request);
    }
}