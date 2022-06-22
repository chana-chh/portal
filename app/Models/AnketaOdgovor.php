<?php

namespace App\Models;

use App\Classes\Model;

class AnketaOdgovor extends Model
{
    protected $table = 'ankete_odgovori';

    public function tip()
    {
        return $this->belongsTo('App\Models\AnketaTipOdgovora', 'odgovor_id');
    }

    public function ispitanik()
    {
        return $this->belongsTo('App\Models\AnketaIspitanik', 'ispitanik_id');
    }

    public function pitanje()
    {
        return $this->belongsTo('App\Models\AnketaPitanje', 'pitanje_id');
    }

    public function anketa()
    {
        return $this->belongsTo('App\Models\Anketa', 'anketa_id');
    }

    public function dokumenta()
    {
        return $this->hasMany('App\Models\AnketaDokument', 'odgovor_id');
    }

    public function odgovori_ispitanika($id_ispitanika, $id_ankete)
    {
        $sql = "SELECT odgovori.id AS id,pitanja.naziv AS pit,pitanja.vrsta,pitanja.redosled,odgovor_id,odgovori.anketa_id AS anketa,pitanje_id,obrazlozenje,tip.naziv 
                FROM {$this->table()} AS odgovori
                JOIN ankete_pitanja AS pitanja ON odgovori.pitanje_id = pitanja.id
                LEFT JOIN ankete_tip_odgovora AS tip ON odgovori.odgovor_id = tip.id
                WHERE odgovori.anketa_id = '{$id_ankete}' AND odgovori.ispitanik_id ='{$id_ispitanika}' ORDER BY pitanja.redosled ASC;";
        return $this->fetch($sql);
    }

    public function ref(int $vrednost)
    {
        // Omogućavanje brisanja zapisa bez obzira na referencijalni integritet
        $sql = "SET FOREIGN_KEY_CHECKS={$vrednost};";
        return $this->run($sql);
    }

    public function ciscenje($id_ispitanika, $id_ankete)
    {
        $sql = "DELETE FROM {$this->table()}
                WHERE anketa_id = '{$id_ankete}' AND ispitanik_id ='{$id_ispitanika}'";
        return $this->run($sql);
    }

    public function preraspodela(Array $odgovori, int $ispitanik)
    {
        $sqla = "SELECT odgovor_id FROM ankete_dokumenta GROUP BY odgovor_id";    
        $dok_id = $this->fetch($sqla);
        $dok_niz = array_column($dok_id, 'odgovor_id');
        foreach ($odgovori as $odg) {
            if (in_array($odg->id, $dok_niz)) {
                $sql = "SELECT * FROM {$this->table()} WHERE ispitanik_id = '{$ispitanik}' AND pitanje_id = '{$odg->pitanje_id}' AND anketa_id = '{$odg->anketa}';";
                if (count($this->fetch($sql)) > 0) {
                    $nov_id = $this->fetch($sql)[0]->id;
                    $sqlb = "UPDATE ankete_dokumenta SET odgovor_id = '{$nov_id}' WHERE odgovor_id = '{$odg->id}'";
                    $rez = $this->run($sqlb);
                }else{
                    $sqld = "SELECT * FROM ankete_dokumenta WHERE odgovor_id = '{$odg->id}';";
                    $dokumenta_brisanje = $this->fetch($sqld);
                    foreach ($dokumenta_brisanje as $bris) {
                        $modelDokument = new AnketaDokument();
                        $dok = $modelDokument->find($bris->id);
                        $tmp = explode('/', $dok->link);
                        $file = DIR . 'pub' . DS . 'ank' . DS . end($tmp);
                        $success = $modelDokument->deleteOne($bris->id);
                        if ($success) {
                        unlink($file);
                        }
                    }
                }
            }
        }
        return $rez;
    }
}
