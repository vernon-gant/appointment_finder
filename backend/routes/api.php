<?php

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

use Laravel\Lumen\Routing\Router;

/** @var Router $router */
$router->get('/', function () use ($router) {
    return $router->app->version();
});

// Appointment routes
$router->group(['prefix' => 'appointments'], function () use ($router) {
	$router->get('/', 'AppointmentController@index');
	// Group routes with same id
	$router->group(['prefix' => '{appointmentId}'], function () use ($router) {
		$router->get('/', 'AppointmentController@get');
		$router->get('/appointment-dates', 'AppointmentDateController@index');
		$router->get('/comments', 'CommentController@index');
		$router->get('/votes', 'VoteController@index');
	});
	$router->post('/', 'AppointmentController@create');
	$router->delete('/{id}', 'AppointmentController@delete');
});

// AppointmentDate routes
$router->get('appointment-dates/{id}', 'AppointmentDateController@get');

// Vote routes
$router->group(['prefix' => 'votes'], function () use ($router) {
	$router->get('/{id}', 'VoteController@get');
	$router->post('/', 'VoteController@create');
});

// Comment routes
$router->group(['prefix' => 'comments'], function () use ($router) {
	$router->delete('/{id}', 'CommentController@delete');
});




