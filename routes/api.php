<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('setWebhook', [App\Http\Controllers\BotTelegramController::class, 'setWebhook']);
Route::post('timohtech/webhook', [App\Http\Controllers\BotController::class, 'handle']);
Route::post('test/webhook', [App\Http\Controllers\BotTestController::class, 'handle']);
