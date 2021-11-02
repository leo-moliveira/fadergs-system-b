<?php

namespace App\Models;

class Cleaning extends BaseModel
{
    protected $table = 'cleaning';
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'rooms_id', 'manager_id', 'employee_id', 'cleaning_date', 'status', 'created_at', 'updated_at'
    ];

    public function manager(){
        return $this->hasOne(User::class, 'manager_id', 'id');
    }
    public function employee(){
        return $this->hasOne(User::class, 'employee_id', 'id');
    }
}
