<?php

namespace App\Controllers;

use App\Models\Log;
use App\Models\Korisnik;

class LogController extends Controller
{
    public function getLog($request, $response)
    {
        $model = new Log();
        $sql="SELECT * FROM logovi WHERE korisnik_id > 0 ORDER BY datum DESC;";
        if ($this->auth->user()->id === 0) {
            $sql="SELECT * FROM logovi ORDER BY datum DESC;";
        }

        $logovi = $model->paginate($this->page(), 'page', $sql);
        $this->unserializeLogs($logovi);

        $model_korisnici = new Korisnik();
        $sqlk = "SELECT * FROM korisnici WHERE id > 0;";
        if ($this->auth->user()->id === 0) {
            $sqlk = "SELECT * FROM korisnici;";
        }
        $korisnici = $model_korisnici->fetch($sqlk);

        $this->render($response, 'log/lista.twig', compact('logovi', 'korisnici'));
    }

    public function postLogPretraga($request, $response)
    {
        $_SESSION['DATA_LOGOVI_PRETRAGA'] = $request->getParams();

        return $response->withRedirect($this->router->pathFor('logovi.pretraga'));
    }

    public function getLogPretraga($request, $response)
    {
        // TODO: izdvojiti u metodu koja pravi where
        $data = $_SESSION['DATA_LOGOVI_PRETRAGA'];
        array_shift($data);
        array_shift($data);

        $korisnik = $data['korisnik_id'] === '' ? false : true;

        if (empty($data['opis']) &&
            empty($data['izmene']) &&
            empty($data['tip']) &&
            empty($data['datum_1']) &&
            empty($data['datum_2']) &&
            $korisnik === false) {
            return $response->withRedirect($this->router->pathFor('logovi'));
        }

        $opis = '%' . filter_var($data['opis'], FILTER_SANITIZE_STRING) . '%';
        $izmene = '%' . filter_var($data['izmene'], FILTER_SANITIZE_STRING) . '%';

        $where = " WHERE ";
        $params = [];

        if (!empty($data['opis'])) {
            $where .= "opis LIKE :opis";
            $params[':opis'] = $opis;
        }

        if (!empty($data['izmene'])) {
            if ($where !== " WHERE ") {
                $where .= " AND ";
            }
            $where .= "izmene LIKE :izmene";
            $params[':izmene'] = $izmene;
        }

        if (!empty($data['tip'])) {
            if ($where !== " WHERE ") {
                $where .= " AND ";
            }
            $where .= "tip = :tip";
            $params[':tip'] = $data['tip'];
        }

        if (!empty($data['datum_1']) && empty($data['datum_2'])) {
            if ($where !== " WHERE ") {
                $where .= " AND ";
            }
            $where .= "DATE(datum) = :datum_1";
            $params[':datum_1'] = $data['datum_1'];
        }

        if (!empty($data['datum_1']) && !empty($data['datum_2'])) {
            if ($where !== " WHERE ") {
                $where .= " AND ";
            }
            $where .= "DATE(datum) >= :datum_1 AND DATE(datum) <= :datum_2 ";
            $params[':datum_1'] = $data['datum_1'];
            $params[':datum_2'] = $data['datum_2'];
        }

        if ($korisnik) {
            if ($where !== " WHERE ") {
                $where .= " AND ";
            }
            $where .= "korisnik_id = :korisnik_id";
            $params[':korisnik_id'] = (int) $data['korisnik_id'];
        }

        $where = $where === " WHERE " ? "" : $where;
        $model = new Log();
        $sql = "SELECT * FROM {$model->getTable()}{$where} ORDER BY datum DESC;";
        $logovi = $model->paginate($this->page(), 'page', $sql, $params);
        $this->unserializeLogs($logovi);

        $this->render($response, 'log/lista.twig', compact('logovi', 'data'));
    }
}
