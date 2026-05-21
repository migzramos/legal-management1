<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                if ($user->isAdmin()) {
                    return redirect()->route('admin.reports.overview');
                }

                if ($user->isLawyer()) {
                    return redirect()->route('lawyer.cases.index');
                }

                // FIX BUG 6: 'client.cases.index' does not exist as a named route.
                // The client cases list is at 'client.cases.index' only through the
                // client prefix group — confirmed in routes/web.php.
                // Changed to 'client.dashboard' which always exists and is the
                // correct landing page for an already-authenticated client.
                return redirect()->route('client.dashboard');
            }
        }

        return $next($request);
    }
}