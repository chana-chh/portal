<?php

use App\Middlewares\AuthMiddleware;
use App\Middlewares\GuestMiddleware;
use App\Middlewares\UserLevelMiddleware;

$app->get('/', '\App\Controllers\HomeController:getHome')->setName('pocetna');

//Clanci
$app->get('/clanci/pregled/{id}', '\App\Controllers\ClanakController:getPregled')->setName('clanci.pregled');
$app->get('/clanci/tabela/{id}', '\App\Controllers\ClanakController:getTabelaKategorije')->setName('clanci.tabela.kategorije');
$app->get('/clanci/arhiva/{mm}/{yy}', '\App\Controllers\ClanakController:getArhiva')->setName('clanci.arhiva');
$app->get('/clanci/lista', '\App\Controllers\ClanakController:getLista')->setName('clanci.lista');
$app->get('/clanci/pretraga', '\App\Controllers\ClanakController:getClanciPretraga')->setName('clanci.pretraga');
$app->post('/clanci/pretraga', '\App\Controllers\ClanakController:postClanciPretraga');
$app->get('/clanci/komentar/{id}', '\App\Controllers\ClanakController:getKomentar')->setName('clanci.komentar.get');
$app->post('/clanci/komentar', '\App\Controllers\ClanakController:postKomentar')->setName('clanci.komentar.post');

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
    // Komentari
    $this->get('/admin/komentari', '\App\Controllers\KomentariController:getLista')->setName('komentari');
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
    // Objave
    $this->get('/autor/objave/lista', '\App\Controllers\AutorObjaveController:getObjaveLista')->setName('autor.objave.lista');
    $this->get('/autor/objave/dodavanje', '\App\Controllers\AutorObjaveController:getObjaveDodavanje')->setName('autor.objave.dodavanje');
    $this->post('/autor/objave/dodavanje', '\App\Controllers\AutorObjaveController:postObjaveDodavanje')->setName('autor.objave.dodavanje');
    // Dokumenti
})->add(new UserLevelMiddleware($container, [0,100]));
