<?php

namespace App\Models;

class Addreses extends BaseModel
{
    protected $table = 'addreses';

    protected $fillable = [
        'id',
        'user_id',
        'address',
        'number',
        'complement',
        'city',
        'state',
        'country',
        'zip_code',
        'active',
        'created_at',
        'updated_at'
    ];


}
