<?php

namespace App\Controllers;

class Controller
{
    protected $container;

    const DODAVANJE = "dodavanje";
    const IZMENA = "izmena";
    const BRISANJE = "brisanje";
    const UPLOAD = "upload";

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function __get($property)
    {
        if ($this->container->get($property)) {
            return $this->container->get($property);
        }
    }

    protected function render($response, $template, $vars = [])
    {
        $this->container->view->render($response, $template, $vars);
    }

    protected function renderPartial($template, $vars = [])
    {
        return $this->view->getEnvironment()->render($template, $vars);
    }

    protected function addCsrfToken(array &$data)
    {
        $data['csrf_name']=$this->csrf->getTokenName();
        $data['csrf_value']=$this->csrf->getTokenValue();
    }

    protected function log($tip, $model, $polje, $model_stari = null)
    {
        $this->logger->log($tip, $model, $polje, $model_stari);
    }

    protected function page($naziv = 'page')
    {
        $query = [];
        parse_str($this->request->getUri()->getQuery(), $query);
        $page = isset($query[$naziv]) ? (int)$query[$naziv] : 1;
        return $page;
    }

    protected function data($unsetId = '')
    {
        $data = $this->request->getParams();
        unset($data['csrf_name']);
        unset($data['csrf_value']);
        if ($unsetId !== '') {
            unset($data[$unsetId]);
        }
        return $data;
    }

    protected function dataId($id = 'id')
    {
        return (int) $this->request->getParam($id);
    }

    protected function unserializeLogs(&$logs)
    {
        if (isset($logs['data'])) {
            foreach ($logs['data'] as $log) {
                $log->izmene = unserialize($log->izmene);
            }
        } else {
            foreach ($logs as $log) {
                $log->izmene = unserialize($log->izmene);
            }
        }
    }
}
