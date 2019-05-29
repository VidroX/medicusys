<?php

namespace App\Controllers;
use App\Models\User;
use App\Utils\i18n;
use Slim\Http\Request;
use Slim\Http\Response;

class AuthController {

    private $view;
    private $i18n;
    private $config;

    public function __construct(\Slim\Views\Twig $view, i18n $i18n) {
        $this->view = $view;
        $this->i18n = $i18n;
        $this->config = include(__DIR__."/../../config/core.php");
    }

    public function login(Request $request, Response $response, $args = []){
        $user = new User();
        $user = $user->auth("test4@example.com", "123321");
        $message = "user not found";
        $curUser = null;
        if($user instanceof User) {
            $curUser = $user->getCurrentUser();
            if($user->isUserLoggedIn()) {
                $message = "user found";
            }
        }
        return $this->view->render($response, 'login.html.twig', [
            "languageCode"=>$this->i18n->getLanguageCode(),
            "appName"=>$this->config['main']['appName'],
            "page"=>"login",
            "i18n"=>$this->i18n->getTranslations(),
            "message"=>$message,
            "session"=> $curUser == null ? [] : $curUser,
            "user"=>$user,
            "userLevel"=>$user->getUserLevel(),
            "urlPrefix"=>empty($this->i18n->getLanguageCodeForUrl()) ? "" : "/".$this->i18n->getLanguageCodeForUrl()
        ]);
    }

    public function register(Request $request, Response $response, $args = []){
        $user = new User();
        return $this->view->render($response, 'registration.html.twig', [
            "languageCode"=>$this->i18n->getLanguageCode(),
            "appName"=>$this->config['main']['appName'],
            "page"=>"register",
            "user"=>$user->isUserLoggedIn() ? "true" : "false",
            "i18n"=>$this->i18n->getTranslations(),
            "urlPrefix"=>empty($this->i18n->getLanguageCodeForUrl()) ? "" : "/".$this->i18n->getLanguageCodeForUrl()
        ]);
    }

}