<?php

namespace App\Transformers;

use App\Models\Client;
use League\Fractal\TransformerAbstract;

class ClientTransformer extends TransformerAbstract
{
    public function transform(Client $client){
        return $client->attributesToArray();
    }
}
