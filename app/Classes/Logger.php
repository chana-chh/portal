<?php

namespace App\Classes;

use App\Models\Log;

class Logger
{
    private $korisnik;
    private $model;

    public function __construct($korisnik)
    {
        $this->korisnik = $korisnik;
        $this->model = new Log();
    }

    public function log($tip, $model, $polje, $model_stari = null)
    {
        $tekst = '';
        if (is_array($polje)) {
            foreach ($polje as $p) {
                $tekst .= "{$p}: {$model->$p}, ";
            }
        } else {
            $tekst = "{$polje}: {$model->$polje}";
        }

        $stari = '';

        if ($tip == 'brisanje') {
            $stari = serialize(get_object_vars($model));
        }

        if ($tip == 'izmena') {
            $s = get_object_vars($model_stari);
            $n = get_object_vars($model);
            $dif = array_diff($n, $s);
            $rez = [];

            foreach ($dif as $key => $value) {
                $rez[$key] = [
                    'stara_vrednost' => $s[$key],
                    'nova_vrednost' => $n[$key],
                ];
            }

            $stari = serialize($rez);
        }

        // ako je model ugovor dodati i termin _id
        $data = [
            'opis' => "{$model->id}, {$model->table()} - {$tekst}",
            'tip' => $tip,
            'izmene' => $stari,
            'korisnik_id' => $this->korisnik->id,
        ];

        $this->model->insert($data);
    }
}
