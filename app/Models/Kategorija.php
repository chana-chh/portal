<?php

namespace App\Models;

use App\Classes\Model;

class Kategorija extends Model
{
    protected $table = 'kategorije';

    public function clanci()
    {
        return $this->hasMany('App\Models\Clanak', 'korisnik_id');
    }

    public function broj_kategorije()
    {
        $sql = "SELECT COUNT(*) AS broj_kategorije FROM clanci WHERE kategorija_id = {$this->id}";
        return (int) $this->fetch($sql)[0]->broj_kategorije;
    }
}
