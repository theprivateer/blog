<?php

namespace Privateer\Basecms\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Privateer\Basecms\Mcp\Support\McpAccess;
use Privateer\Basecms\Models\McpToken;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateMcpRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $bearerToken = $request->bearerToken();
        $token = $bearerToken !== null ? McpToken::findByPlainText($bearerToken) : null;

        // A recognized key wins outright; an OAuth access token never hashes to one of ours,
        // so falling through to the Passport guard below only happens for unrecognized bearer values.
        if ($token !== null) {
            if (! $token->isValid()) {
                abort(401, 'Invalid or expired MCP access key.');
            }

            $token->markUsed();

            app()->instance(McpAccess::class, new McpAccess($token->abilities, $token->site));

            return $next($request);
        }

        if (config('basecms.mcp.oauth.enabled') && auth('api')->check()) {
            app()->instance(McpAccess::class, new McpAccess(
                (array) config('basecms.mcp.oauth.default_abilities', ['*']),
            ));

            return $next($request);
        }

        abort(401, 'Unauthenticated MCP request.');
    }
}
