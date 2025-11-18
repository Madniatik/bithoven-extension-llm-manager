<?php

namespace Bithoven\LLMManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class LLMApiMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated (for API access)
        if (!$request->user()) {
            return response()->json([
                'error' => 'Unauthenticated',
                'message' => 'LLM API requires authentication',
            ], 401);
        }

        // Log API request for auditing
        if (config('llm-manager.logging.enabled', true)) {
            Log::channel(config('llm-manager.logging.channel', 'daily'))
                ->info('LLM API Request', [
                    'user_id' => $request->user()->id,
                    'endpoint' => $request->path(),
                    'method' => $request->method(),
                    'extension_slug' => $request->header('X-Extension-Slug') ?? $request->input('extension_slug'),
                ]);
        }

        return $next($request);
    }
}
