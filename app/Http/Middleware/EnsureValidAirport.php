<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureValidAirport
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->airport_id && !auth()->user()->hasRole('Super Admin')) {
            return redirect()->route('profile.edit')
                ->with('error', 'Please update your airport information before continuing.');
        }

        return $next($request);
    }
}