<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Api Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an API application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
$api = app('Dingo\Api\Routing\Router');

$api->version('v1', ['namespace' => 'App\Http\Controllers\Api\V1'], function ($api) {
    // login
    $api->post('auth/login', 'AuthController@login');
    // refresh jwt token
    $api->post('auth/login/refresh', 'AuthController@refreshToken');
    // need authentication
    $api->group(['middleware' => 'api.auth'], function ($api) {
        //User
        $api->get('user','UsersController@index');

        //Rooms
        $api->get('rooms','RoomsController@index');
        $api->get('rooms/occupied','RoomsController@listOccupiedRooms');
        $api->get('rooms/available','RoomsController@listAvailableRooms');
        $api->get('rooms/status/{status}','RoomsController@listByStatus');
        $api->get('rooms/{id}','RoomsController@show');
        $api->post('rooms','RoomsController@store');
        $api->delete('rooms/delete/{number}','RoomsController@delete');
        $api->put('rooms/{number}', 'RoomsController@update');

        //client
        $api->get('clients/list', 'ClientsController@list');
        $api->get('clients/{id}', 'ClientsController@show');
        $api->get('clients/status/{status}', 'ClientsController@clientsByStatus');
        $api->post('clients', 'ClientsController@store');
        $api->post('clients/list', 'ClientsController@storeList');

        //Cleaning
        $api->get('cleaning', 'CleaningController@list');
        $api->get('cleaning/{number}', 'CleaningController@show');
        $api->get('cleaning/status/{status}', 'CleaningController@listByStatus');
        $api->post('cleaning', 'CleaningController@store');
        $api->put('cleaning/{id}', 'CleaningController@update');
        $api->put('cleaning/start/{id}', 'CleaningController@start');
        $api->put('cleaning/completed/{id}', 'CleaningController@completed');
        $api->delete('cleaning/delete/{id}', 'CleaningController@delete');

        //Reservation
        $api->get('reservation', 'ReservationController@list');
        $api->get('reservation/{id}', 'ReservationController@show');
        $api->get('reservation/client/{client_id}', 'ReservationController@reservationByClient');
        $api->post('reservation', 'ReservationController@store');
        $api->put('reservation/{number}', 'ReservationController@update');
        $api->delete('reservation/delete/{id}', 'ReservationController@delete');

        //Payment
        $api->get('payment/client/{client_id}', 'PaymentController@accountByClient'); //TODO
    });
});
