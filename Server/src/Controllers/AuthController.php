<?php

namespace App\Controllers;

use App\Models\StatusCodes;
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

    /*
     *
     * GET requests
     *
     */

    public function login(Request $request, Response $response, $args = []){
        $user = new User();
        if($user->isUserLoggedIn()) {
            return $response->withRedirect("/");
        }
        $csrf = json_decode($response->getHeader('X-CSRF-Token')[0], true);
        return $this->view->render($response, 'login.html.twig', [
            "languageCode"=>$this->i18n->getLanguageCode(),
            "appName"=>$this->config['main']['appName'],
            "page"=>"login",
            "i18n"=>$this->i18n->getTranslations(),
            "csrf"=>$csrf,
            "urlPrefix"=>empty($this->i18n->getLanguageCodeForUrl()) ? "" : "/".$this->i18n->getLanguageCodeForUrl()
        ]);
    }

    public function register(Request $request, Response $response, $args = []){
        $user = new User();
        if($user->isUserLoggedIn()) {
            return $response->withRedirect("/");
        }
        $csrf = json_decode($response->getHeader('X-CSRF-Token')[0], true);
        return $this->view->render($response, 'registration.html.twig', [
            "languageCode"=>$this->i18n->getLanguageCode(),
            "appName"=>$this->config['main']['appName'],
            "page"=>"register",
            "i18n"=>$this->i18n->getTranslations(),
            "csrf"=>$csrf,
            "urlPrefix"=>empty($this->i18n->getLanguageCodeForUrl()) ? "" : "/".$this->i18n->getLanguageCodeForUrl()
        ]);
    }

    public function logout(Request $request, Response $response, $args = []){
        $user = new User();
        $user->logout();
        return $response->withRedirect("/");
    }

    /*
     *
     * POST requests
     *
     */

    public function loginPost(Request $request, Response $response, $args = []){
        $user = new User();
        if($user->isUserLoggedIn()) {
            return $response->withJson([
                "status"=>17,
                "message"=>StatusCodes::STATUS[17]
            ]);
        }

        $parsedBody = $request->getParsedBody();
        if(isset($parsedBody) && !empty($parsedBody)) {
            if((isset($parsedBody['login']) && !empty($parsedBody['login'])) && (isset($parsedBody['password']) && !empty($parsedBody['password']))) {
                $login = $parsedBody['login'];
                $pass = $parsedBody['password'];

                $user = $user->auth($login, $pass);
                if (!($user instanceof User)) {
                    $data = json_decode($user, true);
                    if ($data['status'] === 2) {
                        return $response->withJson([
                            "status" => 2,
                            "message" => $this->i18n->getTranslation("invalidPass")
                        ]);
                    } elseif ($data['status'] === 1) {
                        return $response->withJson([
                            "status" => 1,
                            "message" => $this->i18n->getTranslation("invalidEmailPass")
                        ]);
                    }
                }

                return $response->withJson([
                    "status" => 18,
                    "message" => StatusCodes::STATUS[18],
                    "data" => [
                        "userLevel" => $user->getUserLevel()
                    ]
                ]);
            }else{
                return $response->withJson([
                    "status" => 20,
                    "message" => $this->i18n->getTranslation("unexpectedError")
                ]);
            }
        }else{
            return $response->withJson([
                "status" => 20,
                "message" => $this->i18n->getTranslation("unexpectedError")
            ]);
        }
    }

    public function registerPost(Request $request, Response $response, $args = []){
        $user = new User();
        if($user->isUserLoggedIn()) {
            return $response->withJson([
                "status"=>17,
                "message"=>StatusCodes::STATUS[17]
            ]);
        }

        $parsedBody = $request->getParsedBody();

        return "";
    }

}