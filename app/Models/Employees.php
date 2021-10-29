<?php

namespace App\Models;

class Employees extends BaseModel {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'first_name', 'last_name', 'status', 'role'
    ];

}
