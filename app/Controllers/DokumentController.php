<?php

namespace App\Controllers;

use App\Models\Dokument;
use App\Models\DokumentKategorija;
use App\Models\DokumentVrsta;
use App\Classes\Logger;

class DokumentController extends Controller
{
    public function getLista($request, $response, $args)
    {
        $where = " WHERE ";
        $params = [];

        $kategorija = null;
        $vrsta = null;

        $model_kategorije = new DokumentKategorija();
        $kategorije = $model_kategorije->getListNS();
        $radni = $model_kategorije->find(1);

        if (!empty($args)) {
            if (isset($args['id_kat'])) {
                if ($where !== " WHERE ") {
                    $where .= " AND ";
                }
                $where .= "kategorija_id = :kategorija_id";
                $params[':kategorija_id'] = (int) $args['id_kat'];

                $modelKat = new DokumentKategorija();
                $kategorija = $modelKat->find((int) $args['id_kat']);

                $modelVrs = new DokumentVrsta();
                $kategorije = $modelVrs->all();
            };

            if (isset($args['id_vrs'])) {
                if ($where !== " WHERE ") {
                    $where .= " AND ";
                }
                $where .= "vrsta_id = :vrsta_id";
                $params[':vrsta_id'] = (int) $args['id_vrs'];

                $modelVrs = new DokumentVrsta();
                $vrsta = $modelVrs->find((int) $args['id_vrs']);
            };
        }

        $where = $where === " WHERE " ? "" : $where;

        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        $page = isset($query['page']) ? (int)$query['page'] : 1;

        $model = new Dokument();
        $sql = "SELECT * FROM {$model->getTable()}{$where} ORDER BY created_at DESC;";

        $dokumenti = $model->paginate($page, 'page', $sql, $params);

        $sql = "SELECT
                COUNT(id) as broj, MONTHNAME(t.created_at) as mesec, YEAR(t.created_at) as godina, MONTH (t.created_at) as mm
                FROM dokumenti t
                GROUP BY EXTRACT(YEAR_MONTH FROM t.created_at) DESC;";
        $arhiva =$model->fetch($sql);

        $this->render($response, 'dokumenti/lista.twig', compact('kategorije', 'dokumenti', 'arhiva', 'kategorija', 'vrsta', 'radni'));
    }

    public function postDokumentiPretraga($request, $response)
    {
        $_SESSION['DATA_DOKUMENTI_PRETRAGA'] = $request->getParams();
        return $response->withRedirect($this->router->pathFor('dokumenti.pretraga'));
    }

    public function getDokumentiPretraga($request, $response)
    {
        $data = $_SESSION['DATA_DOKUMENTI_PRETRAGA'];
        array_shift($data);
        array_shift($data);

        if (empty($data['upit'])) {
            return $response->withRedirect($this->router->pathFor('dokumenti.lista'));
        }

        $data['upit'] = str_replace('%', '', $data['upit']);

        $upit = '%' . filter_var($data['upit'], FILTER_SANITIZE_STRING) . '%';

        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        $page = isset($query['page']) ? (int)$query['page'] : 1;

        $where = " WHERE ";
        $params = [];

        $model_kategorije = new DokumentKategorija();
        $kategorije = $model_kategorije->getListNS();
        $radni = $model_kategorije->find(1);

        if (!empty($data['upit'])) {
            $where .= "naslov LIKE :naslov";
            $params[':naslov'] = $upit;
        }

        if (!empty($data['upit'])) {
            if ($where !== " WHERE ") {
                $where .= " OR ";
            }
            $where .= "opis LIKE :opis";
            $params[':opis'] = $upit;
        }

        if (!empty($data['id_kat'])) {
            if ($where !== " WHERE ") {
                $where .= " AND ";
            }
            $where .= "kategorija_id = :id_kat";
            $params[':id_kat'] = $data['id_kat'];

            $modelKat = new DokumentKategorija();
            $kategorija = $modelKat->find((int) $data['id_kat']);

            $modelVrs = new DokumentVrsta();
            $kategorije = $modelVrs->all();
        } else {
            $kategorija = null;
        }

        if (!empty($data['id_vrs'])) {
            if ($where !== " WHERE ") {
                $where .= " AND ";
            }
            $where .= "vrsta_id = :id_vrs";
            $params[':id_vrs'] = $data['id_vrs'];
            $modelVrs = new DokumentVrsta();
            $vrsta = $modelVrs->find((int) $data['id_vrs']);
        } else {
            $vrsta = null;
        }

        $where = $where === " WHERE " ? "" : $where;
        $model = new Dokument();
        $sql = "SELECT * FROM {$model->getTable()}{$where} ORDER BY created_at DESC;";
        $dokumenti = $model->paginate($page, 'page', $sql, $params);


        $sql = "SELECT
                COUNT(id) as broj, MONTHNAME(t.created_at) as mesec, YEAR(t.created_at) as godina, MONTH (t.created_at) as mm
                FROM dokumenti t
                GROUP BY EXTRACT(YEAR_MONTH FROM t.created_at) DESC;";
        $arhiva =$model->fetch($sql);

        $this->render($response, 'dokumenti/lista.twig', compact('kategorije', 'dokumenti', 'arhiva', 'data', 'kategorija', 'vrsta', 'radni'));
    }

