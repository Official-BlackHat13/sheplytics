<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->middleware('auth:api')->group(function () {
    Route::apiResource('stats', 'API\StatController', ['parameters' => [
        'stats' => 'id'
    ]]);

    Route::apiResource('websites', 'API\WebsiteController', ['parameters' => [
        'websites' => 'id'
    ]]);

    Route::apiResource('account', 'API\AccountController', ['only' => [
        'index'
    ]]);

    Route::fallback(function () {
        return response()->json(['message' => 'Resource not found.', 'status' => 404], 404);
    });
});

Route::post('event', 'API\EventController@index')->name('event');