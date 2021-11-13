<?php

namespace App\Models;



class Phone extends BaseModel
{
    protected $table = 'phone_numbers';

    protected $fillable = [
        'id',
        'user_id',
        'number',
        'created_at'
    ];
}
