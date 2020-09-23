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
        $model_kategorije = new Kategorija();
        $kategorije = $model_kategorije->all();

        $model_clanak = new Clanak();
        $sql = "SELECT *
                FROM   clanci
                WHERE  objavljen = 1
                ORDER  BY published_at DESC
                LIMIT  1;";
        $clanak = $model_clanak->fetch($sql);

        $model = new Clanak();
        $clanci = $model->paginate($this->page(), 'page', 
            "SELECT * FROM clanci
            WHERE  objavljen = 1
            AND arhiviran = 0 
            AND deleted_at IS NULL
            ORDER BY published_at DESC;");

        $this->render($response, 'home.twig', compact('kategorije', 'clanci'));
    }

    public function getBiraj($request, $response)
    {
        $this->render($response, 'biraj.twig');
    }
}
