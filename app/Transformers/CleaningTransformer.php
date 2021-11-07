<?php

namespace App\Transformers;

use App\Models\Cleaning;
use League\Fractal\TransformerAbstract;

class CleaningTransformer extends TransformerAbstract
{
    public function transform(Cleaning $cleaning){
        return $cleaning->attributesToArray();
    }
}
