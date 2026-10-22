<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminIdleTimeout
{
    private const MAX_IDLE_SECONDS = 1800;

    public function handle(Request $request, Closure $next)
    {
        $last = $request->session()->get('last_admin_activity');

        if ($last && (time() - $last) > self::MAX_IDLE_SECONDS) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/admin/login')
                ->withErrors(['username' => 'Your session expired from inactivity. Please sign in again.']);
        }

        $request->session()->put('last_admin_activity', time());

        return $next($request);
    }
}
