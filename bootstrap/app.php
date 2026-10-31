<?php

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        apiPrefix: '',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Web middleware group is intentionally omitted to avoid sessions, cookies, or CSRF.
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(fn (Request $request, Throwable $e) => true);

        $exceptions->render(function (Throwable $e, Request $request) {
            if ($e instanceof QueryException || $e instanceof PDOException) {
                $message = $e->getMessage();
                if (str_contains($message, 'SQLSTATE[08') || str_contains($message, 'SQLSTATE[57P03')) {
                    return response()->json([
                        'error' => 'database_unavailable',
                    ], 503);
                }
            }

            if ($e instanceof \Predis\Connection\ConnectionException || $e instanceof \Predis\Response\ServerException) {
                return response()->json([
                    'error' => 'redis_unavailable',
                ], 503);
            }

            if ($e instanceof ValidationException) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], 422);
            }

            if ($e instanceof NotFoundHttpException) {
                return response()->json([
                    'error' => 'not_found',
                ], 404);
            }

            if ($e instanceof MethodNotAllowedHttpException) {
                return response()->json([
                    'error' => 'method_not_allowed',
                ], 405);
            }

            if ($e instanceof HttpExceptionInterface) {
                return response()->json([
                    'error' => $e->getMessage() ?: 'http_error',
                ], $e->getStatusCode());
            }

            return response()->json([
                'error' => 'internal_server_error',
            ], 500);
        });
    })->create();
