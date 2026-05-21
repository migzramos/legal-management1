<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLawyer
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->isLawyer() && !auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Lawyers only.');
        }

        return $next($request);
    }
}