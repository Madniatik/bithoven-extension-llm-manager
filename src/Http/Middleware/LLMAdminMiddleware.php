<?php

namespace Bithoven\LLMManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LLMAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user has permission to access LLM admin
        if (! $request->user() || ! $request->user()->can('view-llm-configs')) {
            abort(403, 'Unauthorized access to LLM Manager admin panel');
        }

        return $next($request);
    }
}
