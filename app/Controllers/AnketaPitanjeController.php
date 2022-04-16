<?php

namespace App\Controllers;

use App\Models\Anketa;
use App\Models\AnketaPitanje;
use App\Models\AnketaTipOdgovora;

class AnketaPitanjeController extends Controller
{

    public function postDodavanje($request, $response)
    {
        $data = $this->data();
        $data['link'] = isset($data['link']) ? 1 : 0;

        if ($data['vrsta'] === 'radio' || $data['vrsta'] === 'selekt') {
             if (count($data['odgovori']) > 0 ) {
            $data['odgovori'] = implode(', ', $data['odgovori']);
            } else{
            $data['odgovori'] = null;
            }
        }else{
            $data['odgovori'] = null;
        }
       
        
        $validation_rules = [
            'naziv' => [
                'required' => true,
                'maxlen' => 255,
                'unique' => 'ankete_pitanja.naziv'
            ],
            'anketa_id' => [
                'required' => true,
            ],
            'vrsta' => [
                'required' => true,
            ],
            'link' => [
                'required' => true,
            ],
            'korisnik_id' => [
                'required' => true,
            ]
        ];

        $data['korisnik_id'] = $this->auth->user()->id;

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Дошло је до грешке приликом додавања ПИТАЊА.');
            return $response->withRedirect($this->router->pathFor('ankete.pregled', ['id' => $data['anketa_id']]));
        } else {
            $this->flash->addMessage('success', 'Ново ПИТАЊЕ је успешно додато.');
            $model = new AnketaPitanje();
            $model_ankete = new Anketa();
            $ankete = $model_ankete->find($data['anketa_id']);
            $pitanja = $ankete->pitanja();
            $postojeci = array_column($pitanja, 'redosled');
            // RAD SA REDOSLEDOM PITANJA
            if (count($pitanja) > 0 ) {
                if (in_array($data['redosled'], $postojeci)) {
                    foreach ($pitanja as $pit) {
                        if ($pit->redosled >= $data['redosled']) {
                            $pitanje = $model->find($pit->id);
                            $datam = [
                                "redosled" => ($pit->redosled+1)
                            ];
                            $model->update($datam, $pit->id);
                        }
                    }
                }
            }
            $model->insert($data);
            $vrsta = $model->find($model->lastId());
            $this->log($this::DODAVANJE, $vrsta, ['naziv', 'odgovori', 'anketa_id']);
            return $response->withRedirect($this->router->pathFor('ankete.pregled', ['id' => $data['anketa_id']]));
        }
    }

    public function getIzmena($request, $response, $args)
    {
        $id = (int) $args['id'];
        $modelPitanja = new AnketaPitanje();
        $pitanje = $modelPitanja->find($id);
        $vrste = $modelPitanja->enumOrSetList('vrsta');

        $anketa = $pitanje->anketa();

        $modelTipOdgovora = new AnketaTipOdgovora();
        $tipovi = $modelTipOdgovora->all();
        $this->render($response, 'ankete/izmena_pitanja.twig', compact('pitanje', 'tipovi', 'vrste', 'anketa'));
    }

    public function postIzmena($request, $response)
    {
        $data = $this->data();
        $id = $data['idIzmena'];
        unset($data['idIzmena']);

        $data['link'] = isset($data['link']) ? 1 : 0;
        if ($data['vrsta'] === 'radio' || $data['vrsta'] === 'selekt') {
             if (count($data['odgovori']) > 0 ) {
            $data['odgovori'] = implode(', ', $data['odgovori']);
            } else{
            $data['odgovori'] = null;
            }
        }else{
            $data['odgovori'] = null;
        }
        
        $validation_rules = [
            'naziv' => [
                'required' => true,
                'maxlen' => 255,
                'unique' => 'ankete_pitanja.naziv#id:'.$id
            ],
            'anketa_id' => [
                'required' => true,
            ],
            'vrsta' => [
                'required' => true,
            ],
            'link' => [
                'required' => true,
            ],
            'korisnik_id' => [
                'required' => true,
            ]
        ];

        $data['korisnik_id'] = $this->auth->user()->id;

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Дошло је до грешке приликом измене података.');
            return $response->withRedirect($this->router->pathFor('ankete.pregled', ['id' => $data['anketa_id']]));
        } else {
            $model = new AnketaPitanje();
            $model_ankete = new Anketa();
            $ankete = $model_ankete->find($data['anketa_id']);
            $pitanja = $ankete->pitanja();
            $postojeci = array_column($pitanja, 'redosled');
            $pitanje = $model->find($id);
            $stara_pozicija = $pitanje->redosled;
            $key = array_search($pitanje->redosled, $postojeci);
            unset($postojeci[$key]);

            if ($pitanje->redosled != $data['redosled']) {
                // RAD SA REDOSLEDOM PITANJA
                if (in_array($data['redosled'], $postojeci)) {
                    foreach ($pitanja as $pit) {
                        if ($pit->id != $id) {
                            if ($stara_pozicija > (int)$data['redosled']) {
                                if ($pit->redosled >= $data['redosled'] && $pit->redosled < $stara_pozicija) {
                                    $datam = [
                                        "redosled" => ($pit->redosled+1)
                                    ];
                                        $model->update($datam, $pit->id);
                                }
                            }else{
                                if ($pit->redosled <= $data['redosled'] && $pit->redosled > $stara_pozicija) {
                                    $datam = [
                                        "redosled" => ($pit->redosled-1)
                                    ];
                                        $model->update($datam, $pit->id);
                                }

                            }
                        }
                    }
                }
            }
            $model->update($data, $id);
            $pitanjei = $model->find($id);

            $this->log($this::IZMENA, $pitanjei, ['naziv', 'vrsta', 'odgovori'], $pitanje);
            $this->flash->addMessage('success', 'Подаци су успешно измењени.');
            return $response->withRedirect($this->router->pathFor('ankete.pregled', ['id' => $data['anketa_id']]));
        }
    }

    public function postBrisanje($request, $response)
    {
        $id = (int)$request->getParam('idBrisanje');
        $model = new AnketaPitanje();
        $modelAnketa = new Anketa();
        
        $pitanje = $model->find($id);
        $id_ankete = $pitanje->anketa()->id;
        $anketa = $modelAnketa->find($id_ankete);

        if (!empty($pitanje->odgovori())) {
            $this->flash->addMessage('danger', "Ово питање има одговоре анкетираних. Обришите све одговоре везане за питање да бисте га обрисали");
            return $response->withRedirect($this->router->pathFor('ankete.pregled', ['id' => $id_ankete]));
        }else{
            $success = $model->deleteOne($id);
            $pitanja = $anketa->pitanja();
            array_multisort( array_column($pitanja, "redosled"), SORT_ASC, $pitanja );
            $brojac = 0;
            foreach ($pitanja as $pit) {
            $brojac++;
                $datam = [
                        "redosled" => ($brojac)
                        ];
                $model->update($datam, $pit->id);
            }
        }
        
        if ($success) {
            $this->flash->addMessage('success', "ПИТАЊЕ је успешно обрисано.");
            $this->log($this::BRISANJE, $pitanje, ['naziv', 'vrsta', 'odgovori'], $pitanje);
            return $response->withRedirect($this->router->pathFor('ankete.pregled', ['id' => $id_ankete]));
        } else {
            $this->flash->addMessage('danger', "Дошло је до грешке приликом брисања ПИТАЊА.");
            return $response->withRedirect($this->router->pathFor('ankete.pregled', ['id' => $id_ankete]));
        }

    }
}
