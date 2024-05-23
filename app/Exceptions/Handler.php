<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;

use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Custom response for QueryException (handles unique constraint violations)
        $this->renderable(function (QueryException $e, $request) {
            if ($e->errorInfo[1] == 1062) { // Duplicate entry error code
                $message = 'Duplicate entry error.';
                if (strpos($e->getMessage(), 'etudiants_email_unique') !== false || strpos($e->getMessage(), 'encadrants_email_unique') !== false) {
                    $message = 'Cet email est déjà utilisé.';
                } elseif (strpos($e->getMessage(), 'etudiants_cin_unique') !== false || strpos($e->getMessage(), 'encadrants_cin_unique') !== false) {
                    $message = 'Ce CIN est déjà utilisé.';
                } elseif (strpos($e->getMessage(), 'etudiants_cne_unique') !== false || strpos($e->getMessage(), 'encadrants_cne_unique') !== false) {
                    $message = 'Ce CNE est déjà utilisé.';
                }

                return response()->json([
                    'message' => $message,
                ], 409); // Conflict HTTP status code
            }
        });

        // Custom response for ValidationException
        $this->renderable(function (ValidationException $e, $request) {
            return response()->json([
                'message' => 'Erreur de validation.',
                'errors' => $e->errors()
            ], 422); // Unprocessable Entity HTTP status code
        });
    }
}
