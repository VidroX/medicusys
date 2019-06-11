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
    $app->get("/staff[/]",'App\Controllers\MainController:staff');
    $app->get("/contacts[/]",'App\Controllers\MainController:contacts');
    $app->get("/news[/]",'App\Controllers\MainController:news');
    $app->get("/home[/]",'App\Controllers\MainController:home');

    //AuthController
    $app->get("/register[/]", 'App\Controllers\AuthController:register');
    $app->get("/logout[/]", 'App\Controllers\AuthController:logout');
    $app->get("/test[/]", 'App\Controllers\MainController:test');

    //DoctorController
    $app->group('/doctor', function () use ($app) {
        $app->get("[/]", 'App\Controllers\DoctorController:index');
        $app->get("/message/{id}[/]", 'App\Controllers\DoctorController:message');
        $app->group('/report', function () use ($app) {
            $app->group('/{id}', function () use ($app) {
                $app->get("[/]", 'App\Controllers\DoctorController:report');
                $app->get("/info/{diagnosisId}[/]", 'App\Controllers\DoctorController:reportInfo');
                $app->group('/recipe/{diagnosisId}', function () use ($app) {
                    $app->get("[/]", 'App\Controllers\DoctorController:recipeInfo');
                    $app->get("/add[/]", 'App\Controllers\DoctorController:recipeAdd');
                    $app->get("/{recipeId}/edit[/]", 'App\Controllers\DoctorController:recipeEdit');
                });
                $app->get("/add[/]", 'App\Controllers\DoctorController:reportAdd');
            });
        });
        $app->group('/table', function () use ($app) {
            $app->post("/get[/]", 'App\Controllers\DoctorController:tableGet');
        });
    });

    //PatientController
    $app->group('/patient', function () use ($app) {
        $app->get("[/]", 'App\Controllers\PatientController:index');
    });

    //RecorderController
    $app->group('/recorder', function () use ($app) {
        $app->get("[/]", 'App\Controllers\RecorderController:doctors');
        $app->get("/doctors[/]", 'App\Controllers\RecorderController:doctors');
        $app->get("/patients[/]", 'App\Controllers\RecorderController:patients');
        $app->get("/about[/]", 'App\Controllers\RecorderController:about');
    });

    /*\
 ---|  \ * -----------------------------------------------------------------------------------\
    |  *    POST requests                                                                      >
 ---|  / * -----------------------------------------------------------------------------------/
    \*/

    // AuthController
    $app->post("/login[/]", 'App\Controllers\AuthController:loginPost');
    $app->post("/register[/]", 'App\Controllers\AuthController:registerPost');

    // Admin part
    $app->group('/admin', function () use ($app) {
        // DoctorController
        $app->post('/doctor/fcmmessage', 'App\Controllers\DoctorController:postMessageSend');
        $app->post('/doctor/diagnosis', 'App\Controllers\DoctorController:getDiagnosis');
        $app->post('/doctor/symptoms', 'App\Controllers\DoctorController:getSymptoms');
        $app->group('/doctor/report', function () use ($app) {
            $app->post("/add[/]", 'App\Controllers\DoctorController:postReportAdd');
            $app->post("/delete[/]", 'App\Controllers\DoctorController:postReportDelete');
        });
        $app->group('/doctor/recipe', function () use ($app) {
            $app->post("/add[/]", 'App\Controllers\DoctorController:postAddRecipe');
            $app->post("/edit[/]", 'App\Controllers\DoctorController:postEditRecipe');
            $app->post("/delete[/]", 'App\Controllers\DoctorController:postDeleteRecipe');
        });
    });
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
        $app->post('/diagnosis[/]', 'App\Controllers\ApiController:diagnosis');
        $app->post('/recipe[/]', 'App\Controllers\ApiController:recipe');
        $app->post('/fcm/token[/]', 'App\Controllers\ApiController:fcmTokenUpdate');
    });
}