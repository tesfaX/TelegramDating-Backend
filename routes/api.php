<?php

use App\Http\Controllers\AuthController;
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

Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/fetch-users', 'App\Http\Controllers\DatingController@fetchUsers');
    Route::post('/like', 'App\Http\Controllers\DatingController@like');
    Route::post('/dislike', 'App\Http\Controllers\DatingController@dislike');
    Route::get('/matches', 'App\Http\Controllers\DatingController@matches');
    Route::get('/profile/{id?}', 'App\Http\Controllers\ProfileController@getProfile');
    Route::patch('/profile', 'App\Http\Controllers\ProfileController@update');
    Route::post('/unmatch', 'App\Http\Controllers\DatingController@unmatch');
    Route::post('/generate-invoice', 'App\Http\Controllers\TelegramBotController@generateInvoice');


});



Route::post('/login', 'App\Http\Controllers\AuthController@login');
Route::post('/register', 'App\Http\Controllers\AuthController@register');
Route::get('/app-metas', 'App\Http\Controllers\AppMetaController@getAppMetas');
Route::get('/photos/{file_id}', 'App\Http\Controllers\PhotoController@getPhoto');
Route::post('/telegram-hook', 'App\Http\Controllers\TelegramBotController@handleWebhook');
