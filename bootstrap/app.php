<?php

use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Exceptions\MissingAbilityException;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        apiPrefix: '/v1',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // 權限錯誤(帶錯 token)，回傳錯誤訊息 (user 帶到 admin，admin 帶到 user)
        $exceptions->render(function (Throwable $m, Request $request) {
            if ($m->getMessage() == 'Invalid ability provided.') {
                return response()->json([
                    'result' => false,
                    'error_code' => 403001,
                    'error_msg' => 'Invalid token provided.'
                ], 403);
            }
        });
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            $needTokenPath = [
                '/v1/user/userinfo',
                '/v1/user/payment',
                '/v1/admin/userlist',
                '/v1/admin/new-machine',
            ];
            if ($request->header('Accept') != 'application/json' && in_array($request->getRequestUri(), $needTokenPath)) {
                return response()->json([
                    'error' => 'type error',
                    'message' => 'you need json'
                ], 401);
            }
            // 須帶 token 頁面，未帶 token 時回傳錯誤訊息
            if ($e->getMessage() == 'Unauthenticated.' && in_array($request->getRequestUri(), $needTokenPath)) {
                return response()->json([
                    'result' => false,
                    'error_code' => 401001,
                    'error_msg' => 'you need token'
                ], 401);
            }
        });
    })->create();
