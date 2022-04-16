<?php

namespace App\Controllers;

use App\Classes\Auth;
use App\Classes\Logger;
use App\Models\Korisnik;
use App\Models\BibliotekaVrsta;
use App\Models\BibliotekaDokument;
use App\Models\BibliotekaKategorija;
use App\Models\BibliotekaObuka;



class BibliotekaDokumentController extends Controller
{
    public function getLista($request, $response)
    {
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        $page = isset($query['page']) ? (int)$query['page'] : 1;

        $model = new BibliotekaDokument();
        $materijal = $model->paginate($page, 'page', "SELECT * FROM {$model->getTable()} ORDER BY created_at DESC;");

    	$model_vrste = new BibliotekaVrsta();
        $vrste = $model_vrste->all();

        $modelO = new BibliotekaObuka();
        $obuke = $modelO->all();
        $this->render($response, 'bibdokument/lista.twig', compact('materijal', 'vrste', 'obuke'));
    }

    public function postPretraga($request, $response)
    {
        $_SESSION['DATA_DOK_PRETRAGA'] = $request->getParams();

        return $response->withRedirect($this->router->pathFor('materijal.pretraga'));
    }

    public function getPretraga($request, $response)
    {
        $data = $_SESSION['DATA_DOK_PRETRAGA'];
        array_shift($data);
        array_shift($data);

        if (empty($data['naslov']) &&
            empty($data['opis']) &&
            empty($data['vrsta_id']) &&
            empty($data['obuka_id'])) {
            return $response->withRedirect($this->router->pathFor('materijal'));
        }

        $data['naslov'] = str_replace('%', '', $data['naslov']);
        $data['opis'] = str_replace('%', '', $data['opis']);

        $naslov = '%' . filter_var($data['naslov'], FILTER_SANITIZE_STRING) . '%';
        $opis = '%' . filter_var($data['opis'], FILTER_SANITIZE_STRING) . '%';

        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        $page = isset($query['page']) ? (int)$query['page'] : 1;

        $where = " WHERE ";
        $params = [];

        if (!empty($data['naslov'])) {
            if ($where !== " WHERE ") {
                $where .= " AND ";
            }
            $where .= "naslov LIKE :naslov";
            $params[':naslov'] = $naslov;
        }

        if (!empty($data['opis'])) {
            if ($where !== " WHERE ") {
                $where .= " AND ";
            }
            $where .= "opis LIKE :opis";
            $params[':opis'] = $opis;
        }

        if (!empty($data['vrsta_id'])) {
            if ($where !== " WHERE ") {
                $where .= " AND ";
            }
            $where .= "vrsta_id = :vrsta_id";
            $params[':vrsta_id'] = $data['vrsta_id'];
        }

        if (!empty($data['obuka_id'])) {
            if ($where !== " WHERE ") {
                $where .= " AND ";
            }
            $where .= "obuka_id = :obuka_id";
            $params[':obuka_id'] = $data['obuka_id'];
        }

        $model = new BibliotekaDokument();
        $sql = "SELECT * FROM {$model->getTable()}{$where} ORDER BY id DESC;";
        $materijal = $model->paginate($page, 'page', $sql, $params);

        $model_vrste = new BibliotekaVrsta();
        $vrste = $model_vrste->all();

        $modelO = new BibliotekaObuka();
        $obuke = $modelO->all();
        $this->render($response, 'bibdokument/lista.twig', compact('materijal', 'data', 'vrste', 'obuke'));
    }

    public function getDodavanje($request, $response)
    {
        $modelO = new BibliotekaObuka();
        $obuke = $modelO->all();

        $modelV = new BibliotekaVrsta();
        $vrste = $modelV->all();
        $this->render($response, 'bibdokument/dodavanje.twig', compact('obuke', 'vrste'));
    }

