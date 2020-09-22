<?php

namespace App\Middlewares;

class AuthMiddleware extends Middleware
{
    public function __invoke($request, $response, $next)
    {
        if (!$this->auth->isLoggedIn()) {
            $url = (string)$request->getUri();
            $_SESSION['LOGIN_URL'] = $url;
            $this->flash->addMessage('warning', 'Samo za prijavljene korisnike');
            return $response->withRedirect($this->router->pathFor('prijava'));
        }
        return $next($request, $response);
    }
}
