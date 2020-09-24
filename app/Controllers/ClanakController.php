<?php

namespace App\Controllers;

use App\Models\Clanak;
use App\Models\Kategorija;

class ClanakController extends Controller
{
	public function getClanke($request, $response)
    {
        $model = new Clanak();
        $clanci = $model->paginate($this->page());

        $this->render($response, 'clanci/lista.twig', compact('clanci'));
    }

    public function postClanciPretraga($request, $response)
    {
        $_SESSION['DATA_CLANCI_PRETRAGA'] = $request->getParams();
        return $response->withRedirect($this->router->pathFor('feed'));
    }

    public function getClanciPretraga($request, $response)
    {
        $data = $_SESSION['DATA_CLANCI_PRETRAGA'];
        array_shift($data);
        array_shift($data);

        if (empty($data['upit'])) {
            return $response->withRedirect($this->router->pathFor('feed'));
        }

        $data['upit'] = str_replace('%', '', $data['upit']);

        $upit = '%' . filter_var($data['upit'], FILTER_SANITIZE_STRING) . '%';

        $where = " WHERE ";
        $params = [];

        if (!empty($data['upit'])) {
            if ($where !== " WHERE ") {
                $where .= " OR ";
            }
            $where .= "naslov LIKE :upit";
            $params[':upit'] = $upit;
        }

        if (!empty($data['upit'])) {
            if ($where !== " WHERE ") {
                $where .= " OR ";
            }
            $where .= "clanak LIKE :upit";
            $params[':upit'] = $upit;
        }

        if (!empty($data['upit'])) {
            if ($where !== " WHERE ") {
                $where .= " OR ";
            }
            $where .= "rezime LIKE :upit";
            $params[':upit'] = $upit;
        }

        $where = $where === " WHERE " ? "" : $where;
        $model = new Clanak();
        $sql = "SELECT * FROM {$model->getTable()}{$where} ORDER BY published_at DESC;";
        $clanci = $model->paginate($this->page(), 'page', $sql, $params);

        $this->render($response, 'clanci/lista.twig', compact('clanci', 'data'));
    }

    public function getFeed($request, $response)
    {
        $model_kategorije = new Kategorija();
        $kategorije = $model_kategorije->all();

        $model = new Clanak();
        $clanci = $model->paginate($this->page(), 'page', 
            "SELECT * FROM clanci
            WHERE  objavljen = 1
            AND deleted_at IS NULL
            ORDER BY published_at DESC;");

        $najpopularniji = $model->najpopularniji();

        $this->render($response, 'clanci/feed.twig', compact('kategorije', 'clanci', 'najpopularniji'));
    }

    public function postPregled($request, $response)
    {
       	$data = $request->getParams();
        $id = $data['id'];
        unset($data['id']);
        unset($data['csrf_name']);
        unset($data['csrf_value']);

        $modelClanak = new Clanak();
        $clanak = $modelClanak->find($id);
        $pregledi = $clanak->pregledi + 1;
        $data['pregledi'] = $pregledi;
        $clanak->update($data, $id);
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
