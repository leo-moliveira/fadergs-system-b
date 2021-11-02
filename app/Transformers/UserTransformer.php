<?php

namespace App\Transformers;

use App\Http\Classes\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    public function transform(User $user){
        return $user->toArray();
    }
}
