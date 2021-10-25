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

use Illuminate\Http\Request;

$router->group(['middleware' => 'jwt'], function () use ($router) {

    $router->group(['prefix' => 'account'], function () use ($router) {
        $router->get('get/all', [
            'uses' => 'AccountController@getAccounts'
        ]);
        $router->post('insert', [
            'uses' => 'AccountController@insertAccount'
        ]);
        $router->post('update/{id}', [
            'uses' => 'AccountController@updateAccount'
        ]);
        $router->delete('{id}', [
            'uses' => 'AccountController@deleteAccount'
        ]);
    });

    $router->group(['prefix' => 'category'], function () use ($router) {
        $router->get('get/all', [
            'uses' => 'CategoryController@getCategories'
        ]);
        $router->post('insert', [
            'uses' => 'CategoryController@insertCategory'
        ]);
        $router->delete('/{id}', [
            'uses' => 'CategoryController@deleteCategory'
        ]);
        $router->post('update/{id}', [
            'uses' => 'CategoryController@updateCategory'
        ]);
    });

    $router->post('user/{id}/change-name', [
        'uses' => 'UserController@changeName'
    ]);

    $router->get('colors', [
        'uses' => 'ColorController@getColors'
    ]);
});


## rotas sem autenticação
$router->post('/register', [
    'uses' => 'UserController@createUser',
]);

$router->post('/login', [
    'uses' => 'UserController@login',
]);
