<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NoCreateForInvite
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()->hasRole('invite') && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            abort(403, 'Les utilisateurs extranet ne peuvent pas créer ou modifier de contenu.');
        }

        return $next($request);
    }
}