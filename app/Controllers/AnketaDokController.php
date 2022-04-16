<?php

namespace App\Controllers;

use App\Models\AnketaDokument;
use App\Classes\Logger;

class AnketaDokController extends Controller
{
    public function postBrisanje($request, $response)
    {
        $id = (int)$request->getParam('idBrisanje');
        $modelDokument = new AnketaDokument();
        $dok = $modelDokument->find($id);
        $id_ankete = $dok->odgovor()->anketa()->id;
        $tmp = explode('/', $dok->link);

        $file = DIR . 'pub' . DS . 'ank' . DS . end($tmp);
        $success = $modelDokument->deleteOne($id);
        if ($success) {
            unlink($file);
            $this->log($this::BRISANJE, $dok, 'link', $dok);
            $this->flash->addMessage('success', "Докуменат је успешно обрисан.");
            return $response->withRedirect($this->router->pathFor('popunjavanje', ['id' => $id_ankete]));
        } else {
            $this->flash->addMessage('danger', "Дошло је до грешке приликом брисања докумената.");
            return $response->withRedirect($this->router->pathFor('popunjavanje', ['id' => $id_ankete]));
        }
    }
}
