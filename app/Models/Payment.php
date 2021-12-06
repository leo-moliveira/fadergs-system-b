<?php

namespace App\Models;

class Payment extends BaseModel
{
    public const AVAILABLE_STATUS = [
        'pending', 'paid'
    ];

    protected $table = 'payments';

    protected $fillable = ['id', 'client_id', 'reservation_id', 'room_number', 'description', 'price', 'status',
        'pay_code', 'pay_date', 'date_start', 'date_end', 'created_at', 'updated_at'];
}
