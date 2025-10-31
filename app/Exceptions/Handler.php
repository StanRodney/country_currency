<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function report(Throwable $e): void
    {
        parent::report($e);
    }

    public function render($request, Throwable $e): Response|JsonResponse
    {
        // Ensure all API routes return JSON errors
        if ($request->is('api/*')) {
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

            return response()->json([
                'success' => false,
                'error' => class_basename($e),
                'message' => $e->getMessage() ?: 'An unexpected error occurred',
            ], $status);
        }

        // Default Laravel behavior for non-API routes
        return parent::render($request, $e);
    }
}
