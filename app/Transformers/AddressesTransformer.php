<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Addreses;

class AddressesTransformer extends TransformerAbstract
{
    public function transform(Addreses $addresses)
    {
        return $addresses->attributesToArray();
    }
}
