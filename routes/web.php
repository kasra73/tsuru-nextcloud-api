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

$router->get('resources/plans', 'NextcloudController@plans');
$router->post('resources', 'NextcloudController@create');
$router->put('resources/{name}', 'NextcloudController@update');
$router->get('resources/{name}', 'NextcloudController@show');
$router->delete('resources/{name}', 'NextcloudController@delete');
$router->get('resources/{name}/status', 'NextcloudController@status');
$router->post('resources/{name}/bind', 'NextcloudController@bind');
$router->post('resources/{name}/bind-app', 'NextcloudController@bindApp');
$router->delete('resources/{name}/bind-app', 'NextcloudController@unbind');
$router->delete('resources/{name}/bind', 'NextcloudController@restrict');
