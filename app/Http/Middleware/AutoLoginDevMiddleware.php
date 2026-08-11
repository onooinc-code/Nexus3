<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AutoLoginDevMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$guards): Response
    {
        if (app()->environment('local')) {
            // Try to get admin user, or create it if doesn't exist
            $user = User::where('email', 'admin@nexus.local')->first() ?? User::first();

            if (! $user) {
                // Create default admin user
                $user = User::create([
                    'name' => 'Admin',
                    'email' => 'admin@nexus.local',
                    'password' => bcrypt('admin'),
                    'email_verified_at' => now(),
                ]);
            }

            if ($user) {
                if (! Auth::guard('web')->check()) {
                    Auth::guard('web')->login($user);
                }
                Auth::guard('sanctum')->setUser($user);
                $request->setUserResolver(function () use ($user) {
                    return $user;
                });
            }
        }

        return $next($request);
    }
}
