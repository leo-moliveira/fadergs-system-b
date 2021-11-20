<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Classes\Helpers;
use App\Models\Client;
use App\Models\Reservation;
use App\Models\Rooms;
use App\Transformers\ReservationTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReservationController extends BaseController
{
    protected $reservation;

    /**
     * @param Reservation $reservation
     */
    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    /**
     * @OsA\Get (
     *     path="/api/reservation",
     *     tags={"Reservation"},
     *     summary = "Get list of all Reservations",
     *     @OA\Response(
     *         response="200",
     *         description="Return List of all reservations"
     *     ),
     *     security={{"JWT":{}}}
     * )
     * @return \Dingo\Api\Http\Response
     */
    public function list(Request $request){
        //Check permissions
        if (!Helpers::validateUserRole($request->user(), ['admin', 'manager'])){
            return $this->response->errorUnauthorized(trans('rooms.unauthorized'));
        }

        $reservation = $this->reservation->paginate(25);
        return $this->response->paginator($reservation, new ReservationTransformer());
    }

    /**
     * @OA\Get (
     *     path="/api/reservation/{id}",
     *     tags={"Reservation"},
     *     summary = "Get data from reservation id.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of reservation to show",
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
            return $this->response->errorUnauthorized(trans('rooms.unauthorized'));
        }

        //Validate reservation
        $reservation = Reservation::findOrFail($id);

        if(!$reservation){
            return $this->response->errorNotFound('reservation.notFound');
        }

        return $this->response->item($reservation, new ReservationTransformer());
    }

    /**
     * @OA\Get (
     *     path="/api/reservation/client/{client_id}",
     *     tags={"Reservation"},
     *     summary = "Get data from reservation from expecific user.",
     *     @OA\Parameter(
     *         name="client_id",
     *         in="path",
     *         description="ID of user to show",
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
    public function reservationByClient(Request $request, $clientId){
        //Check permissions
        if (!Helpers::validateUserRole($request->user(), ['admin', 'manager'])){
            return $this->response->errorUnauthorized(trans('rooms.unauthorized'));
        }

        //Validate Client
        $client = Client::findOrFail($clientId);

        if(!$client){
            return $this->response->errorNotFound('reservation.clientNotFound');
        }

        $reservation = $this->reservation->where('client_id', '=', $clientId)->paginate(25);
        return $this->response->paginator($reservation, new ReservationTransformer());
    }

    /**
     * @OA\Post (
     *     path="/api/reservation",
     *     tags={"Reservation"},
     *     summary = "Make reservation",
     *     security={{"JWT":{}}},
     *     @OA\RequestBody(
     *          required=true,
     *          description="Make reservation",
     *          @OA\JsonContent(
     *              required={"client_id","room_number","date_start","date_end"},
     *              @OA\Property(property="client_id", type="number", example="8"),
     *              @OA\Property(property="room_number", type="number", example="101"),
     *              @OA\Property(property="date_start", type="date", example="21/11/2021 00:00:00"),
     *              @OA\Property(property="date_end", type="date", example="22/11/2021 17:00:00")
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
        if (Helpers::validateUserRole($request->user(), ['employee'])){
            return $this->response->errorUnauthorized(trans('rooms.unauthorized'));
        }

        //Validate request
        $validator = \Validator::make($request->input(), [
            'client_id'     => 'required|integer',
            'room_number'   => 'required|integer',
            'date_start'    => 'required|date_format:d/m/Y H:i:s',
            'date_end'      => 'required|date_format:d/m/Y H:i:s'
        ]);
        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }

        //Validate Client
        $client = Client::findOrFail($request->get('client_id'));

        if(!$client){
            return $this->response->errorNotFound('reservation.clientNotFound');
        }

        //Validate Room
        $room = Rooms::findOrFail($request->get('room_number'));
        if(!$room){
            return $this->response->errorNotFound('reservation.roomNotFound');
        }

        //Validate DateStart and DateEnd
        $dateStart = \DateTime::createFromFormat('d/m/Y H:i:s',$request->get('date_start'));
        $dateEnd = \DateTime::createFromFormat('d/m/Y H:i:s',$request->get('date_end'));
        $dateNow = new \DateTime('now',(new \DateTimeZone(env('APP_TIMEZONE'))));

        if($dateStart >= $dateEnd || $dateStart < $dateNow){
            return $this->response->error('reservation.dateError');
        }

        $dateDiffDays = $dateStart->diff($dateEnd)->days;

        //Calculate reservation price
        $reservationPrice = $room->price * $dateDiffDays;

        //Insert reservation
        $atributes = [
            'room_id'       => $request->get('room_number'),
            'client_id'     => $request->get('client_id'),
            'date_start'    => $request->get('date_start'),
            'date_end'      => $request->get('date_end'),
            'check_in'      => null,
            'check_out'     => null,
            'price'         => $reservationPrice,
            'status'        => "reserved",
            'created_at'    => Carbon::now()->toDateTimeString()
        ];

        if(!$this->reservation->create($atributes)){
            return $this->response->error('reservation.error');
        }

        return $this->response->created(trans('reservation.success'));
    }

    /**
     * @OA\Put (
     *     path="/api/reservation/{id}",
     *     tags={"Reservation"},
     *     summary = "Update reservation information",
     *     @OA\RequestBody(
     *          required=true,
     *          description="Data to reservation",
     *          @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(property="client_id", type="integer", example="8"),
     *               @OA\Property(property="room_number", type="integer", example="101"),
     *               @OA\Property(property="date_start", type="date-time", example="10/10/2021 00:00:00"),
     *               @OA\Property(property="date_end", type="date-time", example="31/10/2021 17:12:52"),
     *               @OA\Property(property="check_in", type="date-time", example="10/10/2021 04:12:52"),
     *               @OA\Property(property="check_out", type="date-time", example="31/10/2021 17:00:00"),
     *               @OA\Property(property="price", type="double", example="200.2"),
     *               @OA\Property(property="status", type="string", example="checkOut"))
     *           )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id from reservation",
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
    public function update($id, Request $request){
        //Check permissions
        if (!Helpers::validateUserRole($request->user(), ['admin', 'manager'])){
            return $this->response->errorUnauthorized(trans('reservation.unauthorized'));
        }

        //Validate Reservation
        $reservation = Reservation::findOrFail($id);
        if(!$reservation){
            return $this->response->errorNotFound('reservation.roomNotFound');
        }

        //Validate request
        $validator = \Validator::make($request->input(), [
            'client_id'     => 'required|integer',
            'room_number'   => 'required|integer',
            'date_start'    => 'required|date_format:d/m/Y H:i:s',
            'date_end'      => 'required|date_format:d/m/Y H:i:s',
            'check_in'      => 'nullable|date_format:d/m/Y H:i:s',
            'check_out'     => 'nullable|date_format:d/m/Y H:i:s',
            'price'         => 'nullable|between:0,99.99',
            'status'        => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }

        $reservation->client_id     = ($request->get('client_id')) ? $request->get('client_id') : $reservation->client_id;
        $reservation->room_id       = ($request->get('room_number')) ? $request->get('room_number') : $reservation->client_id;
        $reservation->date_start    = ($request->get('date_start')) ? $request->get('date_start') : $reservation->client_id;
        $reservation->date_end      = ($request->get('date_end')) ? $request->get('date_end') : $reservation->client_id;
        $reservation->check_in      = ($request->get('check_in')) ? $request->get('check_in') : $reservation->client_id;
        $reservation->check_out     = ($request->get('check_out')) ? $request->get('check_out') : $reservation->client_id;
        $reservation->price         = ($request->get('price')) ? $request->get('price') : $reservation->client_id;
        $reservation->status        = ($request->get('status')) ? $request->get('status') : $reservation->client_id;

        $reservation->update();
        return $this->response->noContent()->setStatusCode(200);
    }

    /**
     * @OA\Delete (
     *     path="/api/reservation/delete/{id}",
     *     tags={"Reservation"},
     *     summary = "Delete a Reservation",
     *     security={{"JWT":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id of reservation to delete",
     *         required=true,
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Reservation deleted.",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Reservation not found",
     *         @OA\JsonContent()
     *     )
     * )
     * @param Request $request
     */
    public function delete($id, Request $request){
        //Check permissions
        if (!Helpers::validateUserRole($request->user(), ['admin', 'manager'])){
            return $this->response->errorUnauthorized(trans('reservation.unauthorized'));
        }

        //Validate Reservation
        $reservation = Reservation::findOrFail($id);
        if(!$reservation){
            return $this->response->errorNotFound('reservation.roomNotFound');
        }

        //Validate Status
        if(in_array($reservation->status,['checkIn'])){
            return $this->response->error('reservation.statusError');
        }

        //Validate DateStart
        $dateStart = \DateTime::createFromFormat('d/m/Y H:i:s', $reservation->dateStart);
        $dateNow = new \DateTime('now',(new \DateTimeZone(env('APP_TIMEZONE'))));

        if($dateStart >= $dateNow){
            return $this->response->error('reservation.dateError');
        }

        if( !$reservation->delete() ) {
            return $this->response->errorInternal();
        }

        return $this->response->noContent()->setStatusCode(200);
    }
}
