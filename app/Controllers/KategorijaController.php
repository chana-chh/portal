<?php

namespace App\Controllers;

use App\Models\Kategorija;
use App\Classes\Logger;

class KategorijaController extends Controller
{
    public function getKategorije($request, $response)
    {
        $model = new Kategorija();
        $kategorije = $model->all();

        $this->render($response, 'kategorija/lista.twig', compact('kategorije'));
    }

    public function postKategorijeDodavanje($request, $response)
    {
        $data = $this->data();

        $validation_rules = [
            'naziv' => [
                'required' => true,
                'minlen' => 5,
                'maxlen' => 50,
                'unique' => 'kategorije.naziv'
            ],
        ];

        $data['korisnik_id'] = $this->auth->user()->id;

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom dodavanja kategorije.');
            return $response->withRedirect($this->router->pathFor('kategorija'));
        } else {
            $this->flash->addMessage('success', 'Nova kategorija je uspešno dodata.');
            $model = new Kategorija();
            $model->insert($data);
            $kategorija = $model->find($model->lastId());
            $this->log($this::DODAVANJE, $kategorija, 'naziv');
            return $response->withRedirect($this->router->pathFor('kategorija'));
        }
    }

    public function postKategorijeBrisanje($request, $response)
    {
        $id = (int)$request->getParam('idBrisanje');
        $model = new Kategorija();
        $kategorija = $model->find($id);
        $success = $model->deleteOne($id);

        if ($success) {
            $this->flash->addMessage('success', "Kategorija je uspešno obrisana.");
            $this->log($this::BRISANJE, $kategorija, 'naziv', $kategorija);
            return $response->withRedirect($this->router->pathFor('kategorija'));
        } else {
            $this->flash->addMessage('danger', "Došlo je do greške prilikom brisanja kategorije.");
            return $response->withRedirect($this->router->pathFor('kategorija'));
        }
    }

    public function postKategorijeDetalj($request, $response)
    {
        $data = $request->getParams();
        $cName = $this->csrf->getTokenName();
        $cValue = $this->csrf->getTokenValue();
        $id = $data['id'];
        $model = new Kategorija();
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
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom izmene podataka kategorije.');
            return $response->withRedirect($this->router->pathFor('kategorija'));
        } else {
            $this->flash->addMessage('success', 'Podaci o kategoriji su uspešno izmenjeni.');
            $model = new Kategorija();
            $stari = $model->find($id);
            $model->update($datam, $id);
            $kategorija = $model->find($id);
            $this->log($this::IZMENA, $kategorija, 'naziv', $stari);
            return $response->withRedirect($this->router->pathFor('kategorija'));
        }
    }
}
