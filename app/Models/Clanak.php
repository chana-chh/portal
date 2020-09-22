<?php

namespace App\Models;

use App\Classes\Model;

class Clanak extends Model
{
    protected $table = 'clanci';

    public function kategorija()
    {
        return $this->belongsToMany('App\Models\Kategorija', 'id_kategorije');
    }

    public function korisnik()
    {
        return $this->belongsTo('App\Models\Korisnik', 'korisnik_id');
    }
}
