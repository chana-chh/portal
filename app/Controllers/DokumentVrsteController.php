<?php

namespace App\Controllers;

use App\Models\DokumentVrsta;
use App\Classes\Logger;

class DokumentVrsteController extends Controller
{
    public function getVrste($request, $response)
    {
        $model = new DokumentVrsta();
        $vrste = $model->all();

        $this->render($response, 'dokvrste/lista.twig', compact('vrste'));
    }

    public function postVrsteDodavanje($request, $response)
    {
        $data = $this->data();

        $validation_rules = [
            'naziv' => [
                'required' => true,
                'minlen' => 5,
                'maxlen' => 255,
                'unique' => 'dokumenti_vrste.naziv'
            ],
            'korisnik_id' => [
                'required' => true,
            ]
        ];

        $data['korisnik_id'] = $this->auth->user()->id;

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Дошло је до грешке приликом додавања врсте ДОКУМЕНТА.');
            return $response->withRedirect($this->router->pathFor('vrste'));
        } else {
            $this->flash->addMessage('success', 'Нова врста ДОКУМЕНТА је успешно додата.');
            $model = new DokumentVrsta();

            $model->insert($data);
            $vrsta = $model->find($model->lastId());
            $this->log($this::DODAVANJE, $vrsta, 'naziv');
            return $response->withRedirect($this->router->pathFor('vrste'));
        }
    }

    public function postVrsteBrisanje($request, $response)
    {
        $id = (int)$request->getParam('idBrisanje');
        $model = new DokumentVrsta();
        $vrsta = $model->find($id);
        $success = $model->deleteOne($id);

        if ($success) {
            $this->flash->addMessage('success', "Врста ДОКУМЕНТА је успешно обрисана.");
            $this->log($this::BRISANJE, $vrsta, 'naziv', $vrsta);
            return $response->withRedirect($this->router->pathFor('vrste'));
        } else {
            $this->flash->addMessage('danger', "Дошло је до грешке приликом брисања врсте ДОКУМЕНТА.");
            return $response->withRedirect($this->router->pathFor('vrste'));
        }
    }

    public function postVrsteDetalj($request, $response)
    {
        $data = $request->getParams();
        $cName = $this->csrf->getTokenName();
        $cValue = $this->csrf->getTokenValue();
        $id = $data['id'];
        $model = new DokumentVrsta();
        $vrsta = $model->find($id);

        $ar = ["cname" => $cName, "cvalue"=>$cValue, "vrsta"=>$vrsta];

        return $response->withJson($ar);
    }

    public function postVrsteIzmena($request, $response)
    {

        $data = $this->data();
        $id = $data['idIzmena'];
        unset($data['idIzmena']);

        $datam = [
            "naziv" => $data['nazivModal'],
        ];

        $validation_rules = [
            'naziv' => [
                'required' => true,
                'minlen' => 5,
                'maxlen' => 50,
                'unique' => 'dokumenti_vrste.naziv#id:' . $id,
            ]
        ];

        $this->validator->validate($datam, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Дошло је до грешке приликом измене врсте ДОКУМЕНТА.');
            return $response->withRedirect($this->router->pathFor('vrste'));
        } else {
            $this->flash->addMessage('success', 'Подаци о врсти ДОКУМЕНТА су успешно измењени.');
            $model = new DokumentVrsta();
            $stari = $model->find($id);
            $model->update($datam, $id);
            $vrsta = $model->find($id);
            $this->log($this::IZMENA, $vrsta, 'naziv', $stari);
            return $response->withRedirect($this->router->pathFor('vrste'));
        }
    }
}
