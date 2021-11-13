<?php

namespace App\Transformers;

use App\Models\Phone;
use League\Fractal\TransformerAbstract;

class PhoneTransformer extends TransformerAbstract
{
    public function transform(Phone $phone)
    {
        return $phone->attributesToArray();
    }
}
