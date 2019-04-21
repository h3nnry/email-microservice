<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('email')->group(function () {
    Route::post('/',  'EmailController@post');
    Route::get('/{id}',  'EmailController@get')->where('id', '[0-9]+');
    Route::delete('/{id}',  'EmailController@delete')->where('id', '[0-9]+');
});
