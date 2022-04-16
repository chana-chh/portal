<?php

namespace App\Controllers;

use App\Models\Anketa;
use App\Models\AnketaTip;
use App\Models\AnketaPitanje;
use App\Models\AnketaTipOdgovora;

class AnketaController extends Controller
{

    public function getLista($request, $response)
    {
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        $page = isset($query['page']) ? (int)$query['page'] : 1;

        $model = new Anketa();
        $ankete = $model->paginate($this->page(), 'page', 
            "SELECT * FROM {$model->getTable()}
            ORDER BY kraj DESC;");

        $tip = new AnketaTip();
        $tipovi = $tip->all();
        
        $this->render($response, 'ankete/tabela.twig', compact('ankete', 'tipovi'));
    }

    public function postPretraga($request, $response)
    {
        $_SESSION['DATA_ANKETE_PRETRAGA'] = $request->getParams();
        return $response->withRedirect($this->router->pathFor('ankete.pretraga'));
    }

    public function getPretraga($request, $response)
    {
        $data = $_SESSION['DATA_ANKETE_PRETRAGA'];
        array_shift($data);
        array_shift($data);

       if (empty($data['naziv']) &&
            empty($data['tip_id']) &&
            empty($data['datum_1']) &&
            empty($data['datum_2']) &&
            empty($data['datum_3']) &&
            empty($data['datum_4']) &&
            empty($data['kraj'])) {
            return $response->withRedirect($this->router->pathFor('ankete'));
        }

        $data['naziv'] = str_replace('%', '', $data['naziv']);

        $naziv = '%' . filter_var($data['naziv'], FILTER_SANITIZE_STRING) . '%';

        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        $page = isset($query['page']) ? (int)$query['page'] : 1;

        $where = " WHERE ";
        $params = [];

        if (!empty($data['naziv'])) {
            $where .= "naziv LIKE :naziv";
            $params[':naziv'] = $naziv;
        }

         if (!empty($data['tip_id'])) {
            if ($where !== " WHERE ") {
                $where .= " AND ";
            }
            $where .= "tip_id = :tip_id";
            $params[':tip_id'] = $data['tip_id'];
        }

         if (!empty($data['datum_1']) && empty($data['datum_2'])) {
            if ($where !== " WHERE ") {
                $where .= " AND ";
            }
            $where .= "DATE(pocetak) = :datum_1";
            $params[':datum_1'] = $data['datum_1'];
        }

        if (!empty($data['datum_1']) && !empty($data['datum_2'])) {
            if ($where !== " WHERE ") {
                $where .= " AND ";
            }
            $where .= "DATE(pocetak) >= :datum_1 AND DATE(pocetak) <= :datum_2 ";
            $params[':datum_1'] = $data['datum_1'];
            $params[':datum_2'] = $data['datum_2'];
        }

         if (!empty($data['datum_3']) && empty($data['datum_4'])) {
            if ($where !== " WHERE ") {
                $where .= " AND ";
            }
            $where .= "DATE(kraj) = :datum_3";
            $params[':datum_3'] = $data['datum_3'];
        }

        if (!empty($data['datum_3']) && !empty($data['datum_4'])) {
            if ($where !== " WHERE ") {
                $where .= " AND ";
            }
            $where .= "DATE(kraj) >= :datum_3 AND DATE(kraj) <= :datum_4 ";
            $params[':datum_3'] = $data['datum_3'];
            $params[':datum_4'] = $data['datum_4'];
        }

        $where = $where === " WHERE " ? "" : $where;

        $model = new Anketa();
        $sql = "SELECT * FROM {$model->getTable()}{$where} ORDER BY kraj DESC;";
        $ankete = $model->paginate($page, 'page', $sql, $params);

        $tip = new AnketaTip();
        $tipovi = $tip->all();

        $this->render($response, 'ankete/tabela.twig', compact('ankete', 'data', 'tipovi'));
    }

    public function getPregled($request, $response, $args)
    {
        $id = (int)$args['id'];
        $modelAnketa = new Anketa();
        $anketa = $modelAnketa->find($id);

        $pitanja = $anketa->pitanja();
        array_multisort( array_column($pitanja, "redosled"), SORT_ASC, $pitanja );

        $modelPitanja = new AnketaPitanje();
        $vrste = $modelPitanja->enumOrSetList('vrsta');

        $sledeci = 1 + $modelPitanja->maximum($id);

        $modelTipOdgovora = new AnketaTipOdgovora();
        $tipovi = $modelTipOdgovora->all();
        $this->render($response, 'ankete/pregled.twig', compact('anketa', 'vrste', 'tipovi', 'sledeci', 'pitanja'));
    }
    
    public function getDodavanje($request, $response)
    {

        $model = new AnketaTip();
        $tipovi = $model->all();

        $this->render($response, 'ankete/dodavanje.twig', compact('tipovi'));
    }

    public function postDodavanje($request, $response)
    {
        $data = $this->data();

        $pocetak = strtotime($data['pocetak']);
        $kraj = strtotime($data['kraj']);

        if ($kraj < $pocetak) {
            $this->flash->addMessage('danger', 'Датум почетка анкете не сме бити пре њеног краја!');
            return $response->withRedirect($this->router->pathFor('ankete'));
        }

        $validation_rules = [
            'naziv' => [
                'required' => true,
                'maxlen' => 255,
                'unique' => 'ankete.naziv'
            ],
            'tip_id' => [
                'required' => true,
            ],
            'pocetak' => [
                'required' => true,
            ],
            'kraj' => [
                'required' => true,
            ],
            'korisnik_id' => [
                'required' => true,
            ]
        ];

        $data['korisnik_id'] = $this->auth->user()->id;

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Дошло је до грешке приликом додавања АНКЕТЕ.');
            return $response->withRedirect($this->router->pathFor('ankete'));
        } else {
            $this->flash->addMessage('success', 'Нова АНКЕТА је успешно додата.');
            $model = new Anketa();

            $model->insert($data);
            $vrsta = $model->find($model->lastId());
            $this->log($this::DODAVANJE, $vrsta, 'naziv');
            return $response->withRedirect($this->router->pathFor('ankete'));
        }
    }

