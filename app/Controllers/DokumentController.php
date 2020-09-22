<?php

namespace App\Controllers;

use App\Models\Dokument;
use App\Classes\Logger;

class DokumentController extends Controller
{
    public function postDokumentDodavanje($request, $response)
    {
        $data = $request->getParams();
        $dokument = $request->getUploadedFiles()['dokument'];
        unset($data['csrf_name']);
        unset($data['csrf_value']);

        if ($dokument->getError() === UPLOAD_ERR_NO_FILE) {
            $this->flash->addMessage('danger', 'Morate odabrati dokument.');
            return $response->withRedirect($this->router->pathFor('termin.ugovor.detalj.get', ['id' => $data['ugovor_id']]));
        }
        
        if ($dokument->getError() !== UPLOAD_ERR_OK) {
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom prebacivanja dokumenta.');
            return $response->withRedirect($this->router->pathFor('termin.ugovor.detalj.get', ['id' => $data['ugovor_id']]));
        }

        $validation_rules = [
            'opis' => [
                'required' => true,
            ],
        ];

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom dodavanja dokumenta.');
            return $response->withRedirect($this->router->pathFor('termin.ugovor.detalj.get', ['id' => $data['ugovor_id']]));
        } else {
            $unique = bin2hex(random_bytes(8));
            $extension = pathinfo($dokument->getClientFilename(), PATHINFO_EXTENSION);
            $opis = str_replace(" ", "_", $data['opis']);
            $name = "{$data['ugovor_id']}_{$opis}_{$unique}";
            $filename = "{$name}.{$extension}";
            $veza = URL . "doc/{$filename}";
            $data['link'] = $veza;
            $data['korisnik_id'] = $this->auth->user()->id;
            $dokument->moveTo('doc/' . $filename);
            $modelDokument = new Dokument();
            $modelDokument->insert($data);
            $this->flash->addMessage('success', 'Dokument je uspešno sačuvan.');
            return $response->withRedirect($this->router->pathFor('termin.ugovor.detalj.get', ['id' => $data['ugovor_id']]));
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
