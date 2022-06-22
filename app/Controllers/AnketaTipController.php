<?php

namespace App\Controllers;

use App\Models\AnketaTip;
use App\Classes\Logger;

class AnketaTipController extends Controller
{
    public function getLista($request, $response)
    {
        $model = new AnketaTip();
        $vrste = $model->all();

        $this->render($response, 'ankete/tip.twig', compact('vrste'));
    }

    public function postDodavanje($request, $response)
    {
        $data = $this->data();

        $validation_rules = [
            'naziv' => [
                'required' => true,
                'minlen' => 5,
                'maxlen' => 255,
                'unique' => 'ankete_tip.naziv'
            ],
            'korisnik_id' => [
                'required' => true,
            ]
        ];

        $data['korisnik_id'] = $this->auth->user()->id;

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Дошло је до грешке приликом додавања типа анкете.');
            return $response->withRedirect($this->router->pathFor('tip'));
        } else {
            $this->flash->addMessage('success', 'Нови тип анкете/испитаник је успешно додат.');
            $model = new AnketaTip();

            $model->insert($data);
            $vrsta = $model->find($model->lastId());
            $this->log($this::DODAVANJE, $vrsta, 'naziv');
            return $response->withRedirect($this->router->pathFor('tip'));
        }
    }

    public function postBrisanje($request, $response)
    {
        $id = (int)$request->getParam('idBrisanje');
        $model = new AnketaTip();
        $tip = $model->find($id);

        if (!empty($tip->anketa())) {
            $this->flash->addMessage('danger', "Постоје анкете/истраживања овог типа. Молимо да најпре њих обришете како бисте обрисали овај тип.");
            return $response->withRedirect($this->router->pathFor('tip'));
        }else{
            $success = $model->deleteOne($id);
        }
        
        if ($success) {
            $this->flash->addMessage('success', "Тип анкете/испитаник је успешно обрисан.");
            $this->log($this::BRISANJE, $tip, 'naziv', $tip);
            return $response->withRedirect($this->router->pathFor('tip'));
        } else {
            $this->flash->addMessage('danger', "Дошло је до грешке приликом брисања типа анкете.");
            return $response->withRedirect($this->router->pathFor('tip'));
        }

    }

    public function postDetalj($request, $response)
    {
        $data = $request->getParams();
        $cName = $this->csrf->getTokenName();
        $cValue = $this->csrf->getTokenValue();
        $id = $data['id'];
        $model = new AnketaTip();
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
                'maxlen' => 255,
                'unique' => 'ankete_tip.naziv#id:' . $id,
            ]
        ];

        $this->validator->validate($datam, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Дошло је до грешке приликом измене типа анкете.');
            return $response->withRedirect($this->router->pathFor('tip'));
        } else {
            $this->flash->addMessage('success', 'Подаци о типу анкете су успешно измењени.');
            $model = new AnketaTip();
            $stari = $model->find($id);
            $model->update($datam, $id);
            $vrsta = $model->find($id);
            $this->log($this::IZMENA, $vrsta, 'naziv', $stari);
            return $response->withRedirect($this->router->pathFor('tip'));
        }
    }
}
