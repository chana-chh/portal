<?php

namespace App\Controllers;

use App\Classes\Auth;
use App\Classes\Logger;
use App\Models\Kategorija;
use App\Models\Clanak;

class AutorObjaveController extends Controller
{
    public function getObjaveLista($request, $response)
    {
        $model = new Clanak();
        $user_id = $this->auth->user()->id;
        $sql =  "SELECT * FROM clanci WHERE korisnik_id = {$user_id}";
        $clanci = $model->paginate($this->page(), 'page', $sql);
        $this->render($response, 'autor/clanci/lista.twig', compact('clanci'));
    }

    public function getObjaveDodavanje($request, $response, $args)
    {
        $kategorije = $this->auth->user()->dozvoljeneKategorije();
        $this->render($response, 'autor/clanci/dodavanje.twig', compact('kategorije'));
    }

    public function postObjaveDodavanje($request, $response)
    {
        $data = $this->data();
        // mozda strip_tags($data['clanak'], '<p><h1>dozvoljeni tagovi')
        $data['objavljen'] = isset($data['objavljen']) ? 1 : 0;
        // dd($data);
        $validation_rules = [
            'naslov' => [
                'required' => true,
                'maxlen' => 255
            ],
            'rezime' => [
                'required' => true,
                 'maxlen' => 255
            ],
            'clanak' => ['required' => true,],
            'kategorija' => ['required' => true,],
        ];

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Дошло је до грешке приликом додаванја објаве.');
            return $response->withRedirect($this->router->pathFor('autor.objave.dodavanje'));
        } else {
            $data['korisnik_id'] = $this->auth->user()->id;
            $model = new Clanak();
            if ($data['objavljen'] === 0) {
                $data['published_at'] = null;
            }
            $model->insert($data);
            $objava = $model->find($model->lastId());
            $this->log($this::DODAVANJE, $objava, 'naslov');
            $this->flash->addMessage('success', 'Нова објава је успешно додата.');
            return $response->withRedirect($this->router->pathFor('autor.objave.lista'));
        }
    }
}
