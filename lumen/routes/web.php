<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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

use Firebase\JWT\JWT;
use Illuminate\Http\Request;

$router->group(['middleware' => 'jwt'], function () use ($router) {

    $router->group(['prefix' => 'category'], function () use ($router) {
        $router->get('all', [
            'uses' => 'ExampleController@getCategories'
        ]);
        $router->post('insert', [
            'uses' => 'ExampleController@insertCategory'
        ]);
        $router->delete('/', [
            'uses' => 'ExampleController@deleteCategory'
        ]);
        $router->patch('/', [
            'uses' => 'ExampleController@updateCategory'
        ]);
    });

});



$router->post('/login', function (Request $request) use ($router) {
    $token = JWT::encode([
        'id' => 1,
        'iat' => time(),
        'exp' => time() + 3600
    ], 'YRr9wFSzYzQGwkFsnzvqQhcmNUjDGBwZ');

    return compact('token');
});


$router->get('/', function () use ($router) {
    return $router->app->version();
});
