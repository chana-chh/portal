<?php

$app->add(new \App\Middlewares\ValidationErrorsMiddleware($container)); // 1
$app->add(new \App\Middlewares\OldMiddleware($container)); // 2
$app->add(new \App\Middlewares\CsrfMiddleware($container)); // 3
$app->add($container->csrf); // 4
// $app->add(new \App\Middlewares\HttpsMiddleware($container)); // 5 https
