<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultUserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Set a default logged-in user for development mode
     * when auth is disabled.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only in development mode
        if (app()->environment('local', 'development')) {
            // Get or create default user
            $user = User::where('email', 'admin@nexus.local')->first();

            if (! $user) {
                $user = User::create([
                    'name' => 'Admin',
                    'email' => 'admin@nexus.local',
                    'password' => bcrypt('admin'),
                    'email_verified_at' => now(),
                ]);
            }

            // Set as authenticated user
            auth()->setUser($user);

            // Share user with views
            view()->share('user', $user);
            view()->share('auth', (object) ['user' => $user]);
        }

        return $next($request);
    }
}
