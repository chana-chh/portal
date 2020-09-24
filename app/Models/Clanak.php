<?php

namespace App\Models;

use App\Classes\Model;

class Clanak extends Model
{
    protected $table = 'clanci';

    public function kategorija()
    {
        return $this->belongsTo('App\Models\Kategorija', 'kategorija_id');
    }

    public function korisnik()
    {
        return $this->belongsTo('App\Models\Korisnik', 'korisnik_id');
    }

    public function clanci_kategorija($id_kategorije)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE kategorija_id = '{$id_kategorije}'";
        return $this->fetch($sql);
    }

    public function broj_objavljenih()
    {
        $sql = "SELECT COUNT(*) AS broj_clanaka FROM clanci
        WHERE  objavljen = 1
       	AND deleted_at IS NULL";
        return (int) $this->fetch($sql)[0]->broj_clanaka;
    }

    public function poslednja_objava()
    {
        $sql = "SELECT MAX(published_at) AS poslednja_objava FROM clanci";
        return $this->fetch($sql)[0]->poslednja_objava;
    }
}
