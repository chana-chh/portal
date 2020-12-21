<?php

namespace App\Models;

use App\Classes\Model;

class Korisnik extends Model
{
    protected $table = 'korisnici';

    public function findByUsername(string $username)
    {
        $sql = "SELECT * FROM {$this->table} WHERE korisnicko_ime = :kime LIMIT 1;";
        $params = [':kime' => $username];
        return $this->fetch($sql, $params)[0];
    }

    public function findByEmail(string $email)
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1;";
        $params = [':email' => $email];
        return $this->fetch($sql, $params)[0];
    }

    public function imePrezime()
    {
        return "{$this->ime} {$this->prezime}";
    }

    public function prezimeIme()
    {
        return "{$this->prezime} {$this->ime}";
    }

    public function logovi()
    {
        return $this->hasMany('App\Models\Log', 'korisnik_id');
    }

    public function clanci()
    {
        return $this->hasMany('App\Models\Clanak', 'korisnik_id');
    }

    public function dokumenti()
    {
        return $this->hasMany('App\Models\Dokument', 'clanak_id');
    }

    public function dozvoljeneKategorije()
    {
        $model = new Kategorija();
        $sql = "SELECT * FROM kategorije WHERE id IN({$this->dozvoljene_kategorije});";
        $kat = $model->fetch($sql);
        return $kat;
    }

    public function dozvoljeneKatDok()
    {
        $model = new DokumentKategorija();
        $sql = "SELECT * FROM dokumenti_kategorije WHERE id IN({$this->dozvoljene_kategorije_dok});";
        $kat = $model->fetch($sql);
        return $kat;
    }
}
