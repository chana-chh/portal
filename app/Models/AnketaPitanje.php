<?php

namespace App\Models;

use App\Classes\Model;

class AnketaPitanje extends Model
{
    protected $table = 'ankete_pitanja';

    public function odgovori()
    {
        return $this->hasMany('App\Models\AnketaOdgovor', 'pitanje_id');
    }

    public function anketa()
    {
        return $this->belongsTo('App\Models\Anketa', 'anketa_id');
    }

    public function tipoviOdgovora()
    {
        $model = new AnketaTipOdgovora();
        $in = $this->odgovori;
        if ($in) {
            $sql = "SELECT * FROM ankete_tip_odgovora WHERE id IN({$in});";
            $tip = $model->fetch($sql);
            return $tip;
        }else{
            return NULL;
        } 
    }

    public function odgovor($id_ispitanika)
    {
        $sql = "SELECT pitanja.id AS id_pitanja,pitanja.naziv AS pit,pitanja.vrsta,odgovor_id,odgovori.link AS dok,odgovori.id AS id,obrazlozenje,tip.naziv 
                FROM ankete_odgovori AS odgovori
                JOIN ankete_pitanja AS pitanja ON odgovori.pitanje_id = pitanja.id
                LEFT JOIN ankete_tip_odgovora AS tip ON odgovori.odgovor_id = tip.id
                WHERE pitanja.id = {$this->id} AND odgovori.ispitanik_id ='{$id_ispitanika}'";
        return $this->fetch($sql);
    }

    public function procenat()
    {
        $sql = "SELECT COUNT(odgovori.id) AS broj, tip.naziv AS odg, (100/14)*COUNT(odgovori.id) AS procenat FROM ankete_odgovori AS odgovori
                JOIN ankete_pitanja AS pitanja ON odgovori.pitanje_id = pitanja.id
                LEFT JOIN ankete_tip_odgovora AS tip ON odgovori.odgovor_id = tip.id
                WHERE pitanje_id = {$this->id} GROUP BY odgovor_id";
        return $this->fetch($sql);
    }

    public function dokumenta($id_odgovora)
    {
        $sql = "SELECT * FROM ankete_dokumenta WHERE odgovor_id = '{$id_odgovora}'";
        return $this->fetch($sql);
    }

    public function maximum($id_ankete)
    {
        $sql = "SELECT MAX(redosled) AS rez FROM {$this->table()} WHERE anketa_id = '{$id_ankete}';";
        return $this->fetch($sql)[0]->rez;
    }

    public function red($id_ankete){
        $sql = "SELECT redosled FROM {$this->table()} WHERE anketa_id='{$id_ankete}' ORDER BY redosled;";
        $rez = $this->fetch($sql);
        $redosled = array_column($rez, 'redosled');
        return $redosled;
    }
}
