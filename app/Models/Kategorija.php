<?php

namespace App\Models;

use App\Classes\Model;

class Kategorija extends Model
{
    protected $table = 'kategorije';

    public function clanak()
    {
        return $this->belongsToMany('App\Models\Clanak', 'id_clanka');
    }
}
