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

/*
* If has no `Authorization` header in those two routes above,
* they should return the public defined albumn(s).
*/
$router->get('albumns', ['uses' => 'AlbumnsController@list']);
$router->get('albumns/{id}', ['uses' => 'AlbumnsController@get']);

$router->group(['middleware' => 'auth'], function () use ($router) {

  $router->post('albumns', ['uses' => 'AlbumnsController@add']);
  $router->patch('albumns/{id}', ['uses' => 'AlbumnsController@edit']);
  $router->delete('albumns/{id}', ['uses' => 'AlbumnsController@remove']);

});