    public function postDodavanje($request, $response)
    {
        $data = $request->getParams();
        $dokument = $request->getUploadedFiles()['dokument'];
        unset($data['csrf_name']);
        unset($data['csrf_value']);

        if ($dokument->getError() === UPLOAD_ERR_NO_FILE) {
            $this->flash->addMessage('danger', 'Морате одабрати материјал.');
            return $response->withRedirect($this->router->pathFor('materijal'));
        }

        if ($dokument->getError() !== UPLOAD_ERR_OK) {
            $this->flash->addMessage('danger', 'Дошло је до грешке приликом отпремања материјала.');
            return $response->withRedirect($this->router->pathFor('materijal'));
        }

        $validation_rules = [
            'naslov' => [
                'required' => true,
                'minlen' => 4,
                'maxlen' => 255
            ],
            'vrsta_id' => [
                'required' => true,
            ],
            'obuka_id' => [
                'required' => true,
            ],
        ];

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Дошло је до грешке приликом додавања материјала.');
            return $response->withRedirect($this->router->pathFor('materijal'));
        } else {
            $unique = bin2hex(random_bytes(8));
            $extension = pathinfo($dokument->getClientFilename(), PATHINFO_EXTENSION);
            $velicina_tekst = human_filesize($dokument->getSize());
            $velicina_mb = $dokument->getSize() / 1024 / 1024;

            $modelDokument = new BibliotekaDokument();
            $sledeci = $modelDokument->sledeci();

            $name = "biblioteka"."_{$sledeci}_{$unique}";
            $filename = "{$name}.{$extension}";
            $veza = URL . "doc/{$filename}";
            $data['velicina_tekst'] = $velicina_tekst;
            $data['velicina_mb'] = $velicina_mb;
            $data['tip'] = $extension;
            $data['link'] = $veza;
            $data['korisnik_id'] = $this->auth->user()->id;
            $modelDokument->insert($data);
            $dokument->moveTo('doc/' . $filename);

            $dok = $modelDokument->find($modelDokument->lastId());
            $this->log($this::DODAVANJE, $dok, ['naslov', 'vrsta_id', 'obuka_id']);
            $this->flash->addMessage('success', 'Материјал је успешно сачуван.');
            return $response->withRedirect($this->router->pathFor('materijal'));
        }
    }

    public function getIzmena($request, $response, $args)
    {
        $id = (int) $args['id'];
        $model = new BibliotekaDokument();
        $materijal = $model->find($id);

        $modelV = new BibliotekaVrsta();
        $vrste = $modelV->all();

        $modelO = new BibliotekaObuka();
        $obuke = $modelO->all();
        $this->render($response, 'bibdokument/izmena.twig', compact('materijal', 'vrste', 'obuke'));
    }

    public function postIzmena($request, $response)
    {
        $data = $this->data();
        $id = $data['idIzmena'];
        unset($data['idIzmena']);

        $validation_rules = [
            'naslov' => [
                'required' => true,
                'minlen' => 4,
                'maxlen' => 255
            ],
            'vrsta_id' => [
                'required' => true,
            ],
            'obuka_id' => [
                'required' => true,
            ],
        ];

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Дошло је до грешке приликом измене података.');
            return $response->withRedirect($this->router->pathFor('materijal'));
        } else {
            $model = new BibliotekaDokument();
            $mater = $model->find($id);
            $model->update($data, $id);
            $mater1 = $model->find($id);

            $this->log($this::IZMENA, $mater1, ['naslov', 'obuka_id'], $mater);
            $this->flash->addMessage('success', 'Подаци су успешно промењени.');
            return $response->withRedirect($this->router->pathFor('materijal'));
        }
    }

    public function postBrisanje($request, $response)
    {
        $id = (int) $request->getParam('idBrisanje');
        $model = new BibliotekaDokument();
        $materijal = $model->find($id);
        $tmp = explode('/', $materijal->link);
        $file = DIR . 'pub' . DS . 'doc' . DS . end($tmp);
        $success = $model->deleteOne($id);

        if ($success) {
            unlink($file);
            $this->log($this::BRISANJE, $materijal, ['naslov', 'obuka_id'], $materijal);
            $this->flash->addMessage('success', "Докуменат је успешно обрисан.");
            return $response->withRedirect($this->router->pathFor('materijal'));
        } else {
            $this->flash->addMessage('danger', "Дошло је до грешке приликом брисања докумената!");
            return $response->withRedirect($this->router->pathFor('materijal'));
        }
    }
}
