<?php

namespace App\Models;

class Reservation extends BaseModel
{
    protected $table = 'reservations';

    protected $fillable = [
        'id',
        'client_id',
        'room_id',
        'date_start',
        'date_end',
        'check_in',
        'check_out',
        'price',
        'status',
        'created_at',
        ];
}
