<?php

namespace App\Models;

use App\Classes\Model;
use App\Classes\Db;

class Log extends Model
{
    protected $table = 'logovi';

    public function korisnik()
    {
        return $this->belongsTo('App\Models\Korisnik', 'korisnik_id');
    }
}
