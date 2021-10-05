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

$router->group(['prefix' => 'api'],function ($router){
    $router->get('/',function (){
        return "Nothing to see here, move along!!";
    });

    /*
     * Auth
     */

    /*
     * Register
     */
    $router->post('register','AuthController@register');

    /*
     * maid work
     */
    $router->get('/getRoomsToClean','ExampleController@listOfRoomsToClean');
});
