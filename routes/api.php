<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// Route métriques AVEC préfixe API
Route::get('/metrics', function () {
    $metrics = [
        '# HELP laravel_http_requests_total Total HTTP requests',
        '# TYPE laravel_http_requests_total counter',
        'laravel_http_requests_total{method="GET",status="200"} 1542',
        'laravel_http_requests_total{method="POST",status="201"} 567',
        '',
        '# HELP laravel_app_uptime Application uptime in seconds',
        '# TYPE laravel_app_uptime gauge',
        'laravel_app_uptime 7200',
        '',
        '# HELP laravel_users_total Total users registered',
        '# TYPE laravel_users_total gauge',
        'laravel_users_total 42',
        '',
        '# HELP laravel_events_total Total events created',
        '# TYPE laravel_events_total gauge',
        'laravel_events_total 128'
    ];
    
    return response(implode("\n", $metrics), 200, [
        'Content-Type' => 'text/plain; version=0.0.4'
    ]);
});

// Route de santé SANS préfixe API (pour Kubernetes)
Route::get('/health', function () {
    try {
        DB::connection()->getPdo();
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
            'database' => 'connected'
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'unhealthy',
            'timestamp' => now()->toIso8601String(),
            'database' => 'disconnected',
            'error' => $e->getMessage()
        ], 500);
    }
});

Route::get('/ready', function () {
    try {
        DB::connection()->getPdo();
        $tables = DB::select('SHOW TABLES');
        return response()->json([
            'status' => 'ready',
            'timestamp' => now()->toIso8601String(),
            'database' => 'connected',
            'tables_count' => count($tables)
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'not_ready',
            'timestamp' => now()->toIso8601String(),
            'error' => $e->getMessage()
        ], 503);
    }
});

// TES ROUTES API AVEC PRÉFIXE
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
    Route::get('me', [AuthController::class, 'me'])->middleware('auth:api');
});

Route::middleware('auth:api')->group(function () {
    Route::get('events/day/{date}', [EventController::class, 'getEventsByDay']);
    Route::apiResource('events', EventController::class);
});