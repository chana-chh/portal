<?php

namespace App\Controllers;

use App\Models\DokumentKategorija;
use App\Classes\Logger;

class DokumentKatController extends Controller
{
    public function getKategorije($request, $response)
    {
        $model = new DokumentKategorija();
        $kategorije = $model->all();

        $this->render($response, 'dokkategorije/lista.twig', compact('kategorije'));
    }

    public function postKategorijeDodavanje($request, $response)
    {
        $data = $this->data();

        $validation_rules = [
            'naziv' => [
                'required' => true,
                'minlen' => 5,
                'maxlen' => 255,
                'unique' => 'kategorije.naziv'
            ],
            'korisnik_id' => [
                'required' => true,
            ]
        ];

        $data['korisnik_id'] = $this->auth->user()->id;

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Дошло је до грешке приликом додавања категорије ДОКУМЕНТА.');
            return $response->withRedirect($this->router->pathFor('kategorija'));
        } else {
            $this->flash->addMessage('success', 'Нова категорија ДОКУМЕНТА је успешно додата.');
            $model = new DokumentKategorija();
            $model->insert($data);
            $kategorija = $model->find($model->lastId());
            $this->log($this::DODAVANJE, $kategorija, 'naziv');
            return $response->withRedirect($this->router->pathFor('dokument.kategorija'));
        }
    }

    public function postKategorijeBrisanje($request, $response)
    {
        $id = (int)$request->getParam('idBrisanje');
        $model = new DokumentKategorija();
        $kategorija = $model->find($id);
        $success = $model->deleteOne($id);

        if ($success) {
            $this->flash->addMessage('success', "Категорија ДОКУМЕНТА је успешно обрисана.");
            $this->log($this::BRISANJE, $kategorija, 'naziv', $kategorija);
            return $response->withRedirect($this->router->pathFor('dokument.kategorija'));
        } else {
            $this->flash->addMessage('danger', "Дошло је до грешке приликом брисања категорије ДОКУМЕНТА.");
            return $response->withRedirect($this->router->pathFor('dokument.kategorija'));
        }
    }

    public function postKategorijeDetalj($request, $response)
    {
        $data = $request->getParams();
        $cName = $this->csrf->getTokenName();
        $cValue = $this->csrf->getTokenValue();
        $id = $data['id'];
        $model = new DokumentKategorija();
        $kategorija = $model->find($id);

        $ar = ["cname" => $cName, "cvalue"=>$cValue, "kategorija"=>$kategorija];

        return $response->withJson($ar);
    }

    public function postKategorijeIzmena($request, $response)
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
                'unique' => 'kategorije.naziv#id:' . $id,
            ]
        ];

        $this->validator->validate($datam, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Дошло је до грешке приликом измене категорије ДОКУМЕНТА.');
            return $response->withRedirect($this->router->pathFor('dokument.kategorija'));
        } else {
            $this->flash->addMessage('success', 'Подаци о категорији ДОКУМЕНТА су успешно измењени.');
            $model = new DokumentKategorija();
            $stari = $model->find($id);
            $model->update($datam, $id);
            $kategorija = $model->find($id);
            $this->log($this::IZMENA, $kategorija, 'naziv', $stari);
            return $response->withRedirect($this->router->pathFor('dokument.kategorija'));
        }
    }
}
