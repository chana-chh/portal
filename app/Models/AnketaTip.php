<?php

namespace App\Models;

use App\Classes\Model;

class AnketaTip extends Model
{
    protected $table = 'ankete_tip';

    public function anketa()
    {
        return $this->hasMany('App\Models\Anketa', 'tip_id');
    }
}
