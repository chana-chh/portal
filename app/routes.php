<?php

use App\Middlewares\AuthMiddleware;
use App\Middlewares\GuestMiddleware;
use App\Middlewares\UserLevelMiddleware;

$app->get('/', '\App\Controllers\HomeController:getHome')->setName('pocetna');
$app->get('/izrada', '\App\Controllers\HomeController:getIzrada')->setName('izrada');

//Clanci
$app->get('/clanci/pregled/{id}', '\App\Controllers\ClanakController:getPregled')->setName('clanci.pregled');
$app->get('/clanci/tabela/{id}', '\App\Controllers\ClanakController:getTabelaKategorije')->setName('clanci.tabela.kategorije');
$app->get('/clanci/arhiva/{mm}/{yy}', '\App\Controllers\ClanakController:getArhiva')->setName('clanci.arhiva');
$app->get('/clanci/lista', '\App\Controllers\ClanakController:getLista')->setName('clanci.lista');
$app->get('/clanci/pretraga', '\App\Controllers\ClanakController:getClanciPretraga')->setName('clanci.pretraga');
$app->post('/clanci/pretraga', '\App\Controllers\ClanakController:postClanciPretraga');
$app->get('/clanci/komentar/{id}', '\App\Controllers\ClanakController:getKomentar')->setName('clanci.komentar.get');
$app->post('/clanci/komentar', '\App\Controllers\ClanakController:postKomentar')->setName('clanci.komentar.post');
//Doks
$app->get('/dokumenti/lista[/{id_kat}[/{id_vrs}]]', '\App\Controllers\DokumentController:getLista')->setName('dokumenti.lista');
$app->get('/dokumenti/pretraga', '\App\Controllers\DokumentController:getDokumentiPretraga')->setName('dokumenti.pretraga');
$app->post('/dokumenti/pretraga', '\App\Controllers\DokumentController:postDokumentiPretraga');
$app->get('/dokumenti/arhiva[/{id_kat}[/{id_vrs}]]', '\App\Controllers\DokumentController:getArhiva')->setName('dokumenti.arhiva');
$app->get('/dokumenti/arhiva-pretraga', '\App\Controllers\DokumentController:getDokumentiArhivaPretraga')->setName('dokumenti.arhiva.pretraga');
$app->post('/dokumenti/arhiva-pretraga', '\App\Controllers\DokumentController:postDokumentiArhivaPretraga');
//Obuke
$app->get('/obuka/lista[/{id_kat}]', '\App\Controllers\BibliotekaObukaController:getLista')->setName('obuka.lista');
$app->get('/obuka/pretraga', '\App\Controllers\BibliotekaObukaController:getDokumentiPretraga')->setName('obuka.pretraga');
$app->post('/obuka/pretraga', '\App\Controllers\BibliotekaObukaController:postDokumentiPretraga');
$app->get('/obuka/materijal/{id}', '\App\Controllers\BibliotekaObukaController:getMaterijal')->setName('obuka.materijal');

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
	// Obuke
    $this->get('/admin/obuka/dodavanje', '\App\Controllers\BibliotekaObukaController:getDokumentDodavanje')->setName('obuka.dodavanje.get');
    $this->post('/admin/obuka/dodavanje', '\App\Controllers\BibliotekaObukaController:postDokumentDodavanje')->setName('obuka.dodavanje.post');
    $this->post('/admin/obuka/brisanje', '\App\Controllers\BibliotekaObukaController:postDokumentiBrisanje')->setName('obuka.brisanje');
    $this->get('/admin/obuka/izmena/{id}', '\App\Controllers\BibliotekaObukaController:getDokumentiIzmena')->setName('obuka.detalj');
    $this->post('/admin/obuka/izmena', '\App\Controllers\BibliotekaObukaController:postDokumentiIzmena')->setName('obuka.izmena');

    // Materijal
    $this->get('/materijal', '\App\Controllers\BibliotekaDokumentController:getLista')->setName('materijal');
    $this->get('/materijal/pretraga', '\App\Controllers\BibliotekaDokumentController:getPretraga')->setName('materijal.pretraga');
    $this->post('/materijal/pretraga', '\App\Controllers\BibliotekaDokumentController:postPretraga');
    $this->get('/materijal/dodavanje', '\App\Controllers\BibliotekaDokumentController:getDodavanje')->setName('materijal.dodavanje.get');
    $this->post('/materijal/dodavanje', '\App\Controllers\BibliotekaDokumentController:postDodavanje')->setName('materijal.dodavanje.post');
    $this->post('/materijal/brisanje', '\App\Controllers\BibliotekaDokumentController:postBrisanje')->setName('materijal.brisanje');
    $this->get('/materijal/izmena/{id}', '\App\Controllers\BibliotekaDokumentController:getIzmena')->setName('materijal.izmena.get');
    $this->post('/materijal/izmena', '\App\Controllers\BibliotekaDokumentController:postIzmena')->setName('materijal.izmena.post');

    // Komentari
    $this->get('/admin/komentari', '\App\Controllers\KomentariController:getLista')->setName('komentari');
    $this->get('/admin//komentari/pretraga', '\App\Controllers\KomentariController:getKomentariPretraga')->setName('komentari.pretraga');
    $this->post('/admin//komentari/pretraga', '\App\Controllers\KomentariController:postKomentariPretraga');
    $this->get('/admin/pregled/{id}', '\App\Controllers\KomentariController:getPregled')->setName('komentari.pregled');
    $this->get('/admin/objava/{id}', '\App\Controllers\KomentariController:getObjava')->setName('komentari.objava');
    // Clanci Kategorije
    $this->get('/admin/kategorije', '\App\Controllers\KategorijaController:getKategorije')->setName('kategorija');
    $this->post('/admin/kategorije/dodavanje', '\App\Controllers\KategorijaController:postKategorijeDodavanje')->setName('kategorija.dodavanje');
    $this->post('/admin/kategorije/brisanje', '\App\Controllers\KategorijaController:postKategorijeBrisanje')->setName('kategorija.brisanje');
    $this->post('/admin/kategorije/detalj', '\App\Controllers\KategorijaController:postKategorijeDetalj')->setName('kategorija.detalj');
    $this->post('/admin/kategorije/izmena', '\App\Controllers\KategorijaController:postKategorijeIzmena')->setName('kategorija.izmena');
    // Dokumenti vrste
    $this->get('/admin/vrste', '\App\Controllers\DokumentVrsteController:getVrste')->setName('vrste');
    $this->post('/admin/vrste/dodavanje', '\App\Controllers\DokumentVrsteController:postVrsteDodavanje')->setName('vrste.dodavanje');
    $this->post('/admin/vrste/brisanje', '\App\Controllers\DokumentVrsteController:postVrsteBrisanje')->setName('vrste.brisanje');
    $this->post('/admin/vrste/detalj', '\App\Controllers\DokumentVrsteController:postVrsteDetalj')->setName('vrste.detalj');
    $this->post('/admin/vrste/izmena', '\App\Controllers\DokumentVrsteController:postVrsteIzmena')->setName('vrste.izmena');
    // Dokumenti Kategorije
    $this->get('/admin/dokukategorije[/{poslednji}]', '\App\Controllers\DokumentKatController:getKategorije')->setName('dokument.kategorija');
    $this->post('/admin/dokukategorije/dodavanje', '\App\Controllers\DokumentKatController:postKategorijeDodavanje')->setName('dokument.kategorija.dodavanje');
    $this->post('/admin/dokukategorije/brisanje', '\App\Controllers\DokumentKatController:postKategorijeBrisanje')->setName('dokument.kategorija.brisanje');
    $this->get('/admin/dokukategorije/izmena/{id}', '\App\Controllers\DokumentKatController:getKategorijeIzmena')->setName('dokument.kategorija.detalj');
    $this->post('/admin/dokukategorije/izmena', '\App\Controllers\DokumentKatController:postKategorijeIzmena')->setName('dokument.kategorija.izmena');
    // Korisnici
    $this->get('/admin/korisnik-lista', '\App\Controllers\KorisnikController:getKorisnikLista')->setName('admin.korisnik.lista');
    $this->post('/admin/korisnik-brisanje', '\App\Controllers\KorisnikController:postKorisnikBrisanje')->setName('admin.korisnik.brisanje');
    $this->get('/admin/korisnik-dodavanje', '\App\Controllers\KorisnikController:getKorisnikDodavanje')->setName('admin.korisnik.get');
    $this->post('/admin/korisnik-dodavanje', '\App\Controllers\KorisnikController:postKorisnikDodavanje')->setName('admin.korisnik.dodavanje');
    $this->get('/admin/korisnik-izmena/{id}', '\App\Controllers\KorisnikController:getKorisnikIzmena')->setName('admin.korisnik.izmena.get');
    $this->post('/admin/korisnik-izmena', '\App\Controllers\KorisnikController:postKorisnikIzmena')->setName('admin.korisnik.izmena.post');

	// Biblioteka vrste
    $this->get('/admin/bib/vrste', '\App\Controllers\BibliotekaVrsteController:getLista')->setName('bib.vrste');
    $this->post('/admin/bib/vrste/dodavanje', '\App\Controllers\BibliotekaVrsteController:postDodavanje')->setName('bib.vrste.dodavanje');
    $this->post('/admin/bib/vrste/brisanje', '\App\Controllers\BibliotekaVrsteController:postBrisanje')->setName('bib.vrste.brisanje');
    $this->post('/admin/bib/vrste/detalj', '\App\Controllers\BibliotekaVrsteController:postDetalj')->setName('bib.vrste.detalj');
    $this->post('/admin/bib/vrste/izmena', '\App\Controllers\BibliotekaVrsteController:postIzmena')->setName('bib.vrste.izmena');

    // Biblioteka program
    $this->get('/admin/bib/program', '\App\Controllers\BibliotekaProgramController:getLista')->setName('bib.program');
    $this->post('/admin/bib/program/dodavanje', '\App\Controllers\BibliotekaProgramController:postDodavanje')->setName('bib.program.dodavanje');
    $this->post('/admin/bib/program/brisanje', '\App\Controllers\BibliotekaProgramController:postBrisanje')->setName('bib.program.brisanje');
    $this->post('/admin/bib/program/detalj', '\App\Controllers\BibliotekaProgramController:postDetalj')->setName('bib.program.detalj');
    $this->post('/admin/bib/program/izmena', '\App\Controllers\BibliotekaProgramController:postIzmena')->setName('bib.program.izmena');

    // Biblioteka Kategorije
    $this->get('/admin/bib/kategorije[/{poslednji}]', '\App\Controllers\BibliotekaKatController:getKategorije')->setName('bib.kategorija');
    $this->post('/admin/bib/kategorije/dodavanje', '\App\Controllers\BibliotekaKatController:postKategorijeDodavanje')->setName('bib.kategorija.dodavanje');
    $this->post('/admin/bib/kategorije/brisanje', '\App\Controllers\BibliotekaKatController:postKategorijeBrisanje')->setName('bib.kategorija.brisanje');
    $this->get('/admin/bib/kategorije/izmena/{id}', '\App\Controllers\BibliotekaKatController:getKategorijeIzmena')->setName('bib.kategorija.detalj');
    $this->post('/admin/bib/kategorije/izmena', '\App\Controllers\BibliotekaKatController:postKategorijeIzmena')->setName('bib.kategorija.izmena');

    //Logovi
    $this->get('/admin/logovi', '\App\Controllers\LogController:getLog')->setName('logovi');
    $this->get('/admin/logovi/pretraga', '\App\Controllers\LogController:getLogPretraga')->setName('logovi.pretraga');
    $this->post('/admin/logovi/pretraga', '\App\Controllers\LogController:postLogPretraga');
})->add(new UserLevelMiddleware($container, [0]));

