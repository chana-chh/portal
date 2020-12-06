<?php

namespace App\Controllers;

use App\Models\DokumentKategorija;
use App\Classes\Logger;

class DokumentKatController extends Controller
{
    public function getKategorije($request, $response, $args)
    {
        $model = new DokumentKategorija();
        $data = $model->getListNS();
        $id_poslednjeg = null;
        if (!empty($args)) {

            if (isset($args['poslednji'])) {
                $id_poslednjeg = (int) $args['poslednji'];
                $roditelji = $model->getWithParentsNS($id_poslednjeg);
                $roditelji_id = array_column($roditelji, 'id');
                array_pop($roditelji_id);
                json_encode($roditelji_id);
            }else{
                $roditelji_id = null;
            }
        }
        $this->render($response, 'dokkategorije/lista.twig', compact('data', 'roditelji_id', 'id_poslednjeg'));
    }

    public function postKategorijeDodavanje($request, $response)
    {
        $data = $this->data();

        if ($data['modal'] == 1) {
            $data = [
            "naziv" => $data['dnModal'],
            "parent_id" => $data['parentModal'],
            ];
        }


        $validation_rules = [
            'naziv' => [
                'required' => true,
                'minlen' => 4,
                'maxlen' => 255,
                'multi_unique' => 'dokumenti_kategorije.naziv,parent_id'
            ],
            'korisnik_id' => [
                'required' => true,
            ]
        ];

        $data['korisnik_id'] = $this->auth->user()->id;
        unset($data['modal']);

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Дошло је до грешке приликом додавања категорије ДОКУМЕНТА.');
            return $response->withRedirect($this->router->pathFor('dokument.kategorija'));
        } else {
            $this->flash->addMessage('success', 'Нова категорија ДОКУМЕНТА је успешно додата.');
            $model = new DokumentKategorija();
            $poslednji  = $model->insertNS($data['parent_id'], $data['naziv'], $data['korisnik_id']);
            $kategorija = $model->find($poslednji);
            $this->log($this::DODAVANJE, $kategorija, 'naziv');
            return $response->withRedirect($this->router->pathFor('dokument.kategorija', ['poslednji' => $kategorija->id]));
        }
    }

    public function postKategorijeBrisanje($request, $response)
    {
        $id = (int)$request->getParam('idBrisanje');
        $model = new DokumentKategorija();
        $kategorija = $model->find($id);
        $success = $model->deleteOne($id);

        if ($success) {
            $this->flash->addMessage('success', "Категорија ДОКУМЕНТА је успешно обрисана.");
            $this->log($this::BRISANJE, $kategorija, 'naziv', $kategorija);
            return $response->withRedirect($this->router->pathFor('dokument.kategorija'));
        } else {
            $this->flash->addMessage('danger', "Дошло је до грешке приликом брисања категорије ДОКУМЕНТА.");
            return $response->withRedirect($this->router->pathFor('dokument.kategorija'));
        }
    }

    public function getKategorijeIzmena($request, $response, $args)
    {
        $id = (int) $args['id'];
        $model = new DokumentKategorija();
        $kategorija = $model->find($id);

        $kategorije = $model->getFlatListNS();

        $sql = "SELECT MAX(level) AS maks FROM {$model->getTable()};";
        $nivo_query = $model->fetch($sql);
        $nivo = $nivo_query[0]->maks;

        $sql = "SELECT MAX(position) AS maks FROM {$model->getTable()};";
        $pozicija_query = $model->fetch($sql);
        $pozicija = $pozicija_query[0]->maks;

        $roditelji = $model->getWithParentsNS($id);
        $roditelji_nazivi = array_column($roditelji, 'naziv');
        array_pop($roditelji_nazivi);
        $putanja = implode("\\", $roditelji_nazivi);

        $this->render($response, 'dokkategorije/izmena.twig', compact('kategorija', 'putanja', 'kategorije', 'nivo', 'pozicija'));
    }

    public function postKategorijeIzmena($request, $response)
    {

        $data = $this->data();
        $id = $data['idIzmena'];
        $roditelj = $data['parent_id'];
        $pozicija = $data['position'];

        unset($data['idIzmena']);

        $model = new DokumentKategorija();
        $stari = $model->find($id);

        $validation_rules = [
            'naziv' => [
                'required' => true,
                'minlen' => 4,
                'maxlen' => 50,
                'multi_unique' => 'dokumenti_kategorije.naziv,parent_id#id:' . $id
            ]
        ];

        $sql = "SELECT MAX(position) AS maks FROM {$model->getTable()} WHERE parent_id = {$data['parent_id']};";
        $pozicija_query = $model->fetch($sql);
        $pozicija_maks = $pozicija_query[0]->maks;

        if ($stari->parent_id != $data['parent_id']) {

            if ($model->isParentUnderMyChildren($id, $data['parent_id'])) {
                $this->flash->addMessage('danger', 'Дошло је до грешке приликом премештања категорије. Немогуће премештање у сопствену поткатегорију!');
                return $response->withRedirect($this->router->pathFor('dokument.kategorija'));
            }else{
                if (((int) $pozicija) > $pozicija_maks) {
                    $data['position'] = $pozicija_maks+1;

                    $this->validator->validate($data, $validation_rules);
                    if ($this->validator->hasErrors()) {
                        $this->flash->addMessage('danger', 'Дошло је до грешке приликом измене података категорије ДОКУМЕНТА.');
                        return $response->withRedirect($this->router->pathFor('dokument.kategorija'));
                    } else {
                    $model->update($data, $id);
                    $model->rebuild();
                    $this->flash->addMessage('success', 'Подаци категорије ДОКУМЕНТА су успешно измењени.');
                    $kategorija = $model->find($id);
                    $this->log($this::IZMENA, $kategorija, 'naziv', $stari);
                    return $response->withRedirect($this->router->pathFor('dokument.kategorija', ['poslednji' => $id]));
                    }
                }else{
                    $this->validator->validate($data, $validation_rules);
                    if ($this->validator->hasErrors()) {
                        $this->flash->addMessage('danger', 'Дошло је до грешке приликом измене података категорије ДОКУМЕНТА.');
                        return $response->withRedirect($this->router->pathFor('dokument.kategorija'));
                    } else {
                        $model->update($data, $id);
                        $sqla = "UPDATE {$model->getTable()} SET position = position + 1
                                WHERE parent_id = $roditelj
                                AND position >= $pozicija
                                AND id != $id;";

                        $preraspodela_pozicija = $model->fetch($sqla);
                        $model->rebuild();
                        $this->flash->addMessage('success', 'Подаци категорије ДОКУМЕНТА су успешно измењени.');
                        $kategorija = $model->find($id);
                        $this->log($this::IZMENA, $kategorija, 'naziv', $stari);
                        return $response->withRedirect($this->router->pathFor('dokument.kategorija', ['poslednji' => $id]));
                    }
                }
            }
        }elseif ($stari->parent_id == $data['parent_id'] && $stari->position != $data['position']) {
                // Slučaj kada se menja --POZICIJA i (naziv)-- kategorije
                unset($data['parent_id']);

                if (((int) $pozicija) >= $pozicija_maks) {

                    // Kada je nova pozicija veće ili jednaka MAKSIMALNOJ u roditeljskoj kategoriji
                    $data['position'] = $pozicija_maks+1;

                    $this->validator->validate($data, $validation_rules);
                    if ($this->validator->hasErrors()) {
                        $this->flash->addMessage('danger', 'Дошло је до грешке приликом измене података категорије ДОКУМЕНТА.');
                        return $response->withRedirect($this->router->pathFor('dokument.kategorija'));
                    } else {
                    $model->update($data, $id);

                    $sqlb = "SELECT * FROM {$model->getTable()} WHERE parent_id = $roditelj ORDER BY position;";
                    $za_nove_pozicije = $model->fetch($sqlb);

                    unset($data['naziv']);
                    $index = 1;
                    foreach($za_nove_pozicije as $kat) {
                        $data['position'] = $index;
                        $model->update($data, $kat->id);
                        $index++;
                    };
                    $model->rebuild();
                    $this->flash->addMessage('success', 'Подаци категорије ДОКУМЕНТА су успешно измењени.');
                    $kategorija = $model->find($id);
                    $this->log($this::IZMENA, $kategorija, 'naziv', $stari);
                    return $response->withRedirect($this->router->pathFor('dokument.kategorija', ['poslednji' => $id]));
                    }
                }elseif(((int) $pozicija) > $stari->position){
                    // Kada je nova pozicija VEĆA od stare u roditeljskoj kategoriji

                    unset($data['parent_id']);
                    $data['position'] = $pozicija;

                    $this->validator->validate($data, $validation_rules);
                    if ($this->validator->hasErrors()) {
                        $this->flash->addMessage('danger', 'Дошло је до грешке приликом измене података категорије ДОКУМЕНТА.');
                        return $response->withRedirect($this->router->pathFor('dokument.kategorija'));
                    } else {
                        $model->update($data, $id);
                        $sqla = "UPDATE {$model->getTable()} SET position = position - 1
                                WHERE parent_id = $roditelj
                                AND position > {$stari->position}
                                AND position <= $pozicija
                                AND id != $id;";

                        $preraspodela_pozicija = $model->fetch($sqla);
                        $model->rebuild();
                    $this->flash->addMessage('success', 'Подаци категорије ДОКУМЕНТА су успешно измењени.');
                    $kategorija = $model->find($id);
                    $this->log($this::IZMENA, $kategorija, 'naziv', $stari);
                    return $response->withRedirect($this->router->pathFor('dokument.kategorija', ['poslednji' => $id]));}
                }else{
                    // Kada je nova pozicija MANJA od stare u roditeljskoj kategoriji
                    unset($data['parent_id']);
                    $data['position'] = $pozicija;

                    $this->validator->validate($data, $validation_rules);
                    if ($this->validator->hasErrors()) {
                        $this->flash->addMessage('danger', 'Дошло је до грешке приликом измене података категорије ДОКУМЕНТА.');
                        return $response->withRedirect($this->router->pathFor('dokument.kategorija'));
                    } else {
                        $model->update($data, $id);
                        $sqlc = "UPDATE {$model->getTable()} SET position = position + 1
                                WHERE parent_id = $roditelj
                                AND position >= $pozicija
                                AND position < {$stari->position}
                                AND id != $id;";
                        $preraspodela_pozicija = $model->fetch($sqlc);
                        $model->rebuild();
                    $this->flash->addMessage('success', 'Подаци категорије ДОКУМЕНТА су успешно измењени.');
                    $kategorija = $model->find($id);
                    $this->log($this::IZMENA, $kategorija, 'naziv', $stari);
                    return $response->withRedirect($this->router->pathFor('dokument.kategorija', ['poslednji' => $id]));}
                }
        }else{
            // Slučaj kada se menja samo --NAZIV-- kategorije
            unset($data['parent_id']);
            unset($data['position']);
            $this->validator->validate($data, $validation_rules);

            if ($this->validator->hasErrors()) {
                $this->flash->addMessage('danger', 'Дошло је до грешке приликом измене назива категорије ДОКУМЕНТА.');
                return $response->withRedirect($this->router->pathFor('dokument.kategorija'));
            } else {
                $this->flash->addMessage('success', 'Назив категорије ДОКУМЕНТА је успешно измењен.');
                $model->update($data, $id);
                $kategorija = $model->find($id);
                $this->log($this::IZMENA, $kategorija, 'naziv', $stari);
                return $response->withRedirect($this->router->pathFor('dokument.kategorija', ['poslednji' => $id]));
            }
        }
    }
}
