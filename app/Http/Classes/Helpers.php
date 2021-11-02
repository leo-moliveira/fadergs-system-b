<?php

namespace App\Http\Classes;

use App\Models\User;

class Helpers
{
    public static function validateUserRole(User $user, $permitions) : bool
    {
        return (in_array($user->role, $permitions)) ? true : false;
    }
}
