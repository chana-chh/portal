<?php

namespace App\Models;

use App\Classes\Model;

class BibliotekaObuka extends Model
{
    protected $table = 'biblioteka_obuke';

    public function korisnik()
    {
        return $this->belongsTo('App\Models\Korisnik', 'korisnik_id');
    }

    public function kategorija()
    {
        return $this->belongsTo('App\Models\BibliotekaKategorija', 'kategorija_id');
    }

    public function program()
    {
        return $this->belongsTo('App\Models\BibliotekaProgram', 'program_id');
    }

    public function mat()
    {
        return $this->hasMany('App\Models\BibliotekaDokument', 'obuka_id');
    }

    public function broj_doc()
    {
        $sql = "SELECT COUNT(*) AS broj_doc FROM {$this->table()};";
        return (int) $this->fetch($sql)[0]->broj_doc;
    }

    public function poslednjiUp()
    {
        $sql = "SELECT MAX(created_at) AS poslednjiUp FROM {$this->table()}";
        return $this->fetch($sql)[0]->poslednjiUp;
    }

}
