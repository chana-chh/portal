<?php

namespace App\Controllers;

use App\Models\Clanak;

class ClanakController extends Controller
{
	public function getClanke($request, $response)
    {
        $model = new Clanak();
        $clanci = $model->paginate($this->page());

        $this->render($response, 'clanci/lista.twig', compact('clanci'));
    }

    public function getPregled($request, $response, $args)
    {
       	$id = (int) $args['id'];
        $modelClanak = new Clanak();
        $clanak = $modelClanak->find($id);
        $this->render($response, 'clanci/pregled.twig', compact('clanak'));
    }

    public function getLista($request, $response, $args)
    {
       	$id_kategorije = (int) $args['id'];
        $modelClanak = new Clanak();
        $clanci = $modelClanak->clanci_kategorija($id_kategorije);
        $this->render($response, 'clanci/lista.twig', compact('clanci'));
    }
}
