<?php

use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\CheckRole;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->api(prepend: [
            EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);

        $middleware->redirectGuestsTo(fn(Request $request) => route('login'));
        $middleware->redirectUsersTo(fn(Request $request) => route('dashboard'));
        $middleware->alias([
            'auth:sanctum' => EnsureFrontendRequestsAreStateful::class,
        ]);
        // $middleware->throttleApi();
    
        $middleware->group('admin', [
            'auth',
            'verified',
            'role:admin_company,super_admin',
        ]);

        $middleware->group('super_admin', [
            'auth',
            'verified',
            'role:super_admin',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non authentifié.',
                    'error' => 'Unauthenticated'
                ], 401);
            }

            return redirect()->guest(route('login'));
        });

        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $e->errors()
                ], 422);
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès refusé',
                    'error' => 'Forbidden'
                ], 403);
            }

            return Inertia::render('errors/403', [
                'status' => 403,
                'message' => $e->getMessage() ?: 'Accès non autorisé'
            ])->toResponse($request)->setStatusCode(403);
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ressource non trouvée',
                    'error' => 'Not Found'
                ], 404);
            }

            return Inertia::render('errors/404', [
                'status' => 404,
                'message' => 'Page non trouvée'
            ])->toResponse($request)->setStatusCode(404);
        });

        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*') && app()->environment('production')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur serveur',
                    'error' => 'Internal Server Error'
                ], 500);
            }

            if (!$request->is('api/*') && app()->environment('production')) {
                return Inertia::render('errors/500', [
                    'status' => 500,
                    'message' => 'Une erreur inattendue s\'est produite'
                ])->toResponse($request)->setStatusCode(500);
            }
        });

        $exceptions->report(function (\Throwable $e) {
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        });
    })
    ->create();
