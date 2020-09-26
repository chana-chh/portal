<?php

use App\Middlewares\AuthMiddleware;
use App\Middlewares\GuestMiddleware;
use App\Middlewares\UserLevelMiddleware;

$app->get('/', '\App\Controllers\HomeController:getHome')->setName('pocetna');
$app->get('/feed', '\App\Controllers\ClanakController:getFeed')->setName('feed');
$app->get('/feed/pretraga', '\App\Controllers\ClanakController:getClanciPretraga')->setName('clanci.pretraga');
$app->post('/feed/pretraga', '\App\Controllers\ClanakController:postClanciPretraga');
//Clanci
$app->post('/clanci/pregled', '\App\Controllers\ClanakController:postPregled')->setName('clanci.pregled');
$app->get('/clanci/lista/{id}', '\App\Controllers\ClanakController:getLista')->setName('clanci.lista');
$app->get('/clanci/arhiva/{mm}/{yy}', '\App\Controllers\ClanakController:getArhiva')->setName('clanci.arhiva');


$app->group('', function () {
    //Prijava
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
    // Kategorije
    $this->get('/admin/kategorije', '\App\Controllers\KategorijaController:getKategorije')->setName('kategorija');
    $this->post('/admin/kategorije/dodavanje', '\App\Controllers\KategorijaController:postKategorijeDodavanje')->setName('kategorija.dodavanje');
    $this->post('/admin/kategorije/brisanje', '\App\Controllers\KategorijaController:postKategorijeBrisanje')->setName('kategorija.brisanje');
    $this->post('/admin/kategorije/detalj', '\App\Controllers\KategorijaController:postKategorijeDetalj')->setName('kategorija.detalj');
    $this->post('/admin/kategorije/izmena', '\App\Controllers\KategorijaController:postKategorijeIzmena')->setName('kategorija.izmena');
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

// AUTORI
$app->group('', function () {
    // Dokumenta
    

    // Objave
    $this->get('/autor/objave/lista', '\App\Controllers\AutorObjaveController:getObjaveLista')->setName('autor.objave.lista');
    $this->get('/autor/objave/dodavanje', '\App\Controllers\AutorObjaveController:getObjaveDodavanje')->setName('autor.objave.dodavanje');
    $this->post('/autor/objave/dodavanje', '\App\Controllers\AutorObjaveController:postObjaveDodavanje')->setName('autor.objave.dodavanje');
    // Dokumenti
})->add(new UserLevelMiddleware($container, [0,100]));
