<?php

namespace App\Controllers;

use App\Models\BibliotekaDokument;
use App\Models\BibliotekaObuka;
use App\Models\BibliotekaProgram;
use App\Models\BibliotekaKategorija;
use App\Models\BibliotekaVrsta;
use App\Classes\Logger;

class BibliotekaObukaController extends Controller
{
    public function getLista($request, $response, $args)
    {
        $where = " WHERE ";
        $params = [];

        $kategorija = null;

        $model_kategorije = new BibliotekaKategorija();
        $kategorije = $model_kategorije->getListNS();
        $nerasporedjeni = $kategorije[0];
        unset($kategorije[0]);
        array_push($kategorije, $nerasporedjeni);

        $radni = $model_kategorije->find(1);

        if (!empty($args)) {
            if (isset($args['id_kat'])) {
                if ($where !== " WHERE ") {
                    $where .= " AND ";
                }
                $where .= "kategorija_id = :kategorija_id";
                $params[':kategorija_id'] = (int) $args['id_kat'];

                $modelKat = new BibliotekaKategorija();
                $kategorija = $modelKat->find((int) $args['id_kat']);

            };
        }

        $where = $where === " WHERE " ? "" : $where;
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        $page = isset($query['page']) ? (int)$query['page'] : 1;

        $model = new BibliotekaObuka();
        $sql = "SELECT * FROM {$model->getTable()}{$where} ORDER BY created_at DESC;";
        $obuke = $model->paginate($page, 'page', $sql, $params);

        $this->render($response, 'bibobuka/lista.twig', compact('kategorije', 'obuke', 'kategorija', 'radni'));
    }

    public function postDokumentiPretraga($request, $response)
    {
        $_SESSION['DATA_OBUKA_PRETRAGA'] = $request->getParams();
        return $response->withRedirect($this->router->pathFor('obuka.pretraga'));
    }

    public function getDokumentiPretraga($request, $response)
    {
        $data = $_SESSION['DATA_OBUKA_PRETRAGA'];
        array_shift($data);
        array_shift($data);

        if (empty($data['upit'])) {
            return $response->withRedirect($this->router->pathFor('obuka.lista'));
        }

        $data['upit'] = str_replace('%', '', $data['upit']);

        $upit = '%' . filter_var($data['upit'], FILTER_SANITIZE_STRING) . '%';

        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        $page = isset($query['page']) ? (int)$query['page'] : 1;

        $where = " WHERE ";
        $params = [];

        $model_kategorije = new BibliotekaKategorija();
        $kategorije = $model_kategorije->getListNS();
        $nerasporedjeni = $kategorije[0];
        unset($kategorije[0]);
        array_push($kategorije, $nerasporedjeni);
        
        $radni = $model_kategorije->find(1);

        if (!empty($data['upit'])) {
            $where .= "naziv LIKE :naziv";
            $params[':naziv'] = $upit;
        }

        if (!empty($data['upit'])) {
            if ($where !== " WHERE ") {
                $where .= " OR ";
            }
            $where .= "organizator LIKE :organizator";
            $params[':organizator'] = $upit;
        }

        if (!empty($data['upit'])) {
            if ($where !== " WHERE ") {
                $where .= " OR ";
            }
            $where .= "finansijer LIKE :finansijer";
            $params[':finansijer'] = $upit;
        }

        if (!empty($data['upit'])) {
            if ($where !== " WHERE ") {
                $where .= " OR ";
            }
            $where .= "mesto LIKE :mesto";
            $params[':mesto'] = $upit;
        }

        if (!empty($data['id_kat'])) {
            if ($where !== " WHERE ") {
                $where .= " AND ";
            }
            $where .= "kategorija_id = :id_kat";
            $params[':id_kat'] = $data['id_kat'];

            $modelKat = new BibliotekaKategorija();
            $kategorija = $modelKat->find((int) $data['id_kat']);
        } else {
            $kategorija = null;
        }


        $where = $where === " WHERE " ? "" : $where;
        $model = new BibliotekaObuka();
        $sql = "SELECT * FROM {$model->getTable()}{$where} ORDER BY created_at DESC;";
        $obuke = $model->paginate($page, 'page', $sql, $params);

        $this->render($response, 'bibobuka/lista.twig', compact('kategorije', 'obuke', 'data', 'kategorija', 'radni'));
    }

