<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AuthenticateApiToken;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'api.token' => AuthenticateApiToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $exception, $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $status = match (true) {
                $exception instanceof ValidationException => 422,
                $exception instanceof AuthenticationException => 401,
                $exception instanceof AuthorizationException => 403,
                $exception instanceof ModelNotFoundException => 404,
                $exception instanceof HttpExceptionInterface => $exception->getStatusCode(),
                default => 500,
            };

            $message = match (true) {
                $exception instanceof ValidationException => 'The given data was invalid.',
                $exception instanceof AuthenticationException => 'Unauthenticated.',
                $exception instanceof AuthorizationException => 'This action is unauthorized.',
                $exception instanceof ModelNotFoundException => 'Not found.',
                $exception instanceof HttpExceptionInterface && $exception->getMessage() !== '' => $exception->getMessage(),
                $status === 403 => 'Forbidden.',
                $status === 404 => 'Not found.',
                default => 'Server Error.',
            };

            return response()->json([
                'code' => $status,
                'message' => $message,
                'data' => null,
            ], $status);
        });
    })
    ->create();
