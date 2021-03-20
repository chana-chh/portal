<?php

namespace App\Controllers;

use App\Models\BibliotekaProgram;
use App\Classes\Logger;

class BibliotekaProgramController extends Controller
{
    public function getLista($request, $response)
    {
        $model = new BibliotekaProgram();
        $vrste = $model->all();

        $this->render($response, 'bibprogram/lista.twig', compact('vrste'));
    }

    public function postDodavanje($request, $response)
    {
        $data = $this->data();

        $validation_rules = [
            'naziv' => [
                'required' => true,
                'minlen' => 5,
                'maxlen' => 255,
                'unique' => 'biblioteka_program.naziv'
            ],
            'korisnik_id' => [
                'required' => true,
            ]
        ];

        $data['korisnik_id'] = $this->auth->user()->id;

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Дошло је до грешке приликом додавања врсте ПРОГРАМА.');
            return $response->withRedirect($this->router->pathFor('bib.program'));
        } else {
            $this->flash->addMessage('success', 'Нова врста ПРОГРАМА је успешно додата.');
            $model = new BibliotekaProgram();

            $model->insert($data);
            $vrsta = $model->find($model->lastId());
            $this->log($this::DODAVANJE, $vrsta, 'naziv');
            return $response->withRedirect($this->router->pathFor('bib.program'));
        }
    }

    public function postBrisanje($request, $response)
    {
        $id = (int)$request->getParam('idBrisanje');
        $model = new BibliotekaProgram();
        $vrsta = $model->find($id);
        $success = $model->deleteOne($id);

        if ($success) {
            $this->flash->addMessage('success', "Врста ПРОГРАМА је успешно обрисана.");
            $this->log($this::BRISANJE, $vrsta, 'naziv', $vrsta);
            return $response->withRedirect($this->router->pathFor('bib.program'));
        } else {
            $this->flash->addMessage('danger', "Дошло је до грешке приликом брисања врсте ПРОГРАМА.");
            return $response->withRedirect($this->router->pathFor('bib.program'));
        }
    }

    public function postDetalj($request, $response)
    {
        $data = $request->getParams();
        $cName = $this->csrf->getTokenName();
        $cValue = $this->csrf->getTokenValue();
        $id = $data['id'];
        $model = new BibliotekaProgram();
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
                'unique' => 'biblioteka_program.naziv#id:' . $id,
            ]
        ];

        $this->validator->validate($datam, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Дошло је до грешке приликом измене врсте ПРОГРАМА.');
            return $response->withRedirect($this->router->pathFor('bib.program'));
        } else {
            $this->flash->addMessage('success', 'Подаци о врсти ПРОГРАМА су успешно измењени.');
            $model = new BibliotekaProgram();
            $stari = $model->find($id);
            $model->update($datam, $id);
            $vrsta = $model->find($id);
            $this->log($this::IZMENA, $vrsta, 'naziv', $stari);
            return $response->withRedirect($this->router->pathFor('bib.program'));
        }
    }
}
