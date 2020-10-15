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

    public function kategorija()
    {
        return $this->belongsTo('App\Models\DokumentKategorija', 'kategorija_id');
    }

    public function vrsta()
    {
        return $this->belongsTo('App\Models\DokumentVrsta', 'vrsta_id');
    }

    public function clanak()
    {
        return $this->belongsTo('App\Models\Clanak', 'clanak_id');
    }

    public function dokument_kategorija($id_kategorije)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE kategorija_id = '{$id_kategorije}'";
        return $this->fetch($sql);
    }

    public function broj_doc()
    {
        $sql = "SELECT COUNT(*) AS broj_doc FROM dokumenti;";
        return (int) $this->fetch($sql)[0]->broj_doc;
    }

    public function poslednjiUp()
    {
        $sql = "SELECT MAX(created_at) AS poslednjiUp FROM dokumenti";
        return $this->fetch($sql)[0]->poslednjiUp;
    }

}
