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

    public function dok_vrsta($vrsta)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE vrsta LIKE '%{$vrsta}%'";
        return $this->fetch($sql);
    }

    public function broj_vrste($vrsta)
    {
        $sql = "SELECT COUNT(*) AS broj_vrste FROM {$this->table()} WHERE vrsta LIKE '%{$vrsta}%'";
        return (int) $this->fetch($sql)[0]->broj_vrste;
    }
}