    public function getDokumentDodavanje($request, $response)
    {
        $modelK = new BibliotekaKategorija();
        $kategorije = $modelK->getFlatListNS();

        $sql = "SELECT MAX(level) AS maks FROM {$modelK->getTable()};";
        $nivo_query = $modelK->fetch($sql);
        $nivo = $nivo_query[0]->maks;

        $modelp = new BibliotekaProgram();
        $programi = $modelp->all();

        $this->render($response, 'bibobuka/dodavanje.twig', compact('programi', 'kategorije', 'nivo'));
    }

    public function postDokumentDodavanje($request, $response)
    {
        $data = $request->getParams();
        unset($data['csrf_name']);
        unset($data['csrf_value']);


        $validation_rules = [
            'naziv' => [
                'required' => true,
                'minlen' => 4,
                'maxlen' => 255
            ],
            'program_id' => [
                'required' => true,
            ],
            'kategorija_id' => [
                'required' => true,
            ],
        ];
        $data['korisnik_id'] = $this->auth->user()->id;
        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Дошло је до грешке приликом додавања обуке.');
            return $response->withRedirect($this->router->pathFor('obuka.lista'));
        } else {
            $modelDokument = new BibliotekaObuka();
            $modelDokument->insert($data);
            $dok = $modelDokument->find($modelDokument->lastId());
            $this->log($this::DODAVANJE, $dok, ['naziv', 'program_id', 'kategorija_id']);
            $this->flash->addMessage('success', 'Обука је успешно сачувана.');
            return $response->withRedirect($this->router->pathFor('obuka.lista'));
        }
    }

    public function postDokumentiBrisanje($request, $response)
    {
        $id = (int)$request->getParam('idBrisanje');
        $modelDokument = new BibliotekaObuka();
        $dok = $modelDokument->find($id);
        $success = $modelDokument->deleteOne($id);
        if ($success) {
            $this->log($this::BRISANJE, $dok, 'naziv', $dok);
            $this->flash->addMessage('success', "Обука је успешно обрисана.");
            return $response->withRedirect($this->router->pathFor('obuka.lista'));
        } else {
            $this->flash->addMessage('danger', "Дошло је до грешке приликом брисања обуке.");
            return $response->withRedirect($this->router->pathFor('obuka.lista'));
        }
    }

    public function getDokumentiIzmena($request, $response, $args)
    {

        $id = (int) $args['id'];
        $modelDokument = new BibliotekaObuka();
        $dokument = $modelDokument->find($id);

        $modelK = new BibliotekaKategorija();
        $kategorije = $modelK->getFlatListNS();

        $sql = "SELECT MAX(level) AS maks FROM {$modelK->getTable()};";
        $nivo_query = $modelK->fetch($sql);
        $nivo = $nivo_query[0]->maks;

        $modelp = new BibliotekaProgram();
        $programi = $modelp->all();
        
        $this->render($response, 'bibobuka/izmena.twig', compact('dokument', 'kategorije', 'programi', 'nivo'));
    }

    public function postDokumentiIzmena($request, $response)
    {
        $data = $this->data();
        $id = $data['idIzmena'];
        unset($data['idIzmena']);

        $validation_rules = [
            'naziv' => [
                'required' => true,
                'minlen' => 4,
                'maxlen' => 255
            ],
            'program_id' => [
                'required' => true,
            ],
            'kategorija_id' => [
                'required' => true,
            ],
        ];
        $data['korisnik_id'] = $this->auth->user()->id;

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Дошло је до грешке приликом измене података обуке.');
            return $response->withRedirect($this->router->pathFor('obuka.lista'));
        } else {
            $this->flash->addMessage('success', 'Подаци о обуци су успешно промењени.');
            $modelDokument = new BibliotekaObuka();
            $stari = $modelDokument->find($id);
            $modelDokument->update($data, $id);
            $dokument = $modelDokument->find($id);
            $this->log($this::IZMENA, $dokument, 'naziv', $stari);
            return $response->withRedirect($this->router->pathFor('obuka.lista'));
        }
    }

    public function getMaterijal($request, $response, $args)
    {
        $id = (int) $args['id'];
        $modelObuke = new BibliotekaObuka();
        $obuka = $modelObuke->find($id);

        $materijal = $obuka->mat();

        $this->render($response, 'bibobuka/materijal.twig', compact('materijal', 'obuka'));
    }
}
