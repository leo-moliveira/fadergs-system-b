<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Rooms;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Transformers\RoomTransformer;
use App\Http\Classes\Helpers;

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
     *     tags={"Rooms"},
     *     summary = "Get list of all rooms",
     *     @OA\Response(
     *         response="200",
     *         description="Return List of all rooms"
     *     ),
     *     security={{"JWT":{}}}
     * )
     * @return \Dingo\Api\Http\Response
     */
    public function index(){
        $rooms = $this->rooms->paginate(25);
        return $this->response->paginator($rooms, new RoomTransformer());
    }

    /**
     * @OA\Get (
     *     path="/api/rooms/occupied",
     *     tags={"Rooms"},
     *     summary = "Get list of rooms that are not available.",
     *     @OA\Response(
     *         response="200",
     *         description="Return list of rooms.",
     *     ),
     *     security={{"JWT":{}}}
     * )
     * @return \Dingo\Api\Http\Response
     */
    public function listOccupiedRooms(){
        $rooms = $this->rooms->where('status', '=', 1)->paginate(25);
        return $this->response->paginator($rooms, new RoomTransformer());
    }

    /**
     * @OA\Get (
     *     path="/api/rooms/available",
     *     tags={"Rooms"},
     *     summary = "Get list of rooms that are available.",
     *     @OA\Response(
     *         response="200",
     *         description="Return list of rooms.",
     *     ),
     *     security={{"JWT":{}}}
     * )
     * @return \Dingo\Api\Http\Response
     */
    public function listAvailableRooms(){
        $rooms = $this->rooms->where('status', '=', 0)->paginate(25);
        return $this->response->paginator($rooms, new RoomTransformer());
    }

    /**
     * @OA\Get (
     *     path="/api/rooms/{number}",
     *     tags={"Rooms"},
     *     summary = "Get data for room number.",
     *     @OA\Parameter(
     *         name="number",
     *         in="path",
     *         description="Number of rooms to show",
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
     *     path="/api/rooms",
     *     tags={"Rooms"},
     *     summary = "Save room on database",
     *     security={{"JWT":{}}},
     *     @OA\RequestBody(
     *          required=true,
     *          description="Room to insert on database",
     *          @OA\JsonContent(
     *              required={"number","status","price","description"},
     *              @OA\Property(property="number", type="string", example="101"),
     *              @OA\Property(property="status", type="integer", example="0"),
     *              @OA\Property(property="price", type="double", example="100.1"),
     *              @OA\Property(property="description", type="string", example="2 beds and 1 bathroom")
     *          ),
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="OK",
     *      ),
     * )
     * @param Request $request
     */
    public function store(Request $request)
    {
        //Check permissions
        if (!Helpers::validateUserRole($request->user(), ['admin', 'manager'])){
            return $this->response->errorUnauthorized(trans('rooms.unauthorized'));
        }

        //Validate request
        $validator = \Validator::make($request->input(), [
            'number' => 'required|integer',
            'status' => 'required|integer',
            'price' => 'required|between:0,99.99',
            'description' => 'required|string'
        ]);
        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }

        //Insert room
        $atributes = [
            'id'            => $request->get('number'),
            'status'        => $request->get('status'),
            'price'         => $request->get('price'),
            'description'   => $request->get('description'),
            'created_at'    => Carbon::now()->toDateTimeString()
        ];

         if(!$this->rooms->create($atributes)){
            return $this->response->error();
        }

         return $this->response->created(trans('rooms.sucess'));
    }

    /**
     * @OA\Delete (
     *     path="/api/rooms/delete/{number}",
     *     tags={"Rooms"},
     *     summary = "Delete a room",
     *     security={{"JWT":{}}},
     *     @OA\Parameter(
     *         name="number",
     *         in="path",
     *         description="Number of room to delete",
     *         required=true,
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Room deleted.",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Room not found",
     *         @OA\JsonContent()
     *     )
     * )
     * @param Request $request
     */
    public function delete($number, Request $request)
    {
        //Check permissions
        if (!$this->validateRole($request->user(), ['admin', 'manager'] )){
            return $this->response->errorUnauthorized(trans('rooms.unauthorized'));
        }

        //Validate request
        $validator = \Validator::make(["number" => $number], [
            'number' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }

        $room = Rooms::findOrFail($number);

        if (! $room) {
            return $this->response->errorNotFound();
        }

        if( !$room->delete() ) {
            return $this->response->errorInternal();
        }

        return $this->response->noContent()->setStatusCode(200);

    }

    /**
     * @OA\Put (
     *     path="/api/rooms/{number}",
     *     tags={"Rooms"},
     *     summary = "Update room information",
     *     @OA\RequestBody(
     *          required=false,
     *          description="Data to the room",
     *          @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(property="number", type="string", example="101"),
     *               @OA\Property(property="status", type="integer", example="1"),
     *               @OA\Property(property="price", type="double", example="120.1"),
     *               @OA\Property(property="description", type="string", example="2 beds and 1 bathroom"))
     *           )
     *     ),
     *     @OA\Parameter(
     *         name="number",
     *         in="path",
     *         description="Number of room to update",
     *         required=true,
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="OK",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Room not found",
     *         @OA\JsonContent()
     *     ),
     *     security={{"JWT":{}}}
     * )
     * @param Request $request
     */
    public function update($number, Request $request){
        //Check permissions
        if (!$this->validateRole($request->user(), ['admin', 'manager'] )){
            return $this->response->errorUnauthorized(trans('rooms.unauthorized'));
        }

        //Validate request
        $validator = \Validator::make(["number" => $number], [
            'number' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }

        $room = Rooms::findOrFail($number);

        if (! $room) {
            return $this->response->errorNotFound();
        }

        $validator = \Validator::make($request->input(), [
            'number' => 'nullable|integer',
            'status' => 'nullable|integer',
            'price' => 'nullable|between:0,99.99',
            'description' => 'nullable|string'
        ]);
        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }

        $room->id = ($request->get('number') != null) ? $request->get('number') : $room->id;
        $room->status = ($request->get('status') != null) ? $request->get('status') : $room->status;
        $room->price = ($request->get('price') != null) ? $request->get('price') : $room->price;
        $room->description = ($request->get('description') != null) ? $request->get('description') : $room->description;

        $room->update();

        return $this->response->noContent()->setStatusCode(200);
    }

    /*** private ***/

    /**
     *
     */
    private function validateRole(User $user, $permitions) : bool
    {
        return (in_array($user->role, $permitions)) ? true : false;
    }
}
