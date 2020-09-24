<?php

namespace App\Controllers;

use App\Classes\Auth;
use App\Classes\Logger;
use App\Models\Kategorija;
use App\Models\Clanak;


class HomeController extends Controller
{
    public function getHome($request, $response)
    {
        $model = new Clanak();
        $brojClanaka = $model->broj_objavljenih();
        $poslednjaObjava = $model->poslednja_objava();

        $this->render($response, 'home.twig', compact('brojClanaka', 'poslednjaObjava'));
    }

    public function getBiraj($request, $response)
    {
        $model = new Clanak();
        $brojClanaka = $model->broj_objavljenih();
        $poslednjaObjava = $model->poslednja_objava();

        $this->render($response, 'biraj.twig', compact('brojClanaka', 'poslednjaObjava'));
    }
}
