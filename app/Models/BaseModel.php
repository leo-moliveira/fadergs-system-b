<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BaseModel extends Model
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
