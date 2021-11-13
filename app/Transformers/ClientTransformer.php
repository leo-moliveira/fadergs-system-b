<?php

namespace App\Transformers;

use App\Models\Client;
use League\Fractal\TransformerAbstract;

class ClientTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'user',
        'address',
        'phone'
    ];

    public function transform(Client $client)
    {
        return $client->attributesToArray();
    }

    public function includeUser (Client $client)
    {
        $user = $client->user;
        return $this->item($user, new UserTransformer());
    }

    public function includeAddress (Client $client)
    {
        $address = $client->address;
        return $this->collection($address, new AddressesTransformer());
    }

    public function includePhone (Client $client)
    {
        $phone = $client->phone;
        return $this->collection($phone, new PhoneTransformer());
    }
}
