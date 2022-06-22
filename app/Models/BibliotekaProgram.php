<?php

namespace App\Models;

use App\Classes\Model;

class BibliotekaProgram extends Model
{
    protected $table = 'biblioteka_program';

    public function korisnik()
    {
        return $this->belongsTo('App\Models\Korisnik', 'korisnik_id');
    }

    public function obuke()
    {
        return $this->hasMany('App\Models\BibliotekaObuka', 'program_id');
    }

}