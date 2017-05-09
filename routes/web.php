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

$app->get('/', function () use ($app) {
    return $app->version();
});

$notImplemented = function() use ($app) {
    throw new \Symfony\Component\HttpKernel\Exception\HttpException(501, 'Not Implemented');
};

$v1 = function() use ($app, $notImplemented) {
    $app->get('product', 'ProductController@index');
    $app->get('product/{productPLU}', 'ProductController@show');

    $app->get('import', $notImplemented);
    $app->post('import', 'ImportController@create');
};

$app->group(['prefix' => 'latest'], $v1);
$app->group(['prefix' => 'v1'], $v1);