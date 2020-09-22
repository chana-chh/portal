<?php

namespace App\Middlewares;

class CsrfMiddleware extends Middleware
{
    public function __invoke($request, $response, $next)
    {
        $csrf = "
		<input type=\"hidden\" name=\"csrf_name\" value=\"{$this->csrf->getTokenName()}\" class=\"csrf_name\">
		<input type=\"hidden\" name=\"csrf_value\" value=\"{$this->csrf->getTokenValue()}\" class=\"csrf_value\">
		";
        $this->view->getEnvironment()->addGlobal('csrf', $csrf);
        return $next($request, $response);
    }
}
