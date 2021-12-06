<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Classes\Helpers;
use App\Http\Classes\MyPayment;
use App\Models\Client;
use App\Models\Reservation;
use App\Models\Rooms;
use App\Transformers\PaymentTransformer;
use Illuminate\Http\Request;
use App\Models\Payment;

class PaymentController extends BaseController
{
    protected $payment;

    public function __construct()
    {
        $this->payment = new Payment;
    }
    /**
     * @OA\Get (
     *     path="/api/payment/reservation/{id}",
     *     tags={"Payment"},
     *     summary = "Get payment from reservation id",
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of reservation to show",
     *         required=true,
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Return payment"
     *     ),
     *     security={{"JWT":{}}}
     * )
     * @return \Dingo\Api\Http\Response
     */
    public function accountByReservId(Request $request, $id){
        //Check permissions
        if (!Helpers::validateUserRole($request->user(), ['admin', 'manager'])){
            return $this->response->errorUnauthorized(trans('rooms.unauthorized'));
        }

        //Validate Reservation
        $reservation = Reservation::findOrFail($id);
        if(!$reservation){
            return $this->response->errorNotFound('reservation.roomNotFound');
        }

        $payment = $this->payment->where('reservation_id', '=', $id)->paginate(25);
        return $this->response->paginator($payment, new PaymentTransformer());
    }

    /**
     * @OA\Get (
     *     path="/api/payment/client/{id}",
     *     tags={"Payment"},
     *     summary = "Get payment from client id",
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of client to show",
     *         required=true,
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Return payment"
     *     ),
     *     security={{"JWT":{}}}
     * )
     * @return \Dingo\Api\Http\Response
     */
    public function accountByclient(Request $request, $id){
        //Check permissions
        if (!Helpers::validateUserRole($request->user(), ['admin', 'manager'])){
            return $this->response->errorUnauthorized(trans('rooms.unauthorized'));
        }

        //Validate Client
        $client = Client::findOrFail($id);
        if(!$client){
            return $this->response->errorNotFound('reservation.roomNotFound');
        }

        $payment = $this->payment->where('client_id', '=', $id)->paginate(25);
        return $this->response->paginator($payment, new PaymentTransformer());
    }

    /**
     * @OA\Get (
     *     path="/api/payment/{status}",
     *     tags={"Payment"},
     *     summary = "Get list of payments by status",
     *     @OA\Parameter(
     *         name="status",
     *         in="path",
     *         description="Status to search",
     *         required=true,
     *         @OA\Schema(
     *                      type="string",
     *                      enum={"pending", "paid"},
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
    public function listByStatus(Request $request, $status){
        //Check permissions
        if (!Helpers::validateUserRole($request->user(), ['admin', 'manager'])){
            return $this->response->errorUnauthorized(trans('rooms.unauthorized'));
        }

        //check status
        if (!in_array($status, Payment::AVAILABLE_STATUS)) {
            return $this->errorBadRequest("Invalid Status");
        }

        $payment = $this->payment->where('status', '=', $status)->paginate(25);
        return $this->response->paginator($payment, new PaymentTransformer());
    }

    /**
     * @OA\Get (
     *     path="/api/pay/{id}",
     *     tags={"Payment"},
     *     summary = "Get payment url (pagseguro) by id of payment ",
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of reservation to show",
     *         required=true,
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Return payment"
     *     ),
     *     security={{"JWT":{}}}
     * )
     * @return \Dingo\Api\Http\Response
     */
    public function generatePayUrl(Request $request, $id){
        if(!$payment = Payment::findOrFail($id)){
            return $this->response->errorNotFound();
        }
        if(!$client = Client::findOrFail($payment->client_id)){
            return $this->response->errorNotFound();
        }

        if(!$payment){
            return $this->errorBadRequest("Invalid id");
        }

        $payURL = new MyPayment($payment->client_id);

        $dateStart = \DateTime::createFromFormat('Y-m-d H:i:s',$payment->date_start);
        $dateEnd = \DateTime::createFromFormat('Y-m-d H:i:s',$payment->date_end);

        $dateDiffDays = $dateStart->diff($dateEnd)->days;

        $payURL->items = [
            'desc' => "diarias -" . $payment->description,
            'days' => $dateDiffDays,
            'value'=> $payment->price
        ];
        $payURL->costumerName = $client->full_name;
        $payURL->costumerPhone = '11236548759';
        $payURL->costumerEmail = $client->email;
        $payURL->notificationURL = 'teste.com';
        $result = $payURL->generetePSURL();

        $payment->PayCode = substr($result,strpos($result,"code=")+5);
        $payment->update();

        return $this->response->array(['URL' => $result]);
    }

