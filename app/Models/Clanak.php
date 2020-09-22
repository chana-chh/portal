<?php

namespace App\Models;

use App\Classes\Model;

class Clanak extends Model
{
    protected $table = 'clanci';

    public function kategorija()
    {
        return $this->belongsTo('App\Models\Kategorija', 'kategorija_id');
    }

    public function korisnik()
    {
        return $this->belongsTo('App\Models\Korisnik', 'korisnik_id');
    }
}
