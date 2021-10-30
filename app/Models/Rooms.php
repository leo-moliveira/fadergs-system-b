<?php

namespace App\Models;

class Rooms extends BaseModel
{
    protected $table = 'rooms';

    protected $fillable = ['id', 'status', 'price', 'description', 'created_at'];
}
