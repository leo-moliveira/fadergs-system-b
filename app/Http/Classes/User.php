<?php

namespace App\Http\Classes;
use App\Models\User as ModelUser;
use Illuminate\Contracts\Support\Arrayable;

class User implements Arrayable {
    private $user;
    //user
    protected $user_name;
    protected $role;
    protected $user_status;
    //employee client
    protected $first_name;
    protected $last_name;
    protected $employee_status;
    protected $client_status;
    protected $email;
    protected $cpf;
    protected $rg;
    protected $gender;

    public function __construct(ModelUser $user) {
        $this->user = $user->with(['employee','client'])->find($user->id);

        $this->user_name = $this->user->user_name;
        $this->role = $this->user->role;
        $this->user_status = $this->user->status;

        if($this->user->client){
            $this->first_name = $this->user->client->first_name;
            $this->last_name  = $this->user->client->last_name;
            $this->client_status = $this->user->client->status;
            $this->email = $this->user->client->email;
            $this->cpf = $this->user->client->cpf;
            $this->rg = $this->user->client->rg;
            $this->gender = $this->user->client->gender;
        }else{
            $this->first_name = $this->user->employee->first_name;
            $this->last_name  = $this->user->employee->last_name;
            $this->employee_status = $this->user->employee->status;
        }

    }

    public function toArray()
    {
        return [
            'user_name' => $this->user_name,
            'role' => $this->role,
            'user_status' => $this->user_status,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'employee_status' => $this->employee_status,
            'client_status' => $this->client_status,
            'email' => $this->email,
            'cpf' => $this->cpf,
            'rg' => $this->rg,
            'gender' => $this->gender,
        ];
    }


}