    public function getDokumentiKategorija($request, $response, $args)
    {
        $id_kategorije = (int) $args['id'];
        $modelDokument = new Dokument();
        $dokumenti = $modelDokument->paginate(
            $this->page(),
            'page',
            "SELECT * FROM dokumenti
            WHERE kategorija_id = {$id_kategorije}
            ORDER BY created_at DESC;"
        );
        $sql = "SELECT
                COUNT(id) as broj, MONTHNAME(t.created_at) as mesec, YEAR(t.created_at) as godina, MONTH (t.created_at) as mm
                FROM dokumenti t
                GROUP BY EXTRACT(YEAR_MONTH FROM t.created_at) DESC;";
        $arhiva =$modelDokument->fetch($sql);

        $model_kategorije = new DokumentKategorija();
        $kategorije = $model_kategorije->all();
        $this->render($response, 'dokumenti/lista.twig', compact('kategorije', 'dokumenti', 'arhiva'));
    }

    public function getDokumentDodavanje($request, $response)
    {
        $modelK = new DokumentKategorija();
        $kategorije = $modelK->getFlatListNS();

        $sql = "SELECT MAX(level) AS maks FROM {$modelK->getTable()};";
        $nivo_query = $modelK->fetch($sql);
        $nivo = $nivo_query[0]->maks;

        $modelV = new DokumentVrsta();
        $vrste = $modelV->all();

        $this->render($response, 'autor/dokumenti/dodavanje.twig', compact('vrste', 'kategorije', 'nivo'));
    }

