<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ($user->hasRole('admin') || $user->hasRole('instructor'))) {
            if (is_null($user->two_factor_secret)) {
                return redirect()->route('security.edit')
                    ->with('error', 'You must enable Two-Factor Authentication to access this area.');
            }
        }

        return $next($request);
    }
}
