<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleCors
{
    /**
     * Allowed origins pattern or list.
     */
    protected array $allowedOrigins = [
        'http://localhost:3000',
        'http://localhost:5173',
        'http://localhost:5174',
        'http://localhost:5175',
        'http://localhost:5176',
        'http://127.0.0.1:3000',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:5174',
        'http://127.0.0.1:5175',
        'http://127.0.0.1:5176',
    ];

    
    protected array $allowedMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

    protected array $allowedHeaders = [
        'Content-Type',
        'Authorization',
        'X-CSRF-TOKEN',
        'X-XSRF-TOKEN',
        'X-Requested-With',
        'Accept',
        'Origin',
    ];

    protected array $exposedHeaders = ['Content-Length', 'X-JSON-Response'];

    protected int $maxAge = 86400;

    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->headers->get('Origin');
        $isAllowed = $origin && (
            in_array($origin, $this->allowedOrigins, true) ||
            preg_match('/^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?$/i', $origin)
        );

        // Handle preflight BEFORE hitting the router.
        if ($request->getMethod() === 'OPTIONS') {
            $response = response('', 204);

            if ($isAllowed) {
                $this->applyCorsHeaders($response, $origin);
            }

            return $response;
        }

        $response = $next($request);

        if ($isAllowed) {
            $this->applyCorsHeaders($response, $origin);
        }

        return $response;
    }

    protected function applyCorsHeaders(Response $response, string $origin): void
    {
        $response->headers->set('Access-Control-Allow-Origin', $origin);
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Allow-Methods', implode(', ', $this->allowedMethods));
        $response->headers->set('Access-Control-Allow-Headers', implode(', ', $this->allowedHeaders));
        $response->headers->set('Access-Control-Expose-Headers', implode(', ', $this->exposedHeaders));
        $response->headers->set('Access-Control-Max-Age', (string) $this->maxAge);
        $response->headers->set('Vary', 'Origin', false);
    }
}