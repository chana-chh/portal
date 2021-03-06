<?php

namespace App\Models;

use App\Classes\Model;

class DokumentVrsta extends Model
{
    protected $table = 'dokumenti_vrste';

    public function dokument()
    {
        return $this->hasMany('App\Models\Dokument', 'vrsta_id');
    }

    public function broj($kat_id)
    {
        $sql = "SELECT COUNT(*) AS broj_vrste FROM dokumenti WHERE arhiva IS NULL AND vrsta_id = {$this->id}";
        if (isset($kat_id)) {
            $sql = $sql. " AND kategorija_id = $kat_id;";
        }
        return (int) $this->fetch($sql)[0]->broj_vrste;
    }

    public function brojarhiva($kat_id)
    {
        $sql = "SELECT COUNT(*) AS broj_vrste FROM dokumenti WHERE arhiva IS NOT NULL AND vrsta_id = {$this->id}";
        if (isset($kat_id)) {
            $sql = $sql. " AND kategorija_id = $kat_id;";
        }
        return (int) $this->fetch($sql)[0]->broj_vrste;
    }
}
