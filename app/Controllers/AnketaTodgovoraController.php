<?php

namespace App\Controllers;

use App\Models\AnketaTipOdgovora;
use App\Classes\Logger;

class AnketaTodgovoraController extends Controller
{
    public function getLista($request, $response)
    {
        $model = new AnketaTipOdgovora();
        $vrste = $model->all();

        $this->render($response, 'ankete/tip_odgovora.twig', compact('vrste'));
    }

    public function postDodavanje($request, $response)
    {
        $data = $this->data();

        $validation_rules = [
            'naziv' => [
                'required' => true,
                'minlen' => 2,
                'maxlen' => 255,
                'unique' => 'ankete_tip_odgovora.naziv'
            ],
            'ocena' => [
                'required' => true,
            ],
            'korisnik_id' => [
                'required' => true,
            ]
        ];

        $data['korisnik_id'] = $this->auth->user()->id;

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Дошло је до грешке приликом додавања типа одговора.');
            return $response->withRedirect($this->router->pathFor('tip-odgovora'));
        } else {
            $this->flash->addMessage('success', 'Тип одговора је успешно додат.');
            $model = new AnketaTipOdgovora();

            $model->insert($data);
            $vrsta = $model->find($model->lastId());
            $this->log($this::DODAVANJE, $vrsta, ['naziv', 'ocena']);
            return $response->withRedirect($this->router->pathFor('tip-odgovora'));
        }
    }

    public function postBrisanje($request, $response)
    {
        $id = (int)$request->getParam('idBrisanje');
        $model = new AnketaTipOdgovora();
        $provera = $model->provera($id);
        $vrsta = $model->find($id);

        if ($provera) {
            $this->flash->addMessage('danger', "Овај тип одговора је везан за одређена питања и није га могуће обрисати у овом тренутку!");
            return $response->withRedirect($this->router->pathFor('tip-odgovora'));
        }else{
            $success = $model->deleteOne($id);
        }
        
        if ($success) {
            $this->flash->addMessage('success', "Тип одговора је успешно обрисан.");
            $this->log($this::BRISANJE, $vrsta, ['naziv', 'ocena'], $vrsta);
            return $response->withRedirect($this->router->pathFor('tip-odgovora'));
        } else {
            $this->flash->addMessage('danger', "Дошло је до грешке приликом брисања тип одговора.");
            return $response->withRedirect($this->router->pathFor('tip-odgovora'));
        }
    }

    public function postDetalj($request, $response)
    {
        $data = $request->getParams();
        $cName = $this->csrf->getTokenName();
        $cValue = $this->csrf->getTokenValue();
        $id = $data['id'];
        $model = new AnketaTipOdgovora();
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
            "ocena" => $data['ocenaModal']
        ];

        $validation_rules = [
            'naziv' => [
                'required' => true,
                'minlen' => 2,
                'maxlen' => 255,
                'unique' => 'ankete_tip_odgovora.naziv#id:' . $id,
            ],
            'ocena' => [
                'required' => true,
            ]
        ];

        $this->validator->validate($datam, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Дошло је до грешке приликом измене типа одговора.');
            return $response->withRedirect($this->router->pathFor('tip-odgovora'));
        } else {
            $this->flash->addMessage('success', 'Подаци о типу одговора су успешно измењени.');
            $model = new AnketaTipOdgovora();
            $stari = $model->find($id);
            $model->update($datam, $id);
            $vrsta = $model->find($id);
            $this->log($this::IZMENA, $vrsta, ['naziv', 'ocena'], $stari);
            return $response->withRedirect($this->router->pathFor('tip-odgovora'));
        }
    }
}
