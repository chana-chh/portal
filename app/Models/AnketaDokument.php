<?php

namespace App\Models;

use App\Classes\Model;

class AnketaDokument extends Model
{
    protected $table = 'ankete_dokumenta';

    public function odgovor()
    {
        return $this->belongsTo('App\Models\AnketaOdgovor', 'odgovor_id');
    }

    public function korisnik()
    {
        return $this->belongsTo('App\Models\AnketaIspitanik', 'korisnik_id');
    }
}
