<?php

namespace App\Models;

use App\Classes\Model;

class BibliotekaVrsta extends Model
{
    protected $table = 'biblioteka_vrste';

    public function korisnik()
    {
        return $this->belongsTo('App\Models\Korisnik', 'korisnik_id');
    }

    public function dokument()
    {
        return $this->hasMany('App\Models\BibliotekaDokument', 'vrsta_id');
    }

}