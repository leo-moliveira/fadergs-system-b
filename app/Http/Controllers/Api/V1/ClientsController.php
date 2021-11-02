<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Classes\Helpers as MyHelpers;
use App\Models\Client;
use App\Transformers\ClientTransformer;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ClientsController extends BaseController
{
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }
    /**
     *
     * @OA\Get (
     *     path="/api/clients",
     *     tags={"Clients"},
     *     summary = "Get list of all clients",
     *     @OA\Response(
     *         response="200",
     *         description="Return List of all clients"
     *     ),
     *     security={{"JWT":{}}}
     * )
     * @return \Dingo\Api\Http\Response
     */
    public function list(Request $request){
        //Check permissions
        if (!MyHelpers::validateUserRole($request->user(), ['admin', 'manager'])){
            return $this->response->errorUnauthorized(trans('client.unauthorized'));
        }
        $clients = $this->client->paginate(25);
        return $this->response->paginator($clients, new ClientTransformer());
    }

    public function show(Request $request,$id){
        //Check permissions
        if (!MyHelpers::validateUserRole($request->user(), ['admin', 'manager'])){
            return $this->response->errorUnauthorized(trans('client.unauthorized'));
        }
    }

    public function clientsByStatus(Request $request){
        //Check permissions
        if (!MyHelpers::validateUserRole($request->user(), ['admin', 'manager'])){
            return $this->response->errorUnauthorized(trans('client.unauthorized'));
        }
    }

    public function importList(Request $request){
        //Check permissions
        if (!Helpers::validateUserRole($request->user(), ['admin', 'manager'])){
            return $this->response->errorUnauthorized(trans('client.unauthorized'));
        }
    }

    public function import(Request $request){
        //Check permissions
        if (!MyHelpers::validateUserRole($request->user(), ['admin', 'manager'])){
            return $this->response->errorUnauthorized(trans('client.unauthorized'));
        }
    }

    /*** private ***/

}
