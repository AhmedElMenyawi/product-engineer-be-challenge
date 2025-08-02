<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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
