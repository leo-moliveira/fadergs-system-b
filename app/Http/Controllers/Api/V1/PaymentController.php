<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Classes\Helpers;
use App\Http\Classes\MyPayment;
use App\Models\Client;
use App\Models\Reservation;
use App\Transformers\PaymentTransformer;
use Dingo\Api\Http\Request;
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
}
