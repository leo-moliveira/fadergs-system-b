<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Classes\Helpers;
use App\Models\Cleaning;
use App\Models\Rooms;
use App\Transformers\CleaningTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CleaningController extends BaseController
{
    protected $cleaning;

    public function __construct(Cleaning $cleaning)
    {
        $this->cleaning = $cleaning;
    }

    /**
     * @OA\Get (
     *     path="/api/cleaning",
     *     tags={"Cleaning"},
     *     summary = "Get list of all rooms on cleaning process, desconsider status",
     *     @OA\Response(
     *         response="200",
     *         description="Return List of all rooms"
     *     ),
     *     security={{"JWT":{}}}
     * )
     * @return \Dingo\Api\Http\Response
     */
    public function list(Request $request)
    {
        //Check permissions
        if (!Helpers::validateUserRole($request->user(), ['admin', 'manager','employee'])){
            return $this->response->errorUnauthorized(trans('unauthorized'));
        }

        $cleaning = $this->cleaning->paginate(25);
        return $this->response->paginator($cleaning, new CleaningTransformer());
    }

    /**
     * @OA\Get (
     *     path="/api/cleaning/status/{status}",
     *     tags={"Cleaning"},
     *     summary = "Get list of all rooms on cleaning process by status",
     *     @OA\Parameter(
     *         name="status",
     *         in="path",
     *         description="Status to search",
     *         required=true,
     *         @OA\Schema(
     *                      type="string",
     *                      enum={"to-clean", "cleaning", "clean"},
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
    public function listByStatus(Request $request, $status)
    {
        //Check permissions
        if (!Helpers::validateUserRole($request->user(), ['admin', 'manager','employee'])){
            return $this->response->errorUnauthorized(trans('unauthorized'));
        }

        $cleaning = $this->cleaning->where('status', $status)->orderBy('created_at', 'ASC')->paginate(25);

        if(!$cleaning){
            return $this->response->errorNotFound();
        }

        return $this->response->paginator($cleaning, new CleaningTransformer());
    }

    /**
     * @OA\Get (
     *     path="/api/cleaning/{number}",
     *     tags={"Cleaning"},
     *     summary = "Get information of a room on cleaning process",
     *     @OA\Parameter(
     *         name="number",
     *         in="path",
     *         description="Number of room to show",
     *         required=true,
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Return List of all rooms"
     *     ),
     *     security={{"JWT":{}}}
     * )
     * @return \Dingo\Api\Http\Response
     */
    public function show(Request $request, $number)
    {
        //Check permissions
        if (!Helpers::validateUserRole($request->user(), ['admin', 'manager','employee'])){
            return $this->response->errorUnauthorized(trans('unauthorized'));
        }
        //$rooms = $this->rooms->where('status', '=', 1)->paginate(25)
        //->whereNotIn('status', ['clean']);
        $cleaning = $this->cleaning->where('rooms_id', $number)->orderBy('created_at', 'ASC')->paginate(25);

        if(!$cleaning){
            return $this->response->errorNotFound();
        }

        return $this->response->paginator($cleaning, new CleaningTransformer());
    }

    /**
     * @OA\Post (
     *     path="/api/cleaning",
     *     tags={"Cleaning"},
     *     summary = "Save room to clean on database",
     *     security={{"JWT":{}}},
     *     @OA\RequestBody(
     *          required=true,
     *          description="Room to insert in database",
     *          @OA\JsonContent(
     *              required={"room_number","manager_id","employee_id"},
     *              @OA\Property(property="room_number", type="integer", example="101"),
     *              @OA\Property(property="manager_id", type="integer", example="2"),
     *              @OA\Property(property="employee_id", type="integer", example="3"),
     *          ),
     *     ),
     *     @OA\Response(
     *          response=201,
     *          description="Created",
     *      ),
     *     @OA\Response(
     *          response=400,
     *          description="Not Allowed, cleaning already exists",
     *      ),
     *     @OA\Response(
     *          response=404,
     *          description="Not found, room maybe it doesn't exist",
     *      ),
     * )
     * @param Request $request
     */
    public function store(Request $request)
    {
        //Check permissions
        if (!Helpers::validateUserRole($request->user(), ['admin', 'manager'])){
            return $this->response->errorUnauthorized(trans('unauthorized'));
        }

        //Validate request
        $validator = \Validator::make($request->input(), [
            'room_number' => 'required|integer',
            'manager_id' => 'required|integer',
            'employee_id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return $this->response->errorBadRequest($validator->messages());
        }

        //validate room
        if(!Rooms::find($request->get('room_number'))){
            return $this->response->errorNotFound(trans('cleaning.notFound'));
        }

        //validate cleaning
        if(Cleaning::where('rooms_id', $request->get('room_number'))
            ->where('status','to-clean')){
            //->whereDate('created_at', '=>', Carbon::now()->toDateTimeString())){
            return $this->response->errorBadRequest(trans('cleaning.alreadyExists'));
        }

        //Insert room
        $atributes = [
            'rooms_id'          => $request->get('room_number'),
            'manager_id'        => $request->get('manager_id'),
            'employee_id'       => $request->get('employee_id'),
            'cleaning_date'     => null,
            'status'            => 'to-clean',
            'created_at'        => Carbon::now()->toDateTimeString()
        ];

        if(!$this->cleaning->create($atributes)){
            return $this->response->error();
        }

        return $this->response->created(trans('cleaning.sucess'));
    }

    /**
     * @OA\Put (
     *     path="/api/cleaning/{id}",
     *     tags={"Cleaning"},
     *     summary = "Update room to clean on data base",
     *     @OA\RequestBody(
     *          required=false,
     *          description="Data to the room",
     *          @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(property="room_number", type="integer", example="101"),
     *               @OA\Property(property="manager_id", type="integer", example="2"),
     *               @OA\Property(property="employee_id", type="integer", example="3"),
     *               @OA\Property(property="status", type="string", example="clean"),
     *               @OA\Property(property="cleaning_date", type="date", example="2021-11-02 18:14:18"))
     *           )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id of the cleaning register on database",
     *         required=true,
     *         @OA\Schema(type="integer")
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
    public function update(Request $request, $id)
    {
        //Check permissions
        if (!Helpers::validateUserRole($request->user(), ['admin', 'manager'])){
            return $this->response->errorUnauthorized(trans('rooms.unauthorized'));
        }

        //Validate request
        $validator = \Validator::make(["$id" => $id], [
            '$id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }

        $cleaning = Cleaning::findOrFail($id);

        if (! $cleaning) {
            return $this->response->errorNotFound();
        }

        $validator = \Validator::make($request->input(), [
            'room_number'   => 'integer',
            'manager_id'    => 'integer',
            'employee_id'   => 'integer',
            'cleaning_date' => 'date',
            'status'        => 'string'
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }

        $cleaning->rooms_id = ($request->get('room_number') != null) ? $request->get('room_number') : $cleaning->rooms_id;
        $cleaning->manager_id = ($request->get('manager_id') != null) ? $request->get('manager_id') : $cleaning->status;
        $cleaning->employee_id = ($request->get('employee_id') != null) ? $request->get('employee_id') : $cleaning->price;
        $cleaning->cleaning_date = ($request->get('cleaning_date') != null) ? $request->get('cleaning_date') : $cleaning->description;
        $cleaning->status = ($request->get('status') != null) ? $request->get('status') : $cleaning->description;

        $cleaning->update();

        return $this->response->noContent()->setStatusCode(200);
    }

    /**
     * @OA\Delete (
     *     path="/api/cleaning/delete/{id}",
     *     tags={"Cleaning"},
     *     summary = "Delete a room on cleaning process",
     *     security={{"JWT":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id of process clean on database",
     *         required=true,
     *         @OA\Schema(type="integer")
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
    public function delete(Request $request, $id)
    {
        //Check permissions
        if (!Helpers::validateUserRole($request->user(), ['admin', 'manager'])){
            return $this->response->errorUnauthorized(trans('rooms.unauthorized'));
        }

        //Validate request
        $validator = \Validator::make(["number" => $id], [
            'number' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }

        $cleaning = Cleaning::findOrFail($id);

        if (! $cleaning) {
            return $this->response->errorNotFound();
        }

        if( !$cleaning->delete() ) {
            return $this->response->errorInternal();
        }

        return $this->response->noContent()->setStatusCode(200);

    }

    /**
     * @OA\Put (
     *     path="/api/cleaning/start/{id}",
     *     tags={"Cleaning"},
     *     summary = "Update room status to cleaning",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id of cleaning process on data base",
     *         required=true,
     *         @OA\Schema(type="integer")
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
    public function start(Request $request, $id)
    {
        //Check permissions
        if (!Helpers::validateUserRole($request->user(), ['admin', 'manager','employee'])){
            return $this->response->errorUnauthorized(trans('unauthorized'));
        }

        //Validate request
        $validator = \Validator::make(["number" => $id], [
            'number' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }

        $cleaning = Cleaning::findOrFail($id);

        if (!$cleaning) {
            return $this->response->errorNotFound();
        }

        if($cleaning->status != "to-clean"){
            return $this->response->errorBadRequest(trans('cleaning.notToClean'));
        }

        $cleaning->status = "cleaning";
        $cleaning->update();

        return $this->response->noContent()->setStatusCode(200);
    }

    /**
     * @OA\Put (
     *     path="/api/cleaning/completed/{id}",
     *     tags={"Cleaning"},
     *     summary = "Update room status to completed",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id of cleaning process on data base",
     *         required=true,
     *         @OA\Schema(type="integer")
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
    public function completed(Request $request, $id)
    {
        //Check permissions
        if (!Helpers::validateUserRole($request->user(), ['admin', 'manager','employee'])){
            return $this->response->errorUnauthorized(trans('unauthorized'));
        }

        //Validate request
        $validator = \Validator::make(["number" => $id], [
            'number' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }

        $cleaning = Cleaning::findOrFail($id);

        if (!$cleaning) {
            return $this->response->errorNotFound();
        }

        $cleaning->status = "clean";
        $cleaning->cleaning_date = Carbon::now()->toDateTimeString();
        $cleaning->update();

        return $this->response->noContent()->setStatusCode(200);
    }
}
