<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => '/email'], function () use ($router) {
    $router->post('/', 'EmailController@post');
    $router->get('{id:[0-9]+}', 'EmailController@get');
    $router->delete('{id:[0-9]+}', 'EmailController@delete');
});
