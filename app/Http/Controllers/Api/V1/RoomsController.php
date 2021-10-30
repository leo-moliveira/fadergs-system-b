<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Rooms;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Transformers\RoomTransformer;

/**
 *
 */
class RoomsController extends BaseController
{
    protected $rooms;

    /**
     * @param Rooms $rooms
     */
    public function __construct(Rooms $rooms)
    {
        $this->rooms = $rooms;
    }

    /**
     * @OA\Get (
     *     path="/api/rooms",
     *     operationId="/api/rooms",
     *     tags={"Rooms"},
     *     description = "Get list of all rooms",
     *     @OA\Response(
     *         response="200",
     *         description="Return List of all rooms",
     *         @OA\JsonContent()
     *     )
     * )
     * @return \Dingo\Api\Http\Response
     */
    public function index(){
        $rooms = $this->rooms->paginate(25);
        return $this->response->paginator($rooms, new RoomTransformer());
    }

    /**
     * @OA\Get (
     *     path="/api/api/rooms/occupied",
     *     operationId="/api/rooms/occupied",
     *     tags={"Rooms"},
     *     description = "Get list of rooms that are not available.",
     *     @OA\Response(
     *         response="200",
     *         description="Return list of rooms.",
     *         @OA\JsonContent()
     *     )
     * )
     * @return \Dingo\Api\Http\Response
     */
    public function listOccupiedRooms(){
        $rooms = $this->rooms->where('status', '=', 1)->paginate(25);
        return $this->response->paginator($rooms, new RoomTransformer());
    }

    /**
     * @OA\Get (
     *     path="/api/api/rooms/available",
     *     operationId="/api/rooms/available",
     *     tags={"Rooms"},
     *     description = "Get list of rooms that are available.",
     *     @OA\Response(
     *         response="200",
     *         description="Return list of rooms.",
     *         @OA\JsonContent()
     *     )
     * )
     * @return \Dingo\Api\Http\Response
     */
    public function listAvailableRooms(){
        $rooms = $this->rooms->where('status', '=', 0)->paginate(25);
        return $this->response->paginator($rooms, new RoomTransformer());
    }

    /**
     * @OA\Get (
     *     path="/api/api/rooms/{id}",
     *     operationId="/api/rooms/{id}",
     *     tags={"Rooms"},
     *     description = "Get list of rooms that are available.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id of rooms to show",
     *         required=true,
     *         @OA\Schema(type="int")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Rooms information.",
     *         @OA\JsonContent()
     *     )
     * )
     * @param $id
     * @return \Dingo\Api\Http\Response|void
     */
    public function show($id){
        try {
            $rooms = $this->rooms->findOrFail($id);
            return $this->response->item($rooms, new RoomTransformer());
        }catch (\Exception $exception){
            return $this->response->errorNotFound();
        }
    }

    /**
     * @OA\Post (
     *     path="/api/api/rooms",
     *     operationId="/api/rooms",
     *     tags={"Rooms"},
     *     description = "Save list of rooms on database",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id of rooms to show",
     *         required=true,
     *         @OA\Schema(type="int")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Rooms information.",
     *         @OA\JsonContent()
     *     )
     * )
     * @param Request $request
     */
    public function store(Request $request){
        //Check permissions
        $arrayPermission = ['admin', 'manager'];
        if (!$this->validateRole($request->user(),$arrayPermission)){
           return $this->response->errorUnauthorized(trans('auth.unauthorized'));
        }
        //Validate request
        $validator = \Validator::make($request->input(), [
            'number' => 'required|integer',
            'status' => 'required|string',
            'price' => 'required|between:0,99.99',
            'description' => 'required|string'
        ]);
        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }

        $atributes = [
            'id'            => $request->get('number'),
            'status'        => $request->get('status'),
            'price'         => $request->get('price'),
            'description'   => $request->get('description'),
            'created_at'    => Carbon::now()->toDateTimeString()
        ];
        try{
            $this->rooms->create($atributes);
            return $this->response->created();
        }catch (\PDOException $e){
            return $this->response->error($e->getMessage());
        }

    }


    //private

    /**
     *
     */
    private function validateRole(User $user, $permitions) : bool
    {
        return (in_array($user->role, $permitions)) ? true : false;
    }
}
