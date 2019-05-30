<?php
$routes = $app->group('/{lang:[a-z]{2}}', function () use ($app) {
    /*\
 ---|  \ * -----------------------------------------------------------------------------------\
    |  *    GET requests                                                                       >
 ---|  / * -----------------------------------------------------------------------------------/
    \*/

    // MainController
    $app->get("[/]", 'App\Controllers\MainController:index');
    $app->get("/about[/]", 'App\Controllers\MainController:about');
    $app->get("/login[/]", 'App\Controllers\AuthController:login');

    //AuthController
    $app->get("/register[/]", 'App\Controllers\AuthController:register');
    $app->get("/logout[/]", 'App\Controllers\AuthController:logout');
    $app->get("/test[/]", 'App\Controllers\MainController:test');

    //DoctorController
    $app->group('/doctor', function () use ($app) {
        $app->get("[/]", 'App\Controllers\DoctorController:index');
    });

    //PatientController
    $app->group('/patient', function () use ($app) {
        $app->get("[/]", 'App\Controllers\PatientController:index');
    });

    //RecorderController
    $app->group('/recorder', function () use ($app) {
        $app->get("[/]", 'App\Controllers\RecorderController:index');
    });


    /*\
 ---|  \ * -----------------------------------------------------------------------------------\
    |  *    POST requests                                                                      >
 ---|  / * -----------------------------------------------------------------------------------/
    \*/

    //AuthController
    $app->post("/login[/]", 'App\Controllers\AuthController:loginPost');
    $app->post("/register[/]", 'App\Controllers\AuthController:registerPost');
});

if($config['csrfProtection']['enabled']){
    $csrf = $container->get('csrf');
    $routes->add(new \App\Middleware\CSRFTokenMiddleware($csrf));
    $routes->add($csrf);
}

if($config['api']['enabled']){
    $app->group('/api/v1', function () use ($app) {
        $app->map(['GET', 'POST'], "[/]", 'App\Controllers\ApiController:index');
        $app->post('/login[/]', 'App\Controllers\ApiController:login');
    });
}