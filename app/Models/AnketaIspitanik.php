<?php

namespace App\Models;

use App\Classes\Model;

class AnketaIspitanik extends Model
{
    protected $table = 'ankete_ispitanici';

    public function odgovori()
    {
        return $this->hasMany('App\Models\AnketaOdgovor', 'ispitanik_id');
    }
}
