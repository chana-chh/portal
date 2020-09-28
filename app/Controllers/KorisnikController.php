<?php

namespace App\Controllers;

use \App\Models\Korisnik;
use \App\Models\Kategorija;
use App\Classes\Logger;

class KorisnikController extends Controller
{
    public function getKorisnikLista($request, $response)
    {
        $model = new Korisnik();
        if ($this->auth->user()->nivo === 1000) {
            $data = $model->paginate($this->page());
        } else {
            $data = $model->paginate($this->page(), 'page', "SELECT * FROM korisnici WHERE nivo != 1000;");
        }

        $model_kategorije = new Kategorija();
        $kategorije = $model_kategorije->all();

        $this->render($response, 'korisnik/lista.twig', compact('data', 'kategorije'));
    }

    public function postKorisnikDodavanje($request, $response)
    {
        $data = $this->data();

        $validation_rules = [
            'ime' => [
                'required' => true,
                'alnum' => true,
            ],
            'prezime' => [
                'required' => true,
                'alnum' => true,
            ],
            'korisnicko_ime' => [
                'required' => true,
                'minlen' => 5,
                'maxlen' => 50,
                'alnum' => true,
                'unique' => 'korisnici.korisnicko_ime', // tabela.kolona
            ],
            'email' => [
                'required' => true,
                'unique' => 'korisnici.email', // tabela.kolona
            ],
            'lozinka' => [
                'required' => true,
                'minlen' => 6,
            ],
            'lozinka_potvrda' => [
                'match_field' => 'lozinka',
            ],
            'nivo' => [
                'required' => true
            ],
            'korisnik_id' => [
                'required' => true,
            ]
        ];
        $data['dozvoljene_kategorije'] = implode(', ', $data['dozvoljene_kategorije']);
        $data['korisnik_id'] = $this->auth->user()->id;

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Дошло је догрешке приликом додавања корисника.');
            return $response->withRedirect($this->router->pathFor('admin.korisnik.lista'));
        } else {
            $this->flash->addMessage('success', 'Нов корисник је успешно додат.');
            $modelKorisnik = new Korisnik();
            unset($data['lozinka_potvrda']);
            $data['lozinka'] = password_hash($data['lozinka'], PASSWORD_DEFAULT);
            $modelKorisnik->insert($data);
            $korisnik = $modelKorisnik->find($modelKorisnik->lastId());
            $this->log($this::DODAVANJE, $korisnik, ['ime','prezime']);
            return $response->withRedirect($this->router->pathFor('admin.korisnik.lista'));
        }
    }

    public function postKorisnikBrisanje($request, $response)
    {
        $id = $this->dataId('idBrisanje');
        $model = new Korisnik();
        $korisnik = $model->find($id);
        $success = $model->deleteOne($id);
        if ($success) {
            $this->log($this::BRISANJE, $korisnik, ['ime','prezime'], $korisnik);
            $this->flash->addMessage('success', "Корисник је успешно обрисан.");
        } else {
            $this->flash->addMessage('danger', "Дошло је до грешке приликом брисања корисника.");
        }
        return $response->withRedirect($this->router->pathFor('admin.korisnik.lista'));
    }

    public function getKorisnikIzmena($request, $response, $args)
    {
        $id = (int)$args['id'];
        $model = new Korisnik();
        $korisnik = $model->find($id);

        $model_kategorije = new Kategorija();
        $kategorije = $model_kategorije->all();

        $this->render($response, 'korisnik/izmena.twig', compact('korisnik', 'kategorije'));
    }

    public function postKorisnikIzmena($request, $response)
    {
        $data = $this->data('id');
        $id = $this->dataId();

        $validation_rules = [
            'ime' => [
                'required' => true,
                'alnum' => true,
            ],
            'prezime' => [
                'required' => true,
                'alnum' => true,
            ],
            'korisnicko_ime' => [
                'required' => true,
                'minlen' => 5,
                'maxlen' => 50,
                'alnum' => true,
                'unique' => 'korisnici.korisnicko_ime#id:'.$id, // tabela.kolona#id_kol:id_val
            ],
            'email' => [
                'required' => true,
                'unique' => 'korisnici.email#id:'.$id, // tabela.kolona#id_kol:id_val
            ],
            'nivo' => [
                'required' => true
            ]
        ];

        $validation_pass = [
            'lozinka' => [
                'required' => true,
                'minlen' => 6,
            ],
            'lozinka_potvrda' => [
                'match_field' => 'lozinka',
            ]
        ];

        if (!empty($data['lozinka'])) {
            array_push($validation_rules, $validation_pass);
        }

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', '"Дошло је до грешке приликом измене података о кориснику.');
            return $response->withRedirect($this->router->pathFor('admin.korisnik.izmena.get', ['id' => $id]));
        } else {
            $this->flash->addMessage('success', 'Подаци о кориснику су успешно измењени.');
            $modelKorisnik = new Korisnik();
            $stari = $modelKorisnik->find($id);
            unset($data['lozinka_potvrda']);
            if (!empty($data['lozinka'])) {
                $data['lozinka'] = password_hash($data['lozinka'], PASSWORD_DEFAULT);
            } else {
                unset($data['lozinka']);
            }
            $modelKorisnik->update($data, $id);
            $korisnik = $modelKorisnik->find($id);
            $this->log($this::IZMENA, $korisnik, ['ime','prezime'], $stari);
            return $response->withRedirect($this->router->pathFor('admin.korisnik.lista'));
        }
    }
}
