<?php
$app->group('/{lang:[a-z]{2}}', function () use ($app) {
    $app->get("[/]", 'App\Controllers\MainController:index');
    $app->get("/about[/]", 'App\Controllers\MainController:about');
    $app->get("/login[/]", 'App\Controllers\AuthController:login');
    $app->get("/register[/]", 'App\Controllers\AuthController:register');
    $app->get("/logout[/]", 'App\Controllers\AuthController:logout');
    $app->get("/test[/]", 'App\Controllers\MainController:test');
    $app->post("/login[/]", 'App\Controllers\AuthController:loginPost');
    $app->post("/register[/]", 'App\Controllers\AuthController:registerPost');
    $app->get("/staff[/]",'App\Controllers\MainController:staff');
    $app->get("/contacts[/]",'App\Controllers\MainController:contacts');
    $app->get("/news[/]",'App\Controllers\MainController:news');
});
