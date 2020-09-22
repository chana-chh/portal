<?php

namespace App\Classes;

use App\Models\Korisnik;

class Auth
{
    private $model;

    public function __construct()
    {
        $this->model = new Korisnik();
    }

    public function login($username, $password)
    {
        $user = $this->model->findByUsername($username);
        if (!$user) {
            return false;
        }
        if ($this->checkPassword($password, $user->lozinka)) {
            $_SESSION['user'] = $user->id;

            return true;
        }
        return false;
    }

    public function isLoggedIn()
    {
        return isset($_SESSION['user']);
    }

    public function user()
    {
        if (isset($_SESSION['user'])) {
            return $this->model->find((int)$_SESSION['user']);
        }
        return null;
    }

    public function logout()
    {
        unset($_SESSION['user']);
    }

    public function checkPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
}
