<?php

namespace App\Controllers;

use App\Models\AnketaIspitanik;
use App\Classes\Logger;

class AnketaIspitanikController extends Controller
{
    public function getLista($request, $response)
    {
        $model = new AnketaIspitanik();
        $vrste = $model->all();

        $this->render($response, 'ankete/ispitanici.twig', compact('vrste'));
    }

    public function postDodavanje($request, $response)
    {
        $data = $this->data();

        $validation_rules = [
            'naziv' => [
                'required' => true,
                'minlen' => 5,
                'maxlen' => 255,
                'unique' => 'ankete_ispitanici.naziv'
            ],
            'korisnik_id' => [
                'required' => true,
            ]
        ];

        $data['korisnik_id'] = $this->auth->user()->id;

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Дошло је до грешке приликом додавања учесника анкете/испитаника.');
            return $response->withRedirect($this->router->pathFor('ispitanik'));
        } else {
            $this->flash->addMessage('success', 'Нови учесник анкете/испитаник је успешно додат.');
            $model = new AnketaIspitanik();

            $model->insert($data);
            $vrsta = $model->find($model->lastId());
            $this->log($this::DODAVANJE, $vrsta, 'naziv');
            return $response->withRedirect($this->router->pathFor('ispitanik'));
        }
    }

    public function postBrisanje($request, $response)
    {
        $id = (int)$request->getParam('idBrisanje');
        $model = new AnketaIspitanik();
        $ispitanik = $model->find($id);

        if (!empty($ispitanik->odgovori())) {
            $this->flash->addMessage('danger', "Овај УЧЕСНИК анкете/испитаник је учествовао у истраживањима. Обришите све одговоре везане УЧЕСНИКА да бисте га обрисали!");
            return $response->withRedirect($this->router->pathFor('ispitanik'));
        }else{
            $success = $model->deleteOne($id);
        }
        
        if ($success) {
            $this->flash->addMessage('success', "УЧЕСНИК анкете/испитаник је успешно обрисан.");
            $this->log($this::BRISANJE, $ispitanik, 'naziv', $ispitanik);
            return $response->withRedirect($this->router->pathFor('ispitanik'));
        } else {
            $this->flash->addMessage('danger', "Дошло је до грешке приликом брисања УЧЕСНИКА анкете/испитаника.");
            return $response->withRedirect($this->router->pathFor('ispitanik'));
        }

    }

    public function postDetalj($request, $response)
    {
        $data = $request->getParams();
        $cName = $this->csrf->getTokenName();
        $cValue = $this->csrf->getTokenValue();
        $id = $data['id'];
        $model = new AnketaIspitanik();
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
                'unique' => 'ankete_ispitanici.naziv#id:' . $id,
            ]
        ];

        $this->validator->validate($datam, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Дошло је до грешке приликом измене учесника анкете/испитаника.');
            return $response->withRedirect($this->router->pathFor('ispitanik'));
        } else {
            $this->flash->addMessage('success', 'Подаци о учеснику анкете/испитанику су успешно измењени.');
            $model = new AnketaIspitanik();
            $stari = $model->find($id);
            $model->update($datam, $id);
            $vrsta = $model->find($id);
            $this->log($this::IZMENA, $vrsta, 'naziv', $stari);
            return $response->withRedirect($this->router->pathFor('ispitanik'));
        }
    }
}
