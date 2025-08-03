<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            
        });

        $this->renderable(function (ModelNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found.'
                ], 404);
            }
        });
    }

    // Added this to handle validation errors and http exceptions
    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson() || $request->is('api/*')) {

            if ($exception instanceof ValidationException) {
                return response()->json([
                    'message' => 'Validation Failed',
                    'errors' => $exception->errors(),
                ], 422);
            }

            if ($exception instanceof AuthenticationException) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                    'error' => class_basename($exception),
                ], 401);
            }

            $status = 500;
            if ($exception instanceof HttpExceptionInterface) {
                $status = $exception->getStatusCode();
            }

            return response()->json([
                'message' => $exception->getMessage(),
                'error' => class_basename($exception),
            ], $status);
        }

        return parent::render($request, $exception);
    }
}
