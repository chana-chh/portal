<?php

namespace App\Models;

use App\Classes\Model;

class DokumentKategorija extends Model
{
    protected $table = 'dokumenti_kategorije';

    public function dokument()
    {
        return $this->hasMany('App\Models\Dokument', 'kategorija_id');
    }

    public function broj_kategorije()
    {
        $sql = "SELECT COUNT(*) AS broj_kategorije FROM dokumenti WHERE kategorija_id = {$this->id}";
        return (int) $this->fetch($sql)[0]->broj_kategorije;
    }
}
