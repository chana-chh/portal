<?php

namespace App\Models;

use App\Classes\Model;

class Dokument extends Model
{
    protected $table = 'dokumenti';

    public function korisnik()
    {
        return $this->belongsTo('App\Models\Korisnik', 'korisnik_id');
    }

    public function clanak()
    {
        return $this->belongsTo('App\Models\Clanak', 'clanak_id');
    }
}
