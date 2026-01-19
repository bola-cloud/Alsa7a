<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

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
            //
        });

        $this->renderable(function (Throwable $e, $request) {
            // Only handle if debug is OFF
            if (!config('app.debug')) {
                // API Errors
                if ($request->is('api/*') || $request->wantsJson()) {
                    // Start by checking specific non-fatal exceptions (Validation, Auth)
                    if (
                        $e instanceof \Illuminate\Validation\ValidationException ||
                        $e instanceof \Illuminate\Auth\AuthenticationException
                    ) {
                        return null; // Let standard handling work
                    }

                    $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

                    // Mask 500 errors
                    $message = $status === 500 ? 'Server Error' : $e->getMessage();

                    return response()->json([
                        'status' => false,
                        'message' => $message
                    ], $status);
                }

                // Web Errors (Admin Panel)
                // Redirect critical errors (like DB foreign key constraint) to 404 page as requested
                if ($e instanceof \Illuminate\Database\QueryException || $e instanceof \Error) {
                    abort(404);
                }
            }
        });
    }
}