// AUTORI
$app->group('', function () {
    $this->get('/uputstvo', '\App\Controllers\HomeController:getHelp')->setName('uputstvo');
    // Objave
    $this->get('/autor/objave/lista', '\App\Controllers\AutorObjaveController:getObjaveLista')->setName('autor.objave.lista');
    $this->get('/autor/objave/dodavanje', '\App\Controllers\AutorObjaveController:getObjaveDodavanje')->setName('autor.objave.dodavanje');
    $this->post('/autor/objave/dodavanje', '\App\Controllers\AutorObjaveController:postObjaveDodavanje')->setName('autor.objave.dodavanje');
    // Dokumenti
    $this->get('/autor/dokument/dodavanje', '\App\Controllers\DokumentController:getDokumentDodavanje')->setName('dokument.dodavanje.get');
    $this->post('/autor/dokument/dodavanje', '\App\Controllers\DokumentController:postDokumentDodavanje')->setName('dokument.dodavanje.post');
    $this->post('/autor/dokument/brisanje', '\App\Controllers\DokumentController:postDokumentiBrisanje')->setName('dokument.brisanje');
    $this->get('/autor/dokument/izmena/{id}', '\App\Controllers\DokumentController:getDokumentiIzmena')->setName('dokument.detalj');
    $this->post('/autor/dokument/izmena', '\App\Controllers\DokumentController:postDokumentiIzmena')->setName('dokument.izmena');
    $this->get('/autor/dokument/linkovanje/{id}', '\App\Controllers\DokumentController:getDokumentiLink')->setName('dokument.link.get');
    $this->post('/autor/dokument/linkovanje', '\App\Controllers\DokumentController:postDokumentiLink')->setName('dokument.link.post');
    $this->get('/autor/dokument/arhiviranje/{id}', '\App\Controllers\DokumentController:getDokumentiArhiviranje')->setName('dokument.arhiviranje.get');
    $this->post('/autor/dokument/arhiviranje', '\App\Controllers\DokumentController:postDokumentiArhiviranje')->setName('dokument.arhiviranje.post');
    // Anketa
    $this->get('/ankete/lista', '\App\Controllers\AnketaController:getLista')->setName('ankete');
    $this->get('/ankete/pretraga', '\App\Controllers\AnketaController:getPretraga')->setName('ankete.pretraga');
    $this->post('/ankete/pretraga', '\App\Controllers\AnketaController:postPretraga');
    $this->get('/ankete/pregled/{id}', '\App\Controllers\AnketaController:getPregled')->setName('ankete.pregled');
    $this->get('/ankete/uporedni/{id}', '\App\Controllers\AnketaController:getUporedni')->setName('ankete.uporedni');
    $this->get('/anketa/dodavanje', '\App\Controllers\AnketaController:getDodavanje')->setName('anketa.dodavanje.get');
    $this->post('/anketa/dodavanje', '\App\Controllers\AnketaController:postDodavanje')->setName('anketa.dodavanje.post');
    $this->get('/anketa/izmena/{id}', '\App\Controllers\AnketaController:getIzmena')->setName('anketa.izmena.get');
    $this->post('/anketa/izmena', '\App\Controllers\AnketaController:postIzmena')->setName('anketa.izmena');
    $this->post('/anketa/brisanje', '\App\Controllers\AnketaController:postBrisanje')->setName('anketa.brisanje');
        // Ispitanici
    $this->get('/ankete/ispitanik', '\App\Controllers\AnketaIspitanikController:getLista')->setName('ispitanik');
    $this->post('/ankete/ispitanik/dodavanje', '\App\Controllers\AnketaIspitanikController:postDodavanje')->setName('ispitanik.dodavanje');
    $this->post('/ankete/ispitanik/brisanje', '\App\Controllers\AnketaIspitanikController:postBrisanje')->setName('ispitanik.brisanje');
    $this->post('/ankete/ispitanik/detalj', '\App\Controllers\AnketaIspitanikController:postDetalj')->setName('ispitanik.detalj');
    $this->post('/ankete/ispitanik/izmena', '\App\Controllers\AnketaIspitanikController:postIzmena')->setName('ispitanik.izmena');
     // Tip odgovora
    $this->get('/ankete/tip-odgovora', '\App\Controllers\AnketaTodgovoraController:getLista')->setName('tip-odgovora');
    $this->post('/ankete/tip-odgovora/dodavanje', '\App\Controllers\AnketaTodgovoraController:postDodavanje')->setName('tip-odgovora.dodavanje');
    $this->post('/ankete/tip-odgovora/brisanje', '\App\Controllers\AnketaTodgovoraController:postBrisanje')->setName('tip-odgovora.brisanje');
    $this->post('/ankete/tip-odgovora/detalj', '\App\Controllers\AnketaTodgovoraController:postDetalj')->setName('tip-odgovora.detalj');
    $this->post('/ankete/tip-odgovora/izmena', '\App\Controllers\AnketaTodgovoraController:postIzmena')->setName('tip-odgovora.izmena');
    // Tip ankete
    $this->get('/ankete/tip', '\App\Controllers\AnketaTipController:getLista')->setName('tip');
    $this->post('/ankete/tip/dodavanje', '\App\Controllers\AnketaTipController:postDodavanje')->setName('tip.dodavanje');
    $this->post('/ankete/tip/brisanje', '\App\Controllers\AnketaTipController:postBrisanje')->setName('tip.brisanje');
    $this->post('/ankete/tip/detalj', '\App\Controllers\AnketaTipController:postDetalj')->setName('tip.detalj');
    $this->post('/ankete/tip/izmena', '\App\Controllers\AnketaTipController:postIzmena')->setName('tip.izmena');
    // Anketa odgvorovi
    $this->get('/ankete/izbor', '\App\Controllers\AnketaOdgovorController:getIzbor')->setName('izbor');
    $this->get('/ankete/popunjavanje/{id}', '\App\Controllers\AnketaOdgovorController:getPopunjavanje')->setName('popunjavanje');
    $this->post('/ankete/slanje', '\App\Controllers\AnketaOdgovorController:postSlanjeOdgovora')->setName('slanje');
    $this->get('/ankete/odgovori/{id_ankete}/{id_ispitanika}', '\App\Controllers\AnketaOdgovorController:getOdgovoriIspitanika')->setName('odgovori');
    // Anketa dokumenta
    $this->post('/anketa/dokumenta/brisanje', '\App\Controllers\AnketaDokController:postBrisanje')->setName('dokankete.brisanje');
    // Anketa pitanja
    $this->post('/ankete/pitanja/dodavanje', '\App\Controllers\AnketaPitanjeController:postDodavanje')->setName('pitanje.dodavanje');
    $this->get('/anketa/pitanja/izmena/{id}', '\App\Controllers\AnketaPitanjeController:getIzmena')->setName('pitanje.izmena.get');
    $this->post('/anketa/pitanja/izmena', '\App\Controllers\AnketaPitanjeController:postIzmena')->setName('pitanje.izmena');
    $this->post('/anketa/pitanja/brisanje', '\App\Controllers\AnketaPitanjeController:postBrisanje')->setName('pitanje.brisanje');
})->add(new UserLevelMiddleware($container, [0,100]));