    /**
     * @OA\Put (
     *     path="/api/pay",
     *     tags={"Payment"},
     *     summary = "Update payment information",
     *     @OA\RequestBody(
     *          required=true,
     *          description="Data to payment",
     *          @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(property="id", type="integer", example="8"),
     *               @OA\Property(property="client_id", type="integer", example="1"),
     *               @OA\Property(property="reservation_id", type="integer", example="1"),
     *               @OA\Property(property="room_number", type="integer", example="101"),
     *               @OA\Property(property="date_start", type="date-time", example="31/10/2021 17:12:52"),
     *               @OA\Property(property="date_end", type="date-time", example="31/10/2021 17:12:52"),
     *               @OA\Property(property="pay_date", type="date-time", example="10/10/2021 04:12:52"),
     *               @OA\Property(property="pay_code", type="string", example="9272B7ECB4B4284BB4479F90D676DB21"),
     *               @OA\Property(property="price", type="double", example="200.2"),
     *               @OA\Property(property="status", type="string", example="checkOut",description = "pending, paid"),
     *               @OA\Property(property="description", type="string", example="2 beds and 1 bathroom")
     *           ))
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
    private function update(Request $request)
    {
        //Check permissions
        if (!Helpers::validateUserRole($request->user(), ['admin', 'manager'])){
            return $this->response->errorUnauthorized(trans('rooms.unauthorized'));
        }

        //Validate request
        $validator = \Validator::make($request->input(), [
            'id'                => 'required|integer',
            'client_id'         => 'required|integer',
            'reservation_id'    => 'required|integer',
            'room_number'       => 'required|integer',
            'date_start'        => 'required|date_format:d/m/Y H:i:s',
            'date_end'          => 'required|date_format:d/m/Y H:i:s',
            'pay_code'          => 'required|string',
            'pay_date'          => 'required|date_format:d/m/Y H:i:s',
            'price'             => 'nullable|between:0,99.99',
            'status'            => 'nullable|string',
            'description'       => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }

        $this->payment = Payment::findOrFail($request->get('id'));
        $reservation = Reservation::findOrFail($request->get('reservation_id'));
        $client = Client::findOrFail($request->get('client_id'));
        $room = Rooms::findOrFail($request->get('room_number'));

        if(!$this->payment || !$reservation || !$client || !$room){
            return $this->response->errorNotFound();
        }

        $this->payment->reservation_id = ($request->get('reservation_id')) ? $request->get('reservation_id') : $this->payment->reservation_id;
        $this->payment->client_id = ($request->get('client_id')) ? $request->get('client_id') : $this->payment->client_id;
        $this->payment->room_number = ($request->get('room_number')) ? $request->get('room_number') : $this->payment->room_number;
        $this->payment->date_start = ($request->get('date_start')) ? $request->get('date_start') : $this->payment->date_start;
        $this->payment->date_end = ($request->get('date_end')) ? $request->get('date_end') : $this->payment->date_end;
        $this->payment->pay_date = ($request->get('pay_date')) ? $request->get('pay_date') : $this->payment->pay_date;
        $this->payment->pay_code = ($request->get('pay_code')) ? $request->get('pay_code') : $this->payment->pay_code;
        $this->payment->price = ($request->get('price')) ? $request->get('price') : $this->payment->price;
        $this->payment->status = ($request->get('status')) ? $request->get('status') : $this->payment->status;
        $this->payment->description = ($request->get('description')) ? $request->get('description') : $this->payment->description;
        $this->payment->update();

        $messege = "Payment updated";
        return $this->response->noContent()->setStatusCode(200,$messege);
    }
}
