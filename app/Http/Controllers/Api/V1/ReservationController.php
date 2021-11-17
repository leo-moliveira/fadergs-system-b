<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Classes\Helpers;
use App\Models\Client;
use App\Models\Rooms;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReservationController extends BaseController
{
    protected $reservation;

    /**
     * @OA\Get (
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

    }

    public function show(Request $request, $id){

    }

    public function reservationByClient(Request $request,$id){

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
     *              @OA\Property(property="date_start", type="date", example="2021-10-20 00:00:00"),
     *              @OA\Property(property="date_end", type="date", example="2021-10-31 17:00:00")
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

        if($dateStart >= $dateEnd || $dateStart > $dateNow || $dateEnd < $dateNow){
            return $this->response->error('reservation.dateError');
        }

        $dateDiffDays = $dateStart->diff($dateEnd)->days;

        //Calculate reservation price
        $reservationPrice = $room->price * $dateDiffDays;

        //Insert reservation
        $atributes = [
            'room_number'   => $request->get('room_number'),
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

    public function update(Request $request){

    }

    public function delete(Request $request){

    }
}
