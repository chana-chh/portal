<?php

namespace App\Models;

use App\Classes\Model;

class Anketa extends Model
{
    protected $table = 'ankete';

    public function tip()
    {
        return $this->belongsTo('App\Models\AnketaTip', 'tip_id');
    }

    public function pitanja()
    {
        return $this->hasMany('App\Models\AnketaPitanje', 'anketa_id');
    }

    public function odgovor()
    {
        return $this->hasMany('App\Models\AnketaOdgovor', 'anketa_id');
    }

    public function ucesce($id_ankete)
    {
        $sql = "SELECT ispitanik_id,ispitanici.naziv AS ispitanik,COUNT(*) AS broj_odgovora 
                FROM ankete_odgovori AS odgovori
                JOIN ankete_pitanja AS pitanja ON odgovori.pitanje_id = pitanja.id
                JOIN {$this->table()} ON pitanja.anketa_id = ankete.id
                JOIN ankete_ispitanici AS ispitanici ON odgovori.ispitanik_id = ispitanici.id
                WHERE pitanja.anketa_id = '{$id_ankete}'
                GROUP BY ispitanik_id;";
        return $this->fetch($sql);
    }

    public function nucesce($id_ankete)
    {
        $sql = "SELECT ispitanik_id,ispitanici.naziv AS ispitanik,COUNT(*) AS broj_odgovora 
                FROM ankete_odgovori AS odgovori
                JOIN ankete_pitanja AS pitanja ON odgovori.pitanje_id = pitanja.id
                JOIN {$this->table()} ON pitanja.anketa_id = ankete.id
                JOIN ankete_ispitanici AS ispitanici ON odgovori.ispitanik_id = ispitanici.id
                WHERE pitanja.anketa_id = '{$id_ankete}'
                GROUP BY ispitanik_id;";
        $ucestvuju = $this->fetch($sql);
        if ($ucestvuju) {
            $niznotin = implode(", ", array_column($ucestvuju, 'ispitanik_id'));
            $sqlb = "SELECT * FROM ankete_ispitanici
            WHERE id NOT IN({$niznotin})";
            return $this->fetch($sqlb);
        }
        return NULL;
    }

    public function brojpitanja($id_ankete)
    {
        $sql = "SELECT COUNT(*) AS broj FROM ankete_pitanja
                WHERE anketa_id = '{$id_ankete}'";
        return $this->fetch($sql)[0]->broj;
    }

    public function aktuelne()
    {
        $sql = "SELECT * FROM {$this->table()}
                WHERE CURDATE() BETWEEN DATE(pocetak) AND DATE(kraj)";
        return $this->fetch($sql);
    }
}
