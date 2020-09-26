<?php

namespace App\Controllers;

use App\Models\Clanak;
use App\Models\Komentar;

class KomentariController extends Controller
{

    public function getLista($request, $response)
    {
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        $page = isset($query['page']) ? (int)$query['page'] : 1;

        $model = new Komentar();
        $komentari = $model->paginate($this->page(), 'page', 
            "SELECT * FROM {$model->getTable()}
            ORDER BY created_at DESC;");
        
        $this->render($response, 'komentari/tabela.twig', compact('komentari'));
    }

    public function postKomentariPretraga($request, $response)
    {
        $_SESSION['DATA_KOMENTARI_PRETRAGA'] = $request->getParams();
        return $response->withRedirect($this->router->pathFor('komentari.pretraga'));
    }

    public function getKomentariPretraga($request, $response)
    {
        $data = $_SESSION['DATA_KOMENTARI_PRETRAGA'];
        array_shift($data);
        array_shift($data);

        if (empty($data['upit'])) {
            return $response->withRedirect($this->router->pathFor('komentari'));
        }

        $data['upit'] = str_replace('%', '', $data['upit']);

        $upit = '%' . filter_var($data['upit'], FILTER_SANITIZE_STRING) . '%';

        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        $page = isset($query['page']) ? (int)$query['page'] : 1;

        $where = " WHERE ";
        $params = [];

        if (!empty($data['upit'])) {
            $where .= "naslov LIKE :naslov";
            $params[':naslov'] = $upit;
        }

        if (!empty($data['upit'])) {
            if ($where !== " WHERE ") {
                $where .= " OR ";
            }
            $where .= "komentar LIKE :komentar";
            $params[':komentar'] = $upit;
        }

        if (!empty($data['upit'])) {
            if ($where !== " WHERE ") {
                $where .= " OR ";
            }
            $where .= "korisnik LIKE :korisnik";
            $params[':korisnik'] = $upit;
        }

        $where = $where === " WHERE " ? "" : $where;
        $model = new Komentar();
        $sql = "SELECT * FROM {$model->getTable()}{$where} ORDER BY created_at DESC;";
        $komentari = $model->paginate($page, 'page', $sql, $params);

        $this->render($response, 'komentari/tabela.twig', compact('komentari'));
    }
}
