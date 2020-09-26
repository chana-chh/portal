<?php

namespace App\Models;

use App\Classes\Model;

class Komentar extends Model
{
    protected $table = 'komentari';

    public function clanak()
    {
        return $this->belongsTo('App\Models\Clanak', 'id_clanka');
    }
}
