<?php

namespace App\Controllers;

use App\Models\Anketa;
use App\Models\AnketaOdgovor;
use App\Models\AnketaIspitanik;
use App\Models\AnketaDokument;

class AnketaOdgovorController extends Controller
{

    public function getIzbor($request, $response)
    {
        $modelAnketa = new Anketa();
        $ankete = $modelAnketa->aktuelne();

        $this->render($response, 'ankete/izbor.twig', compact('ankete'));
    }

    public function getPopunjavanje($request, $response, $args)
    {
        $id = (int)$args['id'];
        $modelAnketa = new Anketa();
        $anketa = $modelAnketa->find($id);
        $vreme = $anketa->vreme;
        $sesija = 30;
        $razlika = $vreme-$sesija;
        if ($razlika > 0) {
            $sada = time();
            $buducnost = $sada+($razlika*60);
            $_SESSION['LAST_ACTION'] = $buducnost;
        }else{
            $vreme = $sesija;
        }
        $pitanja = $anketa->pitanja();
        array_multisort( array_column($pitanja, "redosled"), SORT_ASC, $pitanja );

        $this->render($response, 'ankete/popunjavanje.twig', compact('anketa', 'pitanja', 'vreme'));
    }

    public function postSlanjeOdgovora($request, $response)
    {
         if ($this->auth->user()->ispitanik === 100) {
            $this->flash->addMessage('danger', 'Администратору није дозвољено учешће у истраживању/анкети.');
            return $response->withRedirect($this->router->pathFor('izbor'));
            }
        $data = $this->data();
        $id_ispitanika = $this->auth->user()->ispitanik;
        $id_ankete = $data['anketa_id'];
        $dokumenti = $request->getUploadedFiles();
        $greska = "";
        
        $model = new AnketaOdgovor();
        $odgovoris = $model->odgovori_ispitanika($id_ispitanika, $id_ankete);

        if (!empty($odgovoris)) {
            $model->ref(0);
            $model->ciscenje($id_ispitanika, $id_ankete);
            $model->ref(1);
        }

        array_shift($data);
        $data_one = [];

        foreach ($data as $pitanje => $odgovor) {
            if (!empty($odgovor)) {
                $data_one['ispitanik_id'] = $id_ispitanika;
                $data_one['anketa_id'] = $id_ankete;
                if (strpos($pitanje, 'odg') !== FALSE ) {
                    if (($pos = strpos($pitanje, "-")) !== FALSE) {
                        $pit = substr($pitanje, $pos+1);
                    }
                        $data_one['pitanje_id'] = $pit;
                        $data_one['odgovor_id'] = $odgovor;

                    $validation_rules = [
                        'pitanje_id' => [
                        'required' => true,
                        'multi_unique' => 'ankete_odgovori.pitanje_id,ispitanik_id,anketa_id'
                        ],
                        'ispitanik_id' => [
                        'required' => true,
                        ],
                        'odgovor_id' => [
                        'required' => true,
                        ]
                    ];
                    $this->validator->validate($data_one, $validation_rules);

                    if ($this->validator->hasErrors()) {
                        $this->flash->addMessage('danger', 'Дошло је до грешке приликом слања одговора.');
                        return $response->withRedirect($this->router->pathFor('izbor'));
                    } else {
                        $model->insert($data_one);
                        $id_izmena = $model->lastId();
                        //UPLOADA
                        $ind = "dokument-".$pit;
                        if (array_key_exists($ind, $dokumenti)) {
                        foreach ($dokumenti[$ind] as $dokm) {
                            if ($dokm->getError() === UPLOAD_ERR_NO_FILE || $dokm->getError() !== UPLOAD_ERR_OK) {
                                $greska .= $ind;
                            }else{
                                $unique = bin2hex(random_bytes(8));
                                $ime = substr($dokm->getClientFilename(), 0, -4);
                                $extension = pathinfo($dokm->getClientFilename(), PATHINFO_EXTENSION);
                                //$filename = "odgovor_{$id_izmena}_{$unique}.{$extension}";
                                $filename = "{$ime}.{$extension}";
                                $veza = URL . "ank/{$filename}";
                                $data_dok['link'] = $veza;
                                $data_dok['odgovor_id'] = $id_izmena;
                                $data_dok['korisnik_id'] = $this->auth->user()->id;
                                $dokm->moveTo('ank/' . $filename);
                                $modelDokument = new AnketaDokument();
                                $modelDokument->insert($data_dok);
                            }
                        }
                    }
                        $modelodgovor = $model->find($id_izmena);
                        $this->log($this::DODAVANJE, $modelodgovor, ['pitanje_id', 'ispitanik_id', 'odgovor_id']);

                    }
                }else{
                    $data_up['obrazlozenje'] = $odgovor;
                    $modelodgovor->update($data_up, $id_izmena);
                }

             }
        }
            if (!empty($odgovoris)) {
                $model->preraspodela($odgovoris, $id_ispitanika);
            }

            $this->flash->addMessage('success', 'Одговори су успешно послати.');
            return $response->withRedirect($this->router->pathFor('izbor'));
    }

    public function getOdgovoriIspitanika($request, $response, $args)
    {
        $anketa_id = (int) $args['id_ankete'];
        $ispitanik_id = (int) $args['id_ispitanika'];
        $model = new AnketaOdgovor();
        $odgovori =$model->odgovori_ispitanika($ispitanik_id, $anketa_id);
        $modelAnketa = new Anketa();
        $anketa = $modelAnketa->find($anketa_id);
        $modelIspitanik = new AnketaIspitanik();
        $ispitanik = $modelIspitanik->find($ispitanik_id);
        $this->render($response, 'ankete/odgovori.twig', compact('odgovori', 'anketa', 'ispitanik'));
    }

}
