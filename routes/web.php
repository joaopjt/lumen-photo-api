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

$router->get('albumns', ['uses' => 'AlbumnsController@list']);
$router->get('albumns/{id}', ['uses' => 'AlbumnsController@get']);

$router->get('albumns/{albumnId}/photos', ['uses' => 'PhotosController@list']);
$router->get('albumns/{albumnId}/photos/{photoId}', ['uses' => 'PhotosController@get']);

$router->group(['middleware' => 'auth'], function () use ($router) {

  $router->post('albumns', ['uses' => 'AlbumnsController@add']);
  $router->put('albumns/{id}', ['uses' => 'AlbumnsController@edit']);
  $router->delete('albumns/{id}', ['uses' => 'AlbumnsController@remove']);

  $router->post('albumns/{albumnId}/photos', ['uses' => 'PhotosController@add']);
  $router->put('albumns/{albumnId}/photos/{photoId}', ['uses' => 'PhotosController@edit']);
  $router->delete('albumns/{albumnId}/photos/{photoId}', ['uses' => 'PhotosController@remove']);
});
