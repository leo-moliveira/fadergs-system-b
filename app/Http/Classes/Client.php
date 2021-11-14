<?php

namespace App\Http\Classes;

use App\Models\User;
use App\Models\Client as ModelClient;
use App\Models\Addreses;
use App\Models\Phone;
use Carbon\Carbon;

class Client
{
    public $user;
    public $client;
    public $addreses;
    public $phones;

    public function __construct()
    {
        $this->user = new User();
        $this->client = new ModelClient();
        $this->addreses = new Addreses();
        $this->phones[] = new Phone();
    }

    public function create($objCleintInfo)
    {
        //create user name
        $fullNameArray = explode(" ", $objCleintInfo->full_name);

        if(count($fullNameArray) > 1) {
            $userName = mb_strtolower($fullNameArray[array_key_first($fullNameArray)]) . "."
                . mb_strtolower($fullNameArray[array_key_last($fullNameArray)]);
        }else{
            $userName = $objCleintInfo->full_name;
        }

        $userAttributes = [
            'user_name'     => $userName,
            'password'      => app('hash')->make("1234"),
            'role'          => "client",
            'status'        => "true",
            'created_at'    => Carbon::now()->toDateTimeString()
        ];

        $counter = 0;
        while(User::where('user_name', $userAttributes['user_name'])->count() != 0){
            $counter++;
            $userAttributes['user_name'] = $userName.$counter;
        }

        $this->user = User::create($userAttributes);

        //create client
        $clientAttributes = [
            'user_id'           => $this->user->id,
            'first_name'        => $fullNameArray[array_key_first($fullNameArray)],
            'last_name'         => $fullNameArray[array_key_last($fullNameArray)],
            'full_name'         => $objCleintInfo->full_name,
            'email'             => (property_exists($objCleintInfo,'email')) ? $objCleintInfo->email : null,
            'cpf'               => $objCleintInfo->cpf,
            'rg'                => $objCleintInfo->rg,
            'gender'            => $objCleintInfo->gender,
            'status'            => $objCleintInfo->status,
            'registration_date' => $objCleintInfo->registration_date,
            'created_at'        => Carbon::now()->toDateTimeString()
        ];

        $this->client = ModelClient::create($clientAttributes);

        //create address
        $clientAddressAttributes = [
            'user_id'           => $this->user->id,
            'address'           => $objCleintInfo->address,
            'number'            => $objCleintInfo->number,
            'complement'        => $objCleintInfo->complement,
            'city'              => $objCleintInfo->city,
            'state'             => $objCleintInfo->state,
            'country'           => $objCleintInfo->country,
            'zip_code'          => $objCleintInfo->zip_code,
            'active'            => true,
            'created_at'        => Carbon::now()->toDateTimeString()
        ];
        $this->addreses = Addreses::create($clientAddressAttributes);

        //create phones
        foreach ($objCleintInfo->phone_numbers as $phone){
            $phoneAttributes = [
                'user_id'       => $this->user->id,
                'number'        => $phone,
                'created_at'    => Carbon::now()->toDateTimeString()
            ];
            $this->phones[] = Phone::create($phoneAttributes);
        }

        return $this;
    }


}
