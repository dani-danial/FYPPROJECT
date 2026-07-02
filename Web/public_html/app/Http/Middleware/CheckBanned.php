<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckBanned
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->status === 'banned' || $user->status === 'ban') {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'This account has been banned.'
                    ], 403);
                }

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'This account has been banned.',
                ]);
            }

            // Reactivate inactive users, and update last_login_at at most once every 12 hours
            if ($user->status === 'inactive' || !$user->last_login_at || \Carbon\Carbon::parse($user->last_login_at)->lt(now()->subHours(12))) {
                $user->status = 'active';
                $user->last_login_at = now();
                $user->save();
            }
        }

        return $next($request);
    }
}
