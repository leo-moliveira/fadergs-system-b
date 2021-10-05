<?php

namespace App\Models;

use Illuminate\Support\Str;

class User extends BaseUser
{
    public function getAttribute($key) {
        if (array_key_exists($key, $this->relations)) {
            return parent::getAttribute($key);
        }

        return parent::getAttribute(Str::snake($key));
    }

    public function setAttribute($key, $value) {
        return parent::setAttribute(Str::snake($key), $value);
    }
}
