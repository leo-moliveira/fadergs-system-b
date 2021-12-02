<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Classes\Helpers;
use App\Models\Reservation;
use Dingo\Api\Http\Request;
use PagSeguro\Resources\Builder\Checkout\Payment;

class PaymentController extends BaseController
{
    protected $payment;

    public function accountByReservId(Request $request, $reservationId){
        //Check permissions
        if (!Helpers::validateUserRole($request->user(), ['admin', 'manager'])){
            return $this->response->errorUnauthorized(trans('rooms.unauthorized'));
        }

        //Validate Reservation
        $reservation = Reservation::findOrFail($reservationId);
        if(!$reservation){
            return $this->response->errorNotFound('reservation.roomNotFound');
        }

        if (!in_array($reservation->status, ['checkOut', 'pendingPayment'])) {
            return $this->errorBadRequest('');
        }

        $payment = new \PagSeguro\Domains\Requests\Payment();

        $payment->addItems()->withParameters(
            '0001',
            'Notebook prata',
            2,
            130.00
        );

        $payment->addItems()->withParameters(
            '0002',
            'Notebook preto',
            2,
            430.00
        );

        $payment->setCurrency("BRL");

        $payment->setExtraAmount(11.5);

        $payment->setReference("LIBPHP000001");

        $payment->setRedirectUrl("http://www.lojamodelo.com.br");

// Set your customer information.
        $payment->setSender()->setName('João Comprador');
        $payment->setSender()->setEmail('email@comprador.com.br');
        $payment->setSender()->setPhone()->withParameters(
            11,
            56273440
        );
        $payment->setSender()->setDocument()->withParameters(
            'CPF',
            'insira um numero de CPF valido'
        );

        $payment->setShipping()->setAddress()->withParameters(
            'Av. Brig. Faria Lima',
            '1384',
            'Jardim Paulistano',
            '01452002',
            'São Paulo',
            'SP',
            'BRA',
            'apto. 114'
        );
        $payment->setShipping()->setCost()->withParameters(20.00);
        $payment->setShipping()->setType()->withParameters(\PagSeguro\Enum\Shipping\Type::SEDEX);



        try {

            /**
             * @todo For checkout with application use:
             * \PagSeguro\Configuration\Configure::getApplicationCredentials()
             *  ->setAuthorizationCode("FD3AF1B214EC40F0B0A6745D041BF50D")
             */
            $result = $payment->register(
                \PagSeguro\Configuration\Configure::getAccountCredentials()
            );

            echo "<h2>Criando requisi&ccedil;&atilde;o de pagamento</h2>"
                . "<p>URL do pagamento: <strong>$result</strong></p>"
                . "<p><a title=\"URL do pagamento\" href=\"$result\" target=\_blank\">Ir para URL do pagamento.</a></p>";
        } catch (Exception $e) {
            die($e->getMessage());
        }

        dd($payment);die();
    }
}
