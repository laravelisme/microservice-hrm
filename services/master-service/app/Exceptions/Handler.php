<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        // ...existing code...
    ];

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'status' => 'error',
                'message' => 'Resource tidak ditemukan',
            ], 404);
        }

        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'status' => 'error',
                'message' => 'Endpoint tidak ditemukan',
            ], 404);
        }

        if ($e instanceof HttpExceptionInterface && $e->getStatusCode() === 404) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not Found',
            ], 404);
        }

        return parent::render($request, $e);
    }

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (ModelNotFoundException $e, $request) {
            return response()->json([
                'status' => 'error',
                'message' => 'Resource tidak ditemukan',
            ], 404);
        });

        $this->renderable(function (NotFoundHttpException $e, $request) {
            return response()->json([
                'status' => 'error',
                'message' => 'Endpoint tidak ditemukan',
            ], 404);
        });

        $this->renderable(function (HttpExceptionInterface $e, $request) {
            if ($e->getStatusCode() === 404) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Not Found',
                ], 404);
            }
        });
    }
}
