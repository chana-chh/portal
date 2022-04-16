<?php

namespace App\Models;

use App\Classes\ModelNestedSet;

class BibliotekaKategorija extends ModelNestedSet
{
    protected $table = 'biblioteka_kategorije';

    public function korisnik()
    {
        return $this->belongsTo('App\Models\Korisnik', 'korisnik_id');
    }

    public function dokument()
    {
        return $this->hasMany('App\Models\BibliotekaObuka', 'kategorija_id');
    }

    public function brojdok($id)
    {
        $sql = "SELECT COUNT(*) AS broj_kategorije FROM biblioteka_obuke WHERE kategorija_id = $id;";
        return (int) $this->fetch($sql)[0]->broj_kategorije;
    }

    public function brojdok_sadecom($id)
    {
        $roditelji = $this->getWithChildrenNS($id);
        $roditelji_id = array_column($roditelji, 'id');
        $in = implode(',', $roditelji_id);
        $sql = "SELECT COUNT(*) AS broj_kategorije FROM biblioteka_obuke WHERE kategorija_id IN ($in);";
        return (int) $this->fetch($sql)[0]->broj_kategorije;
    }

}
