<?php

namespace App\Controllers;

use App\Models\BibliotekaVrsta;
use App\Classes\Logger;

class BibliotekaVrsteController extends Controller
{
    public function getLista($request, $response)
    {
        $model = new BibliotekaVrsta();
        $vrste = $model->all();

        $this->render($response, 'bibvrsta/lista.twig', compact('vrste'));
    }

    public function postDodavanje($request, $response)
    {
        $data = $this->data();

        $validation_rules = [
            'naziv' => [
                'required' => true,
                'minlen' => 5,
                'maxlen' => 255,
                'unique' => 'biblioteka_vrste.naziv'
            ],
            'korisnik_id' => [
                'required' => true,
            ]
        ];

        $data['korisnik_id'] = $this->auth->user()->id;

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Дошло је до грешке приликом додавања врсте МАТЕРИЈАЛА.');
            return $response->withRedirect($this->router->pathFor('bib.vrste'));
        } else {
            $this->flash->addMessage('success', 'Нова врста МАТЕРИЈАЛА је успешно додата.');
            $model = new BibliotekaVrsta();

            $model->insert($data);
            $vrsta = $model->find($model->lastId());
            $this->log($this::DODAVANJE, $vrsta, 'naziv');
            return $response->withRedirect($this->router->pathFor('bib.vrste'));
        }
    }

    public function postBrisanje($request, $response)
    {
        $id = (int)$request->getParam('idBrisanje');
        $model = new BibliotekaVrsta();
        $vrsta = $model->find($id);
        $success = $model->deleteOne($id);

        if ($success) {
            $this->flash->addMessage('success', "Врста МАТЕРИЈАЛА је успешно обрисана.");
            $this->log($this::BRISANJE, $vrsta, 'naziv', $vrsta);
            return $response->withRedirect($this->router->pathFor('bib.vrste'));
        } else {
            $this->flash->addMessage('danger', "Дошло је до грешке приликом брисања врсте МАТЕРИЈАЛА.");
            return $response->withRedirect($this->router->pathFor('bib.vrste'));
        }
    }

    public function postDetalj($request, $response)
    {
        $data = $request->getParams();
        $cName = $this->csrf->getTokenName();
        $cValue = $this->csrf->getTokenValue();
        $id = $data['id'];
        $model = new BibliotekaVrsta();
        $vrsta = $model->find($id);

        $ar = ["cname" => $cName, "cvalue"=>$cValue, "vrsta"=>$vrsta];

        return $response->withJson($ar);
    }

    public function postIzmena($request, $response)
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
                'unique' => 'biblioteka_vrste.naziv#id:' . $id,
            ]
        ];

        $this->validator->validate($datam, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Дошло је до грешке приликом измене врсте МАТЕРИЈАЛА.');
            return $response->withRedirect($this->router->pathFor('bib.vrste'));
        } else {
            $this->flash->addMessage('success', 'Подаци о врсти МАТЕРИЈАЛА су успешно измењени.');
            $model = new BibliotekaVrsta();
            $stari = $model->find($id);
            $model->update($datam, $id);
            $vrsta = $model->find($id);
            $this->log($this::IZMENA, $vrsta, 'naziv', $stari);
            return $response->withRedirect($this->router->pathFor('bib.vrste'));
        }
    }
}
