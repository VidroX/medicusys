<?php
return array(
    "main"=>array(
        "appName"=>"Medicus_System",
        "debugMode"=>true
    ),
    "db"=>array(
        "host"=>"127.0.0.1",
        "dbName"=>"medicusys",
        "user"=>"root",
        "pass"=>"",
        "charset"=>"utf8mb4"
    ),
    "csrfProtection"=>array(
        "enabled"=>true
    ),
    "api"=>array(
        "enabled"=>true,
        "versionCode"=>1,
        "token"=>"PUsecR0B6brOYUcrI9LhiXU8w5SlFRorlFrdrlV"
    ),
    "symptoms_api"=>array(
        "enabled"=>true,
        "urlAuth"=>"https://sandbox-authservice.priaid.ch",
        "urlHealthService"=>"https://sandbox-healthservice.priaid.ch",
        "user"=>"vadym.karachenko@nure.ua",
        "password"=>"Mg83WzLp65Ywt9F4R",
        "defaultLanguage" => "ru-ru",
        "availableLanguages" => array(
            "en-gb",
            "ru-ru"
        )
    ),
    "firebase" => array(
        "credentialsFile" => null
    )
);