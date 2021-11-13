<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Classes\Helpers;
use App\Models\Client;
use App\Models\User;
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
     *     path="/api/clients/list",
     *     tags={"Clients"},
     *     summary = "Get list of all clients",
     *     @OA\Response(
     *         response="200",
     *         description="Return List of all clients"
     *     ),
     *     security={{"JWT":{}}}
     * )
     */
    public function list(Request $request){
        //Check permissions
        if (!Helpers::validateUserRole($request->user(), ['admin', 'manager'])){
            return $this->response->errorUnauthorized(trans('client.unauthorized'));
        }

        $clients = $this->client->with(['user'])->paginate(25);
        return $this->response->paginator($clients, new ClientTransformer());
    }

    public function show(Request $request, $id){
        //Check permissions
        if (!Helpers::validateUserRole($request->user(), ['admin', 'manager'])){
            return $this->response->errorUnauthorized(trans('client.unauthorized'));
        }

    }

    public function clientsByStatus(Request $request){
        //Check permissions
        if (!Helpers::validateUserRole($request->user(), ['admin', 'manager'])){
            return $this->response->errorUnauthorized(trans('client.unauthorized'));
        }
    }

    public function importList(Request $request){
        //Check permissions
        if (!Helpers::validateUserRole($request->user(), ['admin', 'manager'])){
            return $this->response->errorUnauthorized(trans('client.unauthorized'));
        }
    }

    /**
     * @OA\Post (
     *     path="/api/clients",
     *     tags={"Clients"},
     *     summary = "Save single client on database",
     *     security={{"JWT":{}}},
     *     @OA\RequestBody(
     *          required=true,
     *          description="Client to insert on database",
     *          @OA\JsonContent(
     *              required={"full_name","email","cpf","gender","status","registration_date"},
     *              @OA\Property(property="full_name", type="string", example="Erico Verissimo"),
     *              @OA\Property(property="email", type="e-mail", example="a@a.com"),
     *              @OA\Property(property="cpf", type="number", example="17966177092"),
     *              @OA\Property(property="rg", type="number", example="462748066"),
     *              @OA\Property(property="gender", type="string", example="M"),
     *              @OA\Property(property="status", type="boolean", example="1"),
     *              @OA\Property(property="registration_date", type="date-time", example="2021-10-31 17:12:52"),
     *              @OA\Property(property="address", type="string", example=""),
     *              @OA\Property(property="number", type="string", example=""),
     *              @OA\Property(property="complement", type="string", example=""),
     *              @OA\Property(property="city", type="string", example=""),
     *              @OA\Property(property="state", type="string", example=""),
     *              @OA\Property(property="country", type="string", example=""),
     *              @OA\Property(property="zip_code", type="string", example=""),
     *          ),
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="OK",
     *      ),
     * )
     * @param Request $request
     */
    public function store(Request $request){
        dd($request->toArray());die();
        //Check permissions
        if (!Helpers::validateUserRole($request->user(), ['admin', 'manager'])){
            return $this->response->errorUnauthorized(trans('client.unauthorized'));
        }

        //Validate request
        $validator = \Validator::make($request->input(), [
            //client
            'full_name' => 'required|string',
            'cpf' => 'required|string',
            'rg' => 'string',
            'gender' => 'string',
            'status' => 'required|boolean',
            'registration_date' => 'required|date',

            //address
            'address' => 'required|string',
            'number' => 'required|numeric',
            'complement' => 'string',
            'city' => 'required|string',
            'state' => 'required|string',
            'country' => 'required|string',
            'zip_code' => 'required|numeric',

            //phone
            'numbers.*' => 'string|distinct',
        ]);
        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }

        //validate client
        $client = Client::where('cpf', $request->get('cpf'));

        if($client->count() !== 0){
            return $this->response->errorBadRequest(trans('client.alreadyRegistered'));
        }





        //create address
        $addressAtributes = [];

        //create phone
        $phoneAtributes = [];


        //TODO: Cria cliente
        //TODO: Cria endereço
        //TODO: Cria telefones
        //TODO: SALVA TUDO e respode ok
    }
    /*** private ***/

}
