<?php

namespace App\Controllers;

use App\Classes\Logger;

class AuthController extends Controller
{
    public function getPrijava($request, $response)
    {
        $this->render($response, 'auth/prijava.twig');
    }

    public function postPrijava($request, $response)
    {
        $ok = $this->auth->login($request->getParam('korisnicko_ime'), $request->getParam('lozinka'));
        if ($ok) {
            $this->flash->addMessage('success', 'Корисник је успешно пријављен.');
            if (isset($_SESSION['LOGIN_URL'])) {
                $url = $_SESSION['LOGIN_URL'];
                unset($_SESSION['LOGIN_URL']);
                return $response->withRedirect($url);
            } else {
                return $response->withRedirect($this->router->pathFor('pocetna'));
            }
        } else {
            $this->flash->addMessage('danger', 'Подаци за корисника нису исправни.');
            return $response->withRedirect($this->router->pathFor('prijava'));
        }
    }

    public function getOdjava($request, $response)
    {
        $this->auth->logout();
        return $response->withRedirect($this->router->pathFor('pocetna'));
    }

    /*
        FIXME: REGISTRACIJA NIJE KOMPLETNA - NE KORISTITI !!!
    */
    public function getRegistracija($request, $response)
    {
        $this->render($response, 'auth/registracija.twig');
    }

    public function postRegistracija($request, $response)
    {
        $data = $request->getParams();
        $validation_rules = [
            'ime' => [
                'required' => true,
                'minlen' => 5,
                'alnum' => true,
            ],
            'korisnicko_ime' => [
                'required' => true,
                'minlen' => 3,
                'maxlen' => 50,
                'alnum' => true,
                'unique' => 'korisnici.korisnicko_ime', // tabela.kolona
            ],
            'lozinka' => [
                'required' => true,
                'minlen' => 6,
            ],
            'potvrda_lozinke' => [
                'match_field' => 'lozinka',
            ],
        ];
        $this->validator->validate($data, $validation_rules);
        die();
        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Дошло је до грешке приликом регистрације корисника.');
            return $response->withRedirect($this->router->pathFor('registracija'));
        } else {
            // TODO: Upisati novog korisnika
            $this->flash->addMessage('success', 'Нови корисник је успешно регистрован.');
            return $response->withRedirect($this->router->pathFor('prijava'));
        }
    }

    public function getPromena($request, $response)
    {
        $this->render($response, 'auth/promena.twig');
    }

    public function postPromena($request, $response)
    {
        $data = $request->getParams();

        $validation_rules = [
            'lozinka' => [
                'required' => true
            ],
            'nova_lozinka' => [
                'required' => true,
                'minlen' => 6,
            ],
            'lozinka_potvrda' => [
                'required' => true,
                'match_field' => 'nova_lozinka',
            ],
        ];

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Дошло је до грешке приликом измене лозинке.');
            return $response->withRedirect($this->router->pathFor('promena'));
        } else {
            $prijavljeni_korisnik = $this->auth->user();
            $dobra_lozinka = $this->auth->checkPassword($data['lozinka'], $prijavljeni_korisnik->lozinka);
            if ($dobra_lozinka) {
                $novi_hash = password_hash($data['nova_lozinka'], PASSWORD_DEFAULT);
                $prijavljeni_korisnik->update(['lozinka' => $novi_hash], $prijavljeni_korisnik->id);
                $this->log(Logger::IZMENA, $prijavljeni_korisnik, 'korisnicko_ime');
                $this->flash->addMessage('success', 'Лозинка је успешно измењена. Молимо вас да се поново пријавите са новом лозинком.');
                $this->auth->logout();
                return $response->withRedirect($this->router->pathFor('prijava'));
            } else {
                $this->flash->addMessage('danger', 'Дошло је до грешке приликом измене лозинке. Молимо вас да се пријавите са старом лозинком.');
                $this->auth->logout();
                return $response->withRedirect($this->router->pathFor('prijava'));
            }
        }
    }
}
