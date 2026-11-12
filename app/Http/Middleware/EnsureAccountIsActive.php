<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $current = $user?->fresh();
        if ($user && (! $current || $current->deactivated_at !== null)) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return $request->expectsJson()
                ? response()->json(['message' => 'Ce compte est désactivé.'], 401)
                : redirect()->route('login')->withErrors(['email' => 'Ce compte est désactivé. Contactez votre administrateur.']);
        }
        return $next($request);
    }
}
