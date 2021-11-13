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
        'user_id','first_name', 'last_name', 'full_name','email', 'cpf', 'rg', 'gender', 'status', 'last_reservation', 'registration_date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function address()
    {
        return $this->hasMany(Addreses::class, 'user_id', 'user_id');
    }

    public function phone()
    {
        return $this->hasMany(Phone::class,'user_id','user_id');
    }
}
