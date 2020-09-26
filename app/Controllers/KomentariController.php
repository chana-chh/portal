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

    public function postClanciPretraga($request, $response)
    {
        $_SESSION['DATA_CLANCI_PRETRAGA'] = $request->getParams();
        return $response->withRedirect($this->router->pathFor('clanci.pretraga'));
    }

    public function getClanciPretraga($request, $response)
    {
        $data = $_SESSION['DATA_CLANCI_PRETRAGA'];
        array_shift($data);
        array_shift($data);

        if (empty($data['upit'])) {
            return $response->withRedirect($this->router->pathFor('clanci.lista'));
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
            $where .= "clanak LIKE :clanak";
            $params[':clanak'] = $upit;
        }

        if (!empty($data['upit'])) {
            if ($where !== " WHERE ") {
                $where .= " OR ";
            }
            $where .= "rezime LIKE :rezime";
            $params[':rezime'] = $upit;
        }

        $where = $where === " WHERE " ? "" : $where;
        $model = new Clanak();
        $sql = "SELECT * FROM {$model->getTable()}{$where} ORDER BY published_at DESC;";
        $clanci = $model->paginate($page, 'page', $sql, $params);

        $model_kategorije = new Kategorija();
        $kategorije = $model_kategorije->all();

        $najpopularniji = $model->najpopularniji();

        $this->render($response, 'clanci/lista.twig', compact('kategorije', 'clanci', 'najpopularniji', 'data'));
    }

    public function getPregled($request, $response, $args)
    {
       	$id = (int)$args['id'];
        $modelClanak = new Clanak();
        $clanak = $modelClanak->find($id);
        $pregledi = $clanak->pregledi + 1;
        $data['pregledi'] = $pregledi;
        $clanak->update($data, $id);

        $sql = "SELECT
    			COUNT(id) as broj, MONTHNAME(t.published_at) as mesec, YEAR(t.published_at) as godina, MONTH (t.published_at) as mm
				FROM clanci t 
				WHERE  objavljen = 1
            	AND deleted_at IS NULL
				GROUP BY EXTRACT(YEAR_MONTH FROM t.published_at) DESC;";
        $arhiva =$modelClanak->fetch($sql);
        $najpopularniji = $modelClanak->najpopularniji();
        $model_kategorije = new Kategorija();
        $kategorije = $model_kategorije->all();

        $this->render($response, 'clanci/pregled.twig', compact('clanak', 'kategorije', 'najpopularniji', 'arhiva'));
    }

    public function getTabelaKategorije($request, $response, $args)
    {
       	$id_kategorije = (int) $args['id'];
        $modelClanak = new Clanak();
        $clanci = $modelClanak->clanci_kategorija($id_kategorije);
        $this->render($response, 'clanci/tabela.twig', compact('clanci'));
    }

    public function getArhiva($request, $response, $args)
    {
       	$mesec = (int) $args['mm'];
       	$godina = (int) $args['yy'];
        $model = new Clanak();
        $sql = "SELECT *
				FROM clanci
				WHERE month(published_at)={$mesec}
				AND year(published_at)={$godina}
            	AND deleted_at IS NULL
				ORDER BY published_at DESC;";
        $clanci =$model->fetch($sql);
        $this->render($response, 'clanci/tabela.twig', compact('clanci'));
    }

    public function getKomentar($request, $response, $args)
    {
       	$id = (int)$args['id'];
        $modelClanak = new Clanak();
        $clanak = $modelClanak->find($id);
        $this->render($response, 'clanci/komentar.twig', compact('clanak'));
    }

    public function postKomentar($request, $response)
    {
       	$data = $request->getParams();
        $id = $data['id'];
        unset($data['id']);
        unset($data['csrf_name']);
        unset($data['csrf_value']);

        $ip_prijave = $request->getServerParam('REMOTE_ADDR');
        $racunar_prijave = gethostbyaddr($ip_prijave);

        $validation_rules = [
            'naslov' => [
                'minlen' => 5,
                'maxlen' => 100
            ],
            'korisnik' => [
                'required' => true,
                'maxlen' => 100
            ],
            'komentar' => [
                'required' => true,
                'minlen' => 10,
                'maxlen' => 1000
            ]
        ];

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Дошло је до грешке приликом додавања коментара.');
            return $response->withRedirect($this->router->pathFor('clanci.pregled', ['id' => $id]));
        } else {
            $data['korisnik_ip'] = $ip_prijave.','.$racunar_prijave;
            $data['id_clanka'] = $id;
            $model = new Komentar();
            $model->insert($data);
            $this->flash->addMessage('success', 'Коментар је успешно додат.');
            return $response->withRedirect($this->router->pathFor('clanci.pregled', ['id' => $id]));
        }
    }
}
