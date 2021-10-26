<?php

namespace App\Transformers;

use App\Models\Rooms;
use League\Fractal\TransformerAbstract;

class RoomTransformer extends TransformerAbstract
{
    public function transform(Rooms $rooms){
        return $rooms->attributesToArray();
    }
}
