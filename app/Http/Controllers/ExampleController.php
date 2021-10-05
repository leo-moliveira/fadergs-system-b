<?php

namespace App\Http\Controllers;

class ExampleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    //
    function listOfRoomsToClean(){
        return response()->json([
            0 => [
                'number' => 101,
                'status' => 0
            ],
            1 => [
                'number' => 103,
                'status' => 0
            ],
            2 => [
                'number' => 105,
                'status' => 0
            ]
        ]);
    }
}
