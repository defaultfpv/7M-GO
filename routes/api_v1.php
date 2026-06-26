<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\User\UserController;
use App\Http\Controllers\Api\V1\Projects\ProjectController;
use App\Http\Controllers\Api\V1\Telegram\TelegramBotController;
use App\Http\Controllers\Api\V1\Max\MaxBotController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\Api\V1\WebSocket\WebSocketController;


// документация
Route::get('/dist', function () {
    return view('vendor.l5-swagger.index', [
        'documentation' => 'default',
        'documentationTitle' => 'GeoOperator API',
        'documentationConfig' => config('l5-swagger.documentations.default'),
        'urlsToDocs' => ['GeoOperator API' => '/api/v1/docs'],
        'useAbsolutePath' => config('l5-swagger.documentations.default.paths.use_absolute_path', true),
        'operationsSorter' => null,
        'configUrl' => null,
        'validatorUrl' => null,
    ]);
});
Route::get('/docs', function () {
    $path = storage_path('api-docs/api-docs.json');
    if (!file_exists($path)) abort(404, 'API documentation not generated. Run php artisan l5-swagger:generate');
    return response()->file($path, ['Content-Type' => 'application/json', 'Content-Disposition' => 'inline']);
});



// Вебсокеты
Route::prefix('websocket')->group(function () {
    Route::middleware('auth_token')->get('verify-token', [WebSocketController::class, 'verifyToken']); // авторизация пользователя на сокет сервере
    Route::middleware('auth_token')->post('test', [WebSocketController::class, 'test']);
});

// Авторизация
Route::prefix('auth')->group(function () {
    Route::middleware('api_token')->post('register', [AuthController::class, 'register']); // принимаю данные пользователя и его токен от партнерской программы
    Route::middleware('api_token')->post('login', [AuthController::class, 'login']); // принимаю данные пользователя и его токен от партнерской программы
    Route::middleware('auth_token')->post('logout', [AuthController::class, 'logout']);
    Route::middleware('auth_token')->get('sessions', [AuthController::class, 'getSessions']);
    Route::middleware('auth_token')->delete('sessions', [AuthController::class, 'deleteSessions']);
});


// Пользователи
// Route::prefix('users')->group(function () {
//     Route::middleware(\App\Http\Middleware\TelegramChatId::class)->get('me', [UserController::class, 'me']);
//     Route::middleware(\App\Http\Middleware\TelegramChatId::class)->get('', [UserController::class, 'all']);
//     Route::middleware(\App\Http\Middleware\TelegramChatId::class)->post('survey', [UserController::class, 'add_survey']);
//     Route::middleware(\App\Http\Middleware\TelegramChatId::class)->get('survey', [UserController::class, 'get_survey']);
// });


// Проекты
// Route::prefix('projects')->group(function () {
//     Route::get('', [ProjectController::class, 'projects_get']);
//     Route::get('{project_id}', [ProjectController::class, 'project_get']);
//     Route::middleware(\App\Http\Middleware\TelegramChatId::class)->post('add', [ProjectController::class, 'project_post']);
//     Route::middleware(\App\Http\Middleware\TelegramChatId::class)->get('{projects_id}/users', [ProjectController::class, 'project_users']);
//     Route::middleware(\App\Http\Middleware\TelegramChatId::class)->get('{chat_id}', [ProjectController::class, 'result']);
// });


// Телеграм
Route::prefix('telegram')->group(function () {
    Route::post('webhook', [TelegramBotController::class, 'handleUpdates']);
    // Route::post('keyboardUpdate', [TelegramBotController::class, 'rootKeyboardUpdate']);
});

// Макс
Route::prefix('max')->group(function () {
    Route::post('webhook', [MaxBotController::class, 'handleUpdates']);
});


// Тест
Route::any('', [TestController::class, 'handle']);