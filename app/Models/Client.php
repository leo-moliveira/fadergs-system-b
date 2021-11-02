<?php

namespace App\Models;

class Client extends BaseModel {

    protected $table = 'clients';
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'cpf', 'rg', 'gender', 'status', 'last_reservation', 'registration_date'
    ];
}
