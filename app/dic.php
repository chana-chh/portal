<?php

use App\Classes\Config;

$container = $app->getContainer();

$container['db'] = function ($container) {
    $conf = $container['settings']['chasha_app_settings']['db'];
    $db = new \App\Classes\Db($conf['dsn'], $conf['username'], $conf['password'], $conf['options']);
    return $db;
}
;
$container['mailer'] = function ($container) {
    $conf = $container['settings']['chasha_app_settings']['mail'];
    $mailer = new \App\Classes\Mailer(new \PHPMailer\PHPMailer\PHPMailer(true), $conf);
    return $mailer;
};

$container['auth'] = function ($container) {
    return new \App\Classes\Auth();
};

$container['logger'] = function ($container) {
    return new \App\Classes\Logger($container->auth->user());
};

$container['csrf'] = function ($container) {
    return new \Slim\Csrf\Guard;
};

$container['validator'] = function ($container) {
    return new \App\Classes\Validator();
};

$container['flash'] = function () {
    return new \Slim\Flash\Messages;
};

$container['view'] = function ($container) {
    $conf = $container['settings']['renderer'];
    $view = new \Slim\Views\Twig($conf['template_path'], ['cache' => $conf['cache_path'], 'debug' => true]);
    $router = $container->router;
    $uri = $container->request->getUri();
    $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));
    $view->getEnvironment()->addGlobal('auth', [
        'logged' => $container->auth->isLoggedIn(),
        'user' => $container->auth->user(),
    ]);
    $view->getEnvironment()->addGlobal('APP_NAME', APP_NAME);
    $view->getEnvironment()->addGlobal('URL', URL);
    $view->getEnvironment()->addGlobal('DIR', DIR);
    $view->addExtension(new Knlv\Slim\Views\TwigMessages(new Slim\Flash\Messages));
    $view->addExtension(new Twig_Extension_Debug);
    $view->getEnvironment()->getExtension(\Twig\Extension\CoreExtension::class)->setDateFormat('d.m.Y', '%d dana');
    $view->getEnvironment()->getExtension(\Twig\Extension\CoreExtension::class)->setNumberFormat(2, '.', ',');
    return $view;
};

Config::instance($container);
