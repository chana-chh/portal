<?php

namespace App\Controllers;

use App\Models\Dokument;
use App\Classes\Logger;

class DokumentController extends Controller
{
    public function getLista($request, $response)
    {
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        $page = isset($query['page']) ? (int)$query['page'] : 1;

        $model = new Dokument();
        $dokumenti = $model->paginate($this->page(), 'page', 
            "SELECT * FROM dokumenti
            ORDER BY created_at DESC;");
        $vrste = $model->enumOrSetList('vrsta');

        $d = $model->fetch("SELECT *
                    FROM dokumenti
                    LIMIT 1;")[0];

        $sql = "SELECT
                COUNT(id) as broj, MONTHNAME(t.created_at) as mesec, YEAR(t.created_at) as godina, MONTH (t.created_at) as mm
                FROM dokumenti t
                GROUP BY EXTRACT(YEAR_MONTH FROM t.created_at) DESC;";
        $arhiva =$model->fetch($sql);

        $this->render($response, 'dokumenti/lista.twig', compact('vrste', 'dokumenti', 'arhiva', 'd'));
    }

    public function getDokumentDodavanje($request, $response)
    {
        $model = new Dokument();
        $vrste = $model->enumOrSetList('vrsta');

        $this->render($response, 'autor/dokumenti/dodavanje.twig', compact('vrste'));
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
            'opis' => [
                'required' => true,
            ],
        ];

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Дошло је до грешке приликом додаванја докумената.');
            return $response->withRedirect($this->router->pathFor('dokumenti.lista'));
        } else {
            $unique = bin2hex(random_bytes(8));
            $extension = pathinfo($dokument->getClientFilename(), PATHINFO_EXTENSION);
            $naslov = str_replace(" ", "_", $data['naslov']);
            $name = "{$data['vrsta']}_{$naslov}_{$unique}";
            $filename = "{$name}.{$extension}";
            $veza = URL . "doc/{$filename}";
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
        $id = (int)$request->getParam('modal_dokument_id');
        $ugovor_id = (int)$request->getParam('modal_dokument_ugovor_id');
        $modelDokument = new Dokument();
        $dok = $modelDokument->find($id);
        $tmp = explode('/', $dok->link);
        $file = DIR . 'pub' . DS . 'doc' . DS . end($tmp);
        $success = $modelDokument->deleteOne($id);
        if ($success) {
            unlink($file);
            $this->flash->addMessage('success', "Dokument je uspešno obrisan.");
            return $response->withRedirect($this->router->pathFor('termin.ugovor.detalj.get', ['id' => $ugovor_id]));
        } else {
            $this->flash->addMessage('danger', "Došlo je do greške prilikom brisanja dokumenta.");
            return $response->withRedirect($this->router->pathFor('termin.ugovor.detalj.get', ['id' => $ugovor_id]));
        }
    }

    public function postDokumentDetalj($request, $response)
    {
        $data = $request->getParams();
        $cName = $this->csrf->getTokenName();
        $cValue = $this->csrf->getTokenValue();
        $id = $data['id'];
        $modelDokument = new Dokument();
        $dokument = $modelDokument->find($id);
        $ar = ["cname" => $cName, "cvalue"=>$cValue, "dokument"=>$dokument];
        return $response->withJson($ar);
    }

    public function postDokumentIzmena($request, $response)
    {
        $data = $request->getParams();
        $id = $data['idIzmenaDokumenta'];
        $ugovor_id = $data['idUgovorDokumenta'];
        unset($data['idUgovorDokumenta']);
        unset($data['idIzmenaDokumenta']);
        unset($data['csrf_name']);
        unset($data['csrf_value']);

        $datam = ["opis"=>$data['opisModal']];

        $validation_rules = [
            'opis' => [
                'required' => true
            ]
        ];

        $this->validator->validate($datam, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom izmene podataka dokumenta.');
            return $response->withRedirect($this->router->pathFor('termin.ugovor.detalj.get', ['id' => $ugovor_id]));
        } else {
            $this->flash->addMessage('success', 'Podaci o dokumentu su uspešno izmenjeni.');
            $modelDokument = new Dokument();
            $modelDokument->update($datam, $id);
            $dokument = $modelDokument->find($id);
            $this->log(Logger::IZMENA, $dokument, 'opis');
            return $response->withRedirect($this->router->pathFor('termin.ugovor.detalj.get', ['id' => $ugovor_id]));
        }
    }
}