    public function getIzmena($request, $response, $args)
    {
        $id = (int) $args['id'];
        $model = new Anketa();
        $anketa = $model->find($id);

        $tip = new AnketaTip();
        $tipovi = $tip->all();

        $this->render($response, 'ankete/izmena.twig', compact('anketa', 'tipovi'));
    }

    public function postIzmena($request, $response)
    {
        $data = $this->data();
        $id = $data['idIzmena'];
        unset($data['idIzmena']);

        $pocetak = strtotime($data['pocetak']);
        $kraj = strtotime($data['kraj']);

        if ($kraj < $pocetak) {
            $this->flash->addMessage('danger', 'Датум почетка анкете не сме бити пре њеног краја!');
            return $response->withRedirect($this->router->pathFor('ankete'));
        }

        $validation_rules = [
            'naziv' => [
                'required' => true,
                'maxlen' => 255,
                'unique' => 'ankete.naziv#id:'.$id
            ],
            'tip_id' => [
                'required' => true,
            ],
            'pocetak' => [
                'required' => true,
            ],
            'kraj' => [
                'required' => true,
            ],
            'korisnik_id' => [
                'required' => true,
            ]
        ];

        $data['korisnik_id'] = $this->auth->user()->id;

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Дошло је до грешке приликом измене података.');
            return $response->withRedirect($this->router->pathFor('ankete.izmena.get', ['id' => $id]));
        } else {
            $model = new Anketa();
            $anketa = $model->find($id);
            $model->update($data, $id);
            $anketai = $model->find($id);

            $this->log($this::IZMENA, $anketai, ['naziv', 'tip_id', 'pocetak', 'kraj'], $anketa);
            $this->flash->addMessage('success', 'Подаци су успешно измењени.');
            return $response->withRedirect($this->router->pathFor('ankete'));
        }
    }

    public function postBrisanje($request, $response)
    {
        $id = (int)$request->getParam('idBrisanje');
        $model = new Anketa();
        $anketa = $model->find($id);
        if (!empty($anketa->pitanja())) {
            $this->flash->addMessage('danger', "Ова анкета/истраживање има дефинисана питања и могуће одговоре анкетираних. Обришите сва питања везана за анкету да бисте је обрисали");
            return $response->withRedirect($this->router->pathFor('ankete'));
        }else{
            $success = $model->deleteOne($id);
        }
        
        if ($success) {
            $this->flash->addMessage('success', "АНКЕТА је успешно обрисана.");
            $this->log($this::BRISANJE, $anketa, ['naziv', 'tip_id', 'pocetak', 'kraj'], $anketa);
            return $response->withRedirect($this->router->pathFor('ankete'));
        } else {
            $this->flash->addMessage('danger', "Дошло је до грешке приликом брисања АНКЕТЕ.");
            return $response->withRedirect($this->router->pathFor('ankete'));
        }
    }
}
