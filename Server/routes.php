<?php
$app->group('/{lang:[a-z]{2}}', function () use ($app) {
    $app->get("[/]", 'App\Controllers\MainController:index');
    $app->get("/about[/]", 'App\Controllers\MainController:about');
    $app->get("/login[/]", 'App\Controllers\AuthController:login');
    $app->get("/register[/]", 'App\Controllers\AuthController:register');
    $app->get("/test[/]", 'App\Controllers\MainController:test');
});
