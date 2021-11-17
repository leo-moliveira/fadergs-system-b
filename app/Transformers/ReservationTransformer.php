<?php

namespace App\Transformers;

use App\Models\Reservation;
use League\Fractal\TransformerAbstract;

class ReservationTransformer extends TransformerAbstract
{
    public function transform(Reservation $reservation)
    {
        return $reservation->attributesToArray();
    }
}
