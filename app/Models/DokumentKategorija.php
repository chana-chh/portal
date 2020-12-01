<?php

namespace App\Models;

use App\Classes\ModelNestedSet;

class DokumentKategorija extends ModelNestedSet
{
    protected $table = 'dokumenti_kategorije';

    public function dokument()
    {
        return $this->hasMany('App\Models\Dokument', 'kategorija_id');
    }

    public function brojdok($id)
    {
        $sql = "SELECT COUNT(*) AS broj_kategorije FROM dokumenti WHERE kategorija_id = $id";
        return (int) $this->fetch($sql)[0]->broj_kategorije;
    }
}
