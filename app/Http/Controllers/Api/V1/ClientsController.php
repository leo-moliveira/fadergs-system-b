<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Classes\Helpers;
use App\Models\Client as ModelClient;
use App\Http\Classes\Client;
use App\Transformers\ClientTransformer;
use Illuminate\Http\Request;


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
        $clients = $this->client->client->with('user','address','phone')->paginate(25);

        return $this->response->paginator($clients, new ClientTransformer());
    }

    /**
     * @OA\Get (
     *     path="/api/clients/{id}",
     *     tags={"Clients"},
     *     summary = "Get client data by id.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User id from client",
     *         required=true,
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Rooms information.",
     *         @OA\JsonContent()
     *     ),
     *     security={{"JWT":{}}}
     * )
     * @param $number
     * @return \Dingo\Api\Http\Response|void
     */
    public function show(Request $request, $id){
        //Check permissions
        if (!Helpers::validateUserRole($request->user(), ['admin', 'manager'])){
            return $this->response->errorUnauthorized(trans('client.unauthorized'));
        }

        $client = $this->client->client->with('user','address','phone')->findOrFail($id);

        if($client){
            return $this->response->item($client, new ClientTransformer());
        }

        return $this->response->errorNotFound();
    }

    /**
     * @OA\Get (
     *     path="/api/clients/status/{status}",
     *     tags={"Clients"},
     *     summary = "Get list of all clients by status",
     *     @OA\Parameter(
     *         name="status",
     *         in="path",
     *         description="Status to search",
     *         required=true,
     *         @OA\Schema(
     *                      type="string",
     *                      enum={"blocked", "unlocked"},
     *                  )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Return List of all rooms"
     *     ),
     *     security={{"JWT":{}}}
     * )
     * @return \Dingo\Api\Http\Response
     */
    public function clientsByStatus(Request $request, $status){
        //Check permissions
        if (!Helpers::validateUserRole($request->user(), ['admin', 'manager'])){
            return $this->response->errorUnauthorized(trans('client.unauthorized'));
        }
        switch ($status){
            case "blocked":
                $status = false;
                break;
            case "unlocked":
                $status = true;
                break;
            default:
                $status = -1;
                break;
        }
        if($status === -1){
            return $this->response->errorBadRequest(trans('invalid'));
        }

        $clients = $this->client->client->with('user','address','phone')
            ->where('status', '=', $status)->paginate(25);

        return $this->response->paginator($clients, new ClientTransformer());
    }

    /**
     * @OA\Post (
     *     path="/api/clients/list",
     *     tags={"Clients"},
     *     summary = "Save list of client on database",
     *     security={{"JWT":{}}},
     *     @OA\RequestBody(
     *          required=true,
     *          description="List of clients to insert on database",
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="OK",
     *      ),
     * )
     * @param Request $request
     */
    public function storeList(Request $request){
        //Check permissions
        if (!Helpers::validateUserRole($request->user(), ['admin', 'manager'])){
            return $this->response->errorUnauthorized(trans('client.unauthorized'));
        }
        $validatorArray = [
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
            'phone_numbers.*' => 'alpha_num|distinct',
        ];
        $requestArray = $request->json()->all();

        foreach ($requestArray['list'] as $item){
            //Validate request
            $validator = \Validator::make($item, $validatorArray);
            if ($validator->fails()) {
                return $this->errorBadRequest($validator->messages());
            }

            //validate client
            $client = ModelClient::where('cpf', $item['cpf']);

            if($client->count() !== 0){
                return $this->response->errorBadRequest(trans('client.alreadyRegistered'));
            }

            $requestObject = (object)$item;

            if(!is_array($requestObject->phone_numbers) && is_string($requestObject->phone_numbers)){
                $requestObject->phone_numbers = explode(',',$requestObject->phone_numbers);
            }

            $newClient = new Client();
            $newClient->create($requestObject);

        }
        return $this->response->created(trans('client.sucess'));

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
     *              required={"full_name","cpf","gender","status","registration_date"},
     *              @OA\Property(property="full_name", type="string", example="Erico Verissimo"),
     *              @OA\Property(property="email", type="e-mail", example="a@a.com"),
     *              @OA\Property(property="cpf", type="number", example="17966177092"),
     *              @OA\Property(property="rg", type="number", example="462748066"),
     *              @OA\Property(property="gender", type="string", example="M"),
     *              @OA\Property(property="status", type="boolean", example="1"),
     *              @OA\Property(property="registration_date", type="date-time", example="2021-10-31 17:12:52"),
     *              @OA\Property(property="address", type="string", example="Rua a"),
     *              @OA\Property(property="number", type="string", example="1234"),
     *              @OA\Property(property="complement", type="string", example="bloco a"),
     *              @OA\Property(property="city", type="string", example="Porto Alegre"),
     *              @OA\Property(property="state", type="string", example="RS"),
     *              @OA\Property(property="country", type="string", example="Brasil"),
     *              @OA\Property(property="zip_code", type="string", example="80657222"),
     *              @OA\Property(property="phone_numbers", type="string|array", example="9632586,365236,36523,36523"),
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
            'phone_numbers.*' => 'alpha_num|distinct',
        ]);
        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }

        //validate client
        $client = ModelClient::where('cpf', $request->get('cpf'));

        if($client->count() !== 0){
            return $this->response->errorBadRequest(trans('client.alreadyRegistered'));
        }

        $requestObject = (object)$request->toArray();

        if(!is_array($requestObject->phone_numbers) && is_string($requestObject->phone_numbers)){
            $requestObject->phone_numbers = explode(',',$requestObject->phone_numbers);
        }

        $newClient = new Client();
        $newClient->create($requestObject);

        return $this->response->created(trans('client.sucess'));
    }
    /*** private ***/

}
