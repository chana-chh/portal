<?php

namespace App\Controllers;

use App\Classes\Auth;
use App\Classes\Logger;
use App\Models\Kategorija;
use App\Models\Clanak;
use App\Models\Dokument;
use App\Models\BibliotekaObuka;


class HomeController extends Controller
{
    public function getHome($request, $response)
    {
        $model = new Clanak();
        $brojClanaka = $model->broj_objavljenih();
        $poslednjaObjavaClanak = $model->poslednja_objava();

        $modelDoc = new Dokument();
        $brojDoc = $modelDoc->broj_doc();
        $poslednjiUp = $modelDoc->poslednjiUp();

        $modelObu = new BibliotekaObuka();
        $brojBib = $modelObu->broj_doc();
        $posObu = $modelObu->poslednjiUp();

        $imenik = rand(1, 59);

        $this->render($response, 'home.twig', compact('brojClanaka', 'poslednjaObjavaClanak', 'brojDoc', 'poslednjiUp', 'brojBib', 'posObu', 'imenik'));
    }

    public function getHelp($request, $response)
    {
        $this->render($response, 'help.twig');
    }

    public function getIzrada($request, $response)
    {
        $this->render($response, 'izrada.twig');
    }
}
