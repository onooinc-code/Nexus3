<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AutoLoginDevMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$guards): Response
    {
        if (app()->environment('local')) {
            // Try to get admin user, or create it if doesn't exist
            $user = \App\Models\User::where('email', 'admin@nexus.local')->first();
            
            if (!$user) {
                $user = \App\Models\User::first();
            }

            if (!$user) {
                // Create default admin user
                $user = \App\Models\User::create([
                    'name' => 'Admin',
                    'email' => 'admin@nexus.local',
                    'password' => bcrypt('admin'),
                    'email_verified_at' => now(),
                ]);
            }

            if ($user) {
                // Use stateless Auth to prevent session locks
                \Illuminate\Support\Facades\Auth::guard('sanctum')->setUser($user);
                \Illuminate\Support\Facades\Auth::shouldUse('sanctum');
                $request->setUserResolver(function () use ($user) {
                    return $user;
                });
            }
        }
        return $next($request);
    }
}
