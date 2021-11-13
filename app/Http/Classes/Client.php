<?php

namespace App\Http\Classes;

use App\Models\User;
use App\Models\Client as ModelClient;
use App\Models\Address;
use App\Models\Phone;
use Carbon\Carbon;

class Client
{
    private $user;
    private $client;
    private $address;
    private $phones;

    public function __construct(User $user,ModelClient $client,Address $address, Phone $phones)
    {
        $this->user = $user;
        $this->client = $client;
        $this->address = $address;
        $this->phones = $phones;
    }

    public function create($objCleintInfo)
    {
        //create user name
        $fullNameArray = explode(" ", $objCleintInfo->full_name);
        $userName = mb_strtolower($fullNameArray[array_key_first($fullNameArray)]) . "."
            .mb_strtolower($fullNameArray[array_key_last($fullNameArray)]);
        $userAtributes = [
            'user_name'     => $userName,
            'password'      => app('hash')->make("1234"),
            'role'          => "client",
            'status'        => "true",
            'created_at'    => Carbon::now()->toDateTimeString()
        ];

        $counter = 0;
        while(User::where('user_name', $userAtributes['user_name'])->count() != 0){
            $counter++;
            $userAtributes['user_name'] = $userAtributes['user_name'].$counter;
        }

        $this->user = User::create($userAtributes);

        //create client
        $clientAtributes = [
            'user_id'       => $this->user->id,
            'first_name'    => $fullNameArray[array_key_first($fullNameArray)],
            'last_name'     => $fullNameArray[array_key_last($fullNameArray)],
            'full_name'     => $objCleintInfo->full_name,
            'email'         => $objCleintInfo->email,
            'cpf'           => $objCleintInfo->cpf,
            'rg'            => $objCleintInfo->rg,
            'gender'        => $objCleintInfo->gender,
            'status'        => $objCleintInfo->status,
            'created_at'    => $objCleintInfo->created_at
        ];
        $this->client = ModelClient::create($clientAtributes);

        //create address
        $clientAddressAtributes = [

        ];
        $this->address = Address::create($clientAddressAtributes);
    }
}
