<?php

namespace App\Models;

use App\Classes\Model;

class BibliotekaDokument extends Model
{
    protected $table = 'biblioteka_dokumenta';

    public function korisnik()
    {
        return $this->belongsTo('App\Models\Korisnik', 'korisnik_id');
    }

    public function vrsta()
    {
        return $this->belongsTo('App\Models\BibliotekaVrsta', 'vrsta_id');
    }

    public function obuka()
    {
        return $this->belongsTo('App\Models\BibliotekaObuka', 'obuka_id');
    }

    public function sledeci()
    {
        $sql="SELECT AUTO_INCREMENT
        FROM information_schema.tables
        WHERE table_name = '{$this->table()}'
        AND table_schema = 'portal';";
        
        return (int) $this->fetch($sql)[0]->AUTO_INCREMENT;
    }

}
