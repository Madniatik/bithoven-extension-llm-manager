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
        // Debug logging
        \Log::info('LLMAdminMiddleware check', [
            'has_user' => $request->user() !== null,
            'user_id' => $request->user()?->id,
            'user_name' => $request->user()?->name,
            'can_view_llm' => $request->user()?->can('extensions:llm:base:view'),
            'user_roles' => $request->user()?->roles->pluck('name')->toArray(),
            'user_permissions_count' => $request->user()?->getAllPermissions()->count(),
        ]);
        
        // Check if user has permission to access LLM admin
        if (! $request->user() || ! $request->user()->can('extensions:llm:base:view')) {
            \Log::warning('LLM Access Denied', [
                'user_id' => $request->user()?->id,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);
            abort(403, 'Unauthorized access to LLM Manager admin panel');
        }

        return $next($request);
    }
}
