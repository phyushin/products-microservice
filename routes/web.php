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

$v1 = function() use ($app) {
    $app->get('product', '');
};

$app->group(['prefix' => 'latest'], $v1);
$app->group(['prefix' => 'v1'], $v1);