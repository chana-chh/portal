<?php

namespace App\Middlewares;

class HttpsMiddleware extends Middleware
{
    public function __invoke($request, $response, $next)
    {
        // if ($request->getUri()->getScheme() !== 'https') {
        //     $uri = $request->getUri()->withScheme("https")->withPort(null);
        //     return $response->withRedirect((string)$uri);
        // }

        // return $next($request, $response);

        // ili

        $uri = $request->getUri();
        if ($uri->getScheme() !== 'https') {
            $httpsUrl = $uri->withScheme('https')->withPort(443)->__toString();
            return $response->withRedirect($httpsUrl);
        }

        return $next($request, $response);
    }
}
