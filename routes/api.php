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
        $api->get('rooms/{id}','RoomsController@show');
        $api->post('rooms','RoomsController@store');
        $api->delete('rooms/delete/{number}','RoomsController@delete');
        $api->put('rooms/{number}', 'RoomsController@update');
        //client
        $api->get('clients', 'ClientsController@list');
        $api->get('clients/{id}', 'ClientsController@show'); //TODO
        $api->get('clients/status/{status}', 'ClientsController@clientsByStatus'); //TODO
        $api->post('clients/import/list', 'ClientsController@importList'); //TODO
        $api->post('clients/import', 'ClientsController@import'); //TODO
        //Cleaning
        $api->get('cleaning', 'CleaningController@list'); //TODO
        $api->get('cleaning/{number}', 'CleaningController@show'); //TODO
        $api->post('cleaning', 'CleaningController@store'); //TODO
        $api->put('cleaning/{number}', 'CleaningController@update'); //TODO
        $api->put('cleaning/start/{number}', 'CleaningController@start'); //TODO
        $api->put('cleaning/completed/{number}', 'CleaningController@completed'); //TODO
        $api->delete('cleaning/delete/{number}', 'CleaningController@delete'); //TODO
        //Reservation
        $api->get('reservation', 'ReservationController@list'); //TODO
        $api->get('reservation/{id}', 'ReservationController@show'); //TODO
        $api->get('reservation/client/{client_id}', 'ReservationController@reservationByClient'); //TODO
        $api->post('reservation', 'ReservationController@store'); //TODO
        $api->put('reservation/{number}', 'ReservationController@update'); //TODO
        $api->delete('reservation/delete/{number}', 'ReservationController@delete'); //TODO
        //Payment
        $api->get('payment/client/{client_id}', 'PaymentController@accountByClient'); //TODO
    });
});
