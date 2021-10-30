<?php

namespace App\Models;

class Employee extends BaseModel {

    protected $table = 'employees';
    protected $primaryKey = 'user_id';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'first_name', 'last_name', 'status'
    ];
}
