<?php

namespace App\Models;

use App\Classes\Model;

class Kategorija extends Model
{
    protected $table = 'kategorije';

    public function clanci()
    {
        return $this->hasMany('App\Models\Clanak', 'korisnik_id');
    }
}
