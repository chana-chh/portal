<?php

use App\Middlewares\AuthMiddleware;
use App\Middlewares\GuestMiddleware;
use App\Middlewares\UserLevelMiddleware;

$app->group('', function () {
    $this->get('/', '\App\Controllers\HomeController:getHome')->setName('pocetna');
    $this->get('/prijava', '\App\Controllers\AuthController:getPrijava')->setName('prijava');
    $this->post('/prijava', '\App\Controllers\AuthController:postPrijava');
})->add(new GuestMiddleware($container));

$app->group('', function () {
    $this->get('/odjava', '\App\Controllers\AuthController:getOdjava')->setName('odjava');

    // Promena lozinke
    $this->get('/promena', '\App\Controllers\AuthController:getPromena')->setName('promena');
    $this->post('/promena', '\App\Controllers\AuthController:postPromena')->setName('promena');
})->add(new AuthMiddleware($container));

// ADMIN
$app->group('', function () {
    // Korisnici
    $this->get('/admin/korisnik-lista', '\App\Controllers\KorisnikController:getKorisnikLista')->setName('admin.korisnik.lista');
    $this->post('/admin/korisnik-brisanje', '\App\Controllers\KorisnikController:postKorisnikBrisanje')->setName('admin.korisnik.brisanje');
    $this->post('/admin/korisnik-dodavanje', '\App\Controllers\KorisnikController:postKorisnikDodavanje')->setName('admin.korisnik.dodavanje');
    $this->get('/admin/korisnik-izmena/{id}', '\App\Controllers\KorisnikController:getKorisnikIzmena')->setName('admin.korisnik.izmena.get');
    $this->post('/admin/korisnik-izmena', '\App\Controllers\KorisnikController:postKorisnikIzmena')->setName('admin.korisnik.izmena.post');
    //Logovi
    $this->get('/admin/logovi', '\App\Controllers\LogController:getLog')->setName('logovi');
    $this->get('/admin/logovi/pretraga', '\App\Controllers\LogController:getLogPretraga')->setName('logovi.pretraga');
    $this->post('/admin/logovi/pretraga', '\App\Controllers\LogController:postLogPretraga');
})->add(new UserLevelMiddleware($container, [0]));

// ZAKAZIVACI
$app->group('', function () {
    // Clanci-objave
})->add(new UserLevelMiddleware($container, [0,100]));
