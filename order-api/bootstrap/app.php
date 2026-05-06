<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Exceptions\Handler;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Dang ky middleware aliases (TV3 + TV5)
        // Sau khi dang ky, co the dung trong routes:
        //   Route::middleware('jwt.auth')  -> JwtAuthMiddleware
        //   Route::middleware('order.owner') -> OrderOwnerMiddleware
        $middleware->alias([
            'jwt.auth'    => \App\Http\Middleware\JwtAuthMiddleware::class,
            'order.owner' => \App\Http\Middleware\OrderOwnerMiddleware::class,
            'cors'        => \App\Http\Middleware\CorsMiddleware::class,
            'admin'       => \App\Http\Middleware\AdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Error Handler tap trung
        // Moi exception deu duoc chuyen qua Handler::render()
        // Neu la API request -> tra ve JSON format thong nhat
        // Neu la Web request -> de Laravel xu ly mac dinh (HTML)
        $exceptions->render(function (\Throwable $e, $request) {
            return Handler::render($e, $request);
        });
    })->create();
