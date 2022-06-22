<?php

namespace App\Models;

use App\Classes\Model;

class AnketaTipOdgovora extends Model
{
    protected $table = 'ankete_tip_odgovora';

    public function odgovori()
    {
        return $this->hasMany('App\Models\AnketaOdgovor', 'odgovor_id');
    }

    public function provera($id)
    {
         $sql = "SELECT GROUP_CONCAT(odgovori SEPARATOR ', ') AS tipovi FROM ankete_pitanja;";
         $nesredjeni = $this->fetch($sql)[0]->tipovi;
         $sredjeni = str_replace(' ', '', $nesredjeni);
         $niz = explode( ',', $sredjeni );
         $resultat = array_unique($niz);
         if (in_array($id, $resultat)) {
            return true;
         }else{
            return false;
         }
    }
}
