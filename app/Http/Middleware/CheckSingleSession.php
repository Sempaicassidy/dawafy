<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSingleSession
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
            $currentSessionId = session()->getId();

            // If user's session_id in database is set and does not match this session,
            // it means another session or admin reset has occurred.
            if ($user->session_id && $user->session_id !== $currentSessionId) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'username' => 'Kikao chako kimefungwa kwa sababu akaunti hii imeingia kutoka kwenye kifaa kingine.',
                ]);
            }

            // Update heartbeat every 60 seconds
            if (!$user->last_activity || now()->diffInSeconds($user->last_activity) >= 60) {
                $user->timestamps = false;
                $user->last_activity = now();
                if (!$user->session_id) {
                    $user->session_id = $currentSessionId;
                }
                $user->save();
                $user->timestamps = true;
            }
        }

        return $next($request);
    }
}
