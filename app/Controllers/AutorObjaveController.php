<?php

namespace App\Controllers;

use App\Classes\Auth;
use App\Classes\Logger;
use App\Models\Kategorija;
use App\Models\Clanak;

class AutorObjaveController extends Controller
{
    public function getObjaveLista($request, $response)
    {
        $model = new Clanak();
        $user_id = $this->auth->user()->id;
        $sql =  "SELECT * FROM clanci WHERE korisnik_id = {$user_id}";
        $clanci = $model->paginate($this->page(), 'page', $sql);
        $this->render($response, 'autor/clanci/lista.twig', compact('clanci'));
    }

    public function getObjaveDodavanje($request, $response, $args)
    {
        $kategorije = $this->auth->user()->dozvoljeneKategorije();
        $this->render($response, 'autor/clanci/dodavanje.twig', compact('kategorije'));
    }

    public function postObjaveDodavanje($request, $response)
    {
        $data = $this->data();
        // $validation_rules = [
        //     'termin_id' => ['required' => true,],
        //     'prezime' => ['required' => true,],
        //     'ime' => ['required' => true,],
        //     'broj_mesta' => ['required' => true,],
        //     'broj_stolova' => ['required' => true,],
        //     'broj_mesta_po_stolu' => ['required' => true,],
        //     'fizicko_pravno' => ['required' => true,]
        // ];

        // $model_ugovor = new Ugovor();
        // if (trim($data['broj_ugovora']) != "") {
        //     $sql = "SELECT COUNT(*) AS broj FROM ugovori WHERE broj_ugovora = :br;";
        //     $params = [':br' => trim($data['broj_ugovora'])];
        //     $br = (int) $model_ugovor->fetch($sql, $params)[0]->broj;
        //     if ($br > 0) {
        //         $this->validator->addError('broj_ugovora', 'U bazi već postoji [Broj ugovora] sa istom vrednošću');
        //     }
        // }

        // $this->validator->validate($data, $validation_rules);

        // if ($this->validator->hasErrors()) {
        //     $this->flash->addMessage('danger', 'Došlo je do greške prilikom dodavanja ugovora.');
        //     return $response->withRedirect($this->router->pathFor('termin.dodavanje.ugovor', ['termin_id' => (int) $data['termin_id']]));
        // } else {
        //     $data['korisnik_id'] = $this->auth->user()->id;
        //     $model_ugovor->insert($data);
        //     $ugovor = $model_ugovor->find($model_ugovor->lastId());
        //     $this->log($this::DODAVANJE, $ugovor, 'broj_ugovora');

        //     $model_termin = new Termin();
        //     $termin = $model_termin->find($ugovor->termin_id);
        //     if ($termin->zakljucavanje()) {
        //         $model_termin->update(['zauzet' => 1], $termin->id);
        //     } else {
        //         $model_termin->update(['zauzet' => 0], $termin->id);
        //     }

        //     $this->flash->addMessage('success', 'Novi ugovor je uspešno dodat.');
        //     return $response->withRedirect($this->router->pathFor('ugovor.dopuna.get', ['id' => (int) $ugovor->id]));
        // }
    }
}
