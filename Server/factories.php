<?php
$container['view'] = function ($container) {
    $config = include(__DIR__."/config/core.php");

    $view = new \Slim\Views\Twig(__DIR__.'/web', [
        'cache' => $config['main']['debugMode'] ? false : __DIR__.'/cache'
    ]);

    $router = $container->get('router');
    $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));

    return $view;
};

$container['App\Controllers\MainController'] = function($container) {
    $view = $container->get("view");
    $i18n = $container->get("locale");
    return new \App\Controllers\MainController($view, $i18n);
};

$container['App\Controllers\AuthController'] = function($container) {
    $view = $container->get("view");
    $i18n = $container->get("locale");
    return new \App\Controllers\AuthController($view, $i18n);
};

$container['App\Controllers\DoctorController'] = function($container) {
    $view = $container->get("view");
    $i18n = $container->get("locale");
    return new \App\Controllers\DoctorController($view, $i18n);
};

$container['App\Controllers\PatientController'] = function($container) {
    $view = $container->get("view");
    $i18n = $container->get("locale");
    return new \App\Controllers\PatientController($view, $i18n);
};

$container['App\Controllers\RecorderController'] = function($container) {
    $view = $container->get("view");
    $i18n = $container->get("locale");
    return new \App\Controllers\RecorderController($view, $i18n);
};

$container['App\Controllers\ApiController'] = function($container) {
    $i18n = $container->get("locale");
    return new \App\Controllers\ApiController($i18n);
};