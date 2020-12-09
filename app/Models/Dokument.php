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
        $sql = "SELECT COUNT(*) AS broj_doc FROM {$this->table()};";
        return (int) $this->fetch($sql)[0]->broj_doc;
    }

    public function poslednjiUp()
    {
        $sql = "SELECT MAX(created_at) AS poslednjiUp FROM {$this->table()}";
        return $this->fetch($sql)[0]->poslednjiUp;
    }

    public function isti(string $naziv_link)
    {
        $zaupit = '%'.$naziv_link.'%';
        $sql = "SELECT COUNT(*) AS broj FROM {$this->table()} WHERE link LIKE '$zaupit';";
        return (int) $this->fetch($sql)[0]->broj;
    }

    public function kategorije_za_link(string $naziv_link)
    {
        $zaupit = '%'.$naziv_link.'%';
        $sql = "SELECT d.id, k.naziv FROM {$this->table()} d
        JOIN dokumenti_kategorije k ON d.kategorija_id=k.id
        WHERE link LIKE '$zaupit';";
        return $this->fetch($sql);
    }

}
