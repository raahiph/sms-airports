<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Filament\Facades\Filament;

class EnsureValidAirport
{
    public function handle(Request $request, Closure $next)
    {
        $user = Filament::auth()->user();

        if (!$user) {
            return redirect(Filament::getLoginUrl());
        }

        if (!$user->airport_id && !$user->hasRole('Super Admin')) {
            // You can also use notification instead of redirect
            Filament::notify('danger', 'Please update your airport information before continuing.');
            
            return redirect(Filament::getProfileUrl());
        }

        return $next($request);
    }
}