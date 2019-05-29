<?php
use \App\Utils\i18n;

require_once(__DIR__."/vendor/autoload.php");

$config = include(__DIR__."/config/core.php");

session_start();

if($config['main']['debugMode']){
    error_reporting(-1);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
}

$i18n = new i18n(i18n::getBrowserLocale());

$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => $config['main']['debugMode'],
    ]
]);

$container = $app->getContainer();

$container['locale'] = $i18n;

$container['errorHandler'] = function ($container) {
    return new \App\Handlers\RouterErrorHandler();
};
$container['notFoundHandler'] = function ($container) {
    return new \App\Handlers\RouterNotFoundHandler();
};
$container['notAllowedHandler'] = function ($container) {
    return new \App\Handlers\RouterNotAllowedHandler();
};

include_once(__DIR__."/factories.php");

$app->add(new \App\Middleware\Languager($i18n));

include_once(__DIR__."/routes.php");

try{
    $app->run();
}catch (\Exception $ex){
    die($ex);
}