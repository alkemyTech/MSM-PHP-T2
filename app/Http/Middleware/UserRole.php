<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserRole
{
    /**
     * Handle an incoming request.
     * ADMIN can access all resources
     * USER cannot acces to ADMIN resources
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        $userRole = Auth::user()->role->name;
        if ($userRole !== $role && $userRole !== 'ADMIN') {
            return response()->forbidden(['message' => 'You are not authorized to access this resource']);
        }
        return $next($request);
    }
}
