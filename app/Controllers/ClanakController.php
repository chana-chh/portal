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
}
