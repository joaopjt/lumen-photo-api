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

$router->post('login', [
    'uses' => 'LoginController@makeLogin'
]);

$router->get('photos', [
    'middleware' => 'auth', function() {

      return response()->json([
        '01' => 'blabla.jpg',
        '02' => 'blabla.jpg'
      ]);
    }
]);
