<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;
use Closure;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    public function handle($request, Closure $next, ...$guards)
    {
        if (!Auth::check()) {
            // Redirect to login page if not authenticated
            session()->flash('errors', ['Session expired please login again']);
            return redirect()->route('login'); // Adjust with your login route name
        }
        if (auth()->user()->status == 0) {
            auth()->logout();
            // Redirect to login page if not authenticated
            session()->flash('errors', ['Your account is deactivatd by admin. Please contact admin to activate your account']);
            return redirect()->route('login'); // Adjust with your login route name
        }
        // If authenticated, continue the request
        auth()->user()->update(['last_login' => now()]);
        return $next($request);
    }
}
