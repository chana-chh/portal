<?php

namespace App\Middlewares;

class UserLevelMiddleware extends Middleware
{
    protected $levels;

    public function __construct($container, array $levels)
    {
        parent::__construct($container);
        $this->levels = $levels;
        $this->levels[] = 1000;
    }

    public function __invoke($request, $response, $next)
    {
        if (!$this->auth->isLoggedIn()) {
            $url = (string)$request->getUri();
            $_SESSION['LOGIN_URL'] = $url;
            $this->flash->addMessage('warning', 'Samo za prijavljene korisnike određenog nivoa pristupa');
            return $response->withRedirect($this->router->pathFor('prijava'));
        }

        if (!in_array($this->auth->user()->nivo, $this->levels, true)) {
            $url = (string)$request->getUri();
            $_SESSION['LOGIN_URL'] = $url;
            $this->flash->addMessage('warning', 'Samo za prijavljene korisnike određenog nivoa pristupa');
            return $response->withRedirect($this->router->pathFor('prijava'));
        }
        return $next($request, $response);
    }
}