    public function postDokumentDodavanje($request, $response)
    {
        $data = $request->getParams();
        $dokument = $request->getUploadedFiles()['dokument'];
        unset($data['csrf_name']);
        unset($data['csrf_value']);

        if ($dokument->getError() === UPLOAD_ERR_NO_FILE) {
            $this->flash->addMessage('danger', 'Морате одабрати документ.');
            return $response->withRedirect($this->router->pathFor('dokumenti.lista'));
        }

        if ($dokument->getError() !== UPLOAD_ERR_OK) {
            $this->flash->addMessage('danger', 'Дошло је до грешке приликом отпремања документа.');
            return $response->withRedirect($this->router->pathFor('dokumenti.lista'));
        }

        $validation_rules = [
            'naslov' => [
                'required' => true,
            ],
            'vrsta_id' => [
                'required' => true,
            ],
            'kategorija_id' => [
                'required' => true,
            ],
        ];
        $modelVrs = new DokumentVrsta();
        $vrstam = $modelVrs->find((int) $data['vrsta_id']);
        $vrsta = $vrstam->naziv;

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Дошло је до грешке приликом додавања докумената.');
            return $response->withRedirect($this->router->pathFor('dokumenti.lista'));
        } else {
            $unique = bin2hex(random_bytes(8));
            $extension = pathinfo($dokument->getClientFilename(), PATHINFO_EXTENSION);
            $velicina_tekst = human_filesize($dokument->getSize());
            $velicina_mb = $dokument->getSize() / 1024 / 1024;
            $naslov = str_replace(" ", "_", $data['naslov']);
            $name = "{$vrsta}_{$naslov}_{$unique}";
            $filename = "{$name}.{$extension}";
            $veza = URL . "doc/{$filename}";
            $data['velicina_tekst'] = $velicina_tekst;
            $data['velicina_mb'] = $velicina_mb;
            $data['tip'] = $extension;
            $data['link'] = $veza;
            $data['korisnik_id'] = $this->auth->user()->id;
            $dokument->moveTo('doc/' . $filename);
            $modelDokument = new Dokument();
            $modelDokument->insert($data);
            $this->flash->addMessage('success', 'Докуменат је успешно сачуван.');
            return $response->withRedirect($this->router->pathFor('dokumenti.lista'));
        }
    }

    public function postDokumentiBrisanje($request, $response)
    {
        $id = (int)$request->getParam('idBrisanje');
        $modelDokument = new Dokument();
        $dok = $modelDokument->find($id);
        $tmp = explode('/', $dok->link);
        $file = DIR . 'pub' . DS . 'doc' . DS . end($tmp);
        $success = $modelDokument->deleteOne($id);
        if ($success) {
            if ($modelDokument->isti(end($tmp)) == 1) {
                unlink($file);
            }
            $this->flash->addMessage('success', "Докуменат је успешно обрисан.");
            return $response->withRedirect($this->router->pathFor('dokumenti.lista'));
        } else {
            $this->flash->addMessage('danger', "Дошло је до грешке приликом брисања докумената.");
            return $response->withRedirect($this->router->pathFor('dokumenti.lista'));
        }
    }

    public function getDokumentiIzmena($request, $response, $args)
    {

        $id = (int) $args['id'];
        $modelDokument = new Dokument();
        $dokument = $modelDokument->find($id);

        $modelK = new DokumentKategorija();
        $kategorije = $modelK->getFlatListNS();

        $sql = "SELECT MAX(level) AS maks FROM {$modelK->getTable()};";
        $nivo_query = $modelK->fetch($sql);
        $nivo = $nivo_query[0]->maks;

        $modelV = new DokumentVrsta();
        $vrste = $modelV->all();
        
        $this->render($response, 'autor/dokumenti/izmena.twig', compact('dokument',  'kategorije', 'vrste', 'nivo'));
    }

    public function postDokumentiIzmena($request, $response)
    {
        $data = $this->data();
        $id = $data['idIzmena'];
        unset($data['idIzmena']);

        $validation_rules = [
            'naslov' => [
                'required' => true,
            ],
            'vrsta_id' => [
                'required' => true,
            ],
            'kategorija_id' => [
                'required' => true,
            ],
        ];

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Дошло је до грешке приликом измене података документа.');
            return $response->withRedirect($this->router->pathFor('dokumenti.lista'));
        } else {
            $this->flash->addMessage('success', 'Подаци о документу су успешно промењени.');
            $modelDokument = new Dokument();
            $stari = $modelDokument->find($id);
            $modelDokument->update($data, $id);
            $dokument = $modelDokument->find($id);
            $this->log($this::IZMENA, $dokument, 'naslov', $stari);
            return $response->withRedirect($this->router->pathFor('dokumenti.lista'));
        }
    }

    public function getDokumentiLink($request, $response, $args)
    {

        $id = (int) $args['id'];
        $modelDokument = new Dokument();
        $dokument = $modelDokument->find($id);

        $gde_ga_ima = $modelDokument->kategorije_za_link($dokument->link);

        $modelK = new DokumentKategorija();
        $kategorije = $modelK->getFlatListNS();

        $sql = "SELECT MAX(level) AS maks FROM {$modelK->getTable()};";
        $nivo_query = $modelK->fetch($sql);
        $nivo = $nivo_query[0]->maks;
        
        $this->render($response, 'autor/dokumenti/link.twig', compact('dokument',  'kategorije', 'nivo', 'gde_ga_ima'));
    }

    public function postDokumentiLink($request, $response)
    {
        $data = $this->data();
        $id = $data['idIzmena'];
        unset($data['idIzmena']);

        $modelDokument = new Dokument();
        $stari = $modelDokument->find($id);

        $data['naslov'] = $stari->naslov;
        $data['link'] = $stari->link;
        $data['vrsta_id'] = $stari->vrsta_id;
        $data['opis'] = $stari->opis;
        $data['tip'] = $stari->tip;
        $data['velicina_tekst'] = $stari->velicina_tekst;
        $data['velicina_mb'] = $stari->velicina_mb;
        $data['korisnik_id'] = $this->auth->user()->id;

        $validation_rules = [
            'naslov' => [
                'required' => true,
            ],
            'vrsta_id' => [
                'required' => true,
            ],
            'kategorija_id' => [
                'required' => true,
            ],
        ];

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Дошло је до грешке приликом постављања линка на додатну категорију.');
            return $response->withRedirect($this->router->pathFor('dokumenti.lista'));
        } else {
            $this->flash->addMessage('success', 'Линковање документа у додатну категорију је успешно.');
            $modelDokument->insert($data);
            $dok = $modelDokument->find($modelDokument->lastId());
            $this->log($this::DODAVANJE, $dok, 'naziv');
            return $response->withRedirect($this->router->pathFor('dokumenti.lista'));
        }
    }

    public function getDokumentiArhiviranje($request, $response, $args)
    {

        $id = (int) $args['id'];
        $modelDokument = new Dokument();
        $dokument = $modelDokument->find($id);

        $gde_ga_ima = $modelDokument->kategorije_za_link($dokument->link);
        
        $this->render($response, 'autor/dokumenti/arhiviranje.twig', compact('dokument', 'gde_ga_ima'));
    }

    public function postDokumentiArhiviranje($request, $response)
    {
        $data = $this->data();
        $id = $data['idIzmena'];
        unset($data['idIzmena']);

        //Vreme 
        $sada = time();
        $mysqlvreme = date ('Y-m-d H:i:s', $sada);

        if ($id) {
            $model = new Dokument();
            $stari = $model->find($id);
            $zaupit = '%'.$stari->link.'%';
            if ($stari->arhiva) {
                $sql = "UPDATE {$model->getTable()} SET arhiva = NULL WHERE link LIKE '$zaupit';";
            }else{
                $sql = "UPDATE {$model->getTable()} SET arhiva = '$mysqlvreme' WHERE link LIKE '$zaupit';";
            }
            
            $model->fetch($sql);
            return $response->withRedirect($this->router->pathFor('dokumenti.lista'));
        } else {
            return $response->withRedirect($this->router->pathFor('dokumenti.lista'));
        }
    }
}
