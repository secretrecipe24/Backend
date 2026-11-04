<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiResponseMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->expectsJson()) {
            return $response;
        }

        if (! ($response instanceof JsonResponse)) {
            return $response;
        }

        $payload = $response->getData(true);

        // If already standardized, leave it.
        if (is_array($payload) && array_key_exists('success', $payload) && array_key_exists('data', $payload)) {
            return $response;
        }

        $status = $response->getStatusCode();
        $success = $status < 400;

        $message = null;
        $data = $payload;
        $errors = (object) [];

        // If controller returned {message: "..."} shape, lift message.
        if (is_array($payload) && array_key_exists('message', $payload) && is_string($payload['message'])) {
            $message = $payload['message'];
        }

        return response()->json([
            'success' => $success,
            'data' => $data,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}

