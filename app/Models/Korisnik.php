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
        $in = $this->dozvoljene_kategorije;
        if ($in) {
        $sql = "SELECT * FROM kategorije WHERE id IN({$in});";
        $kat = $model->fetch($sql);
         return $kat;
        }else{
            return NULL;
        }
    }
    public function dozvoljeneKategorijeNiz()
    {
        $in = $this->dozvoljene_kategorije;
        if ($in) {
            $kat = explode( ',', $in );
            return $kat;
        }else{
            return NULL;
        } 
    }

    public function dozvoljeneKatDok()
    {
        $model = new DokumentKategorija();
        $in = $this->dozvoljene_kategorije_dok;
        if ($in) {
            $sql = "SELECT * FROM dokumenti_kategorije WHERE id IN({$in});";
            $kat = $model->fetch($sql);
            return $kat;
        }else{
            return NULL;
        } 
    }

    public function dozvoljeneKatDokNiz()
    {
        $in = $this->dozvoljene_kategorije_dok;
        if ($in) {
            $kat = explode( ',', $in );
            return $kat;
        }else{
            return NULL;
        } 
    }

    public function dkdnaziv()
    {
        $dozvoljene = $this->dozvoljeneKatDok();
        if ($dozvoljene) {
            $za_naziv = array_column($dozvoljene, 'naziv');
            $nazivi = implode(',', $za_naziv);
            return $nazivi;
        }else{
            return "Није дефинисано!";
        }
        
    }

    public function dknaziv()
    {
        $dozvoljene = $this->dozvoljeneKategorije();
        if ($dozvoljene) {
            $za_naziv = array_column($dozvoljene, 'naziv');
            $nazivi = implode(',', $za_naziv);
            return $nazivi;
        }else{
            return "Није дефинисано!";
        }
        
    }

    public function ankete()
    {
        return $this->hasMany('App\Models\Anketa', 'korisnik_id');
    }

    public function anketedok()
    {
        return $this->hasMany('App\Models\AnketaDokument', 'korisnik_id');
    }

    public function anketeIspitanik()
    {
        return $this->hasMany('App\Models\AnketaIspitanik', 'korisnik_id');
    }

    public function anketeOdgovor()
    {
        return $this->hasMany('App\Models\AnketaOdgovor', 'korisnik_id');
    }

    public function anketePitanje()
    {
        return $this->hasMany('App\Models\AnketaPitanje', 'korisnik_id');
    }

    public function anketeTip()
    {
        return $this->hasMany('App\Models\AnketaTip', 'korisnik_id');
    }

    public function anketaTipOdgovora()
    {
        return $this->hasMany('App\Models\AnketaTipOdgovora', 'korisnik_id');
    }
}
