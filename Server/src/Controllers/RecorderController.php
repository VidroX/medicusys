<?php

namespace App\Controllers;

use App\Models\StatusCodes;
use App\Models\User;
use App\Utils\i18n;
use Slim\Http\Request;
use Slim\Http\Response;

class RecorderController {

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

    public function index(Request $request, Response $response, $args = []){
        $user = new User();
        $status = $user->isUserLoggedIn();
        $urlPrefix = empty($this->i18n->getLanguageCodeForUrl()) ? "" : "/".$this->i18n->getLanguageCodeForUrl();

        if(!$status) {
            return $response->withRedirect($urlPrefix."/login");
        }else{
            $user = $user->getCurrentUser();
            switch ($user->getUserLevel()) {
                case User::USER_PATIENT:
                    return $response->withRedirect($urlPrefix."/patient");
                    break;
                case User::USER_DOCTOR:
                    return $response->withRedirect($urlPrefix."/doctor");
                    break;
                case User::USER_UNSPECIFIED:
                    return $response->withRedirect($urlPrefix."/");
                    break;
            }
        }

        $csrf = json_decode($response->getHeader('X-CSRF-Token')[0], true);
        return $this->view->render($response, 'recorder/indexD.html.twig', [
            "languageCode"=>$this->i18n->getLanguageCode(),
            "appName"=>$this->config['main']['appName'],
            "page"=>"recorder_index",
            "i18n"=>$this->i18n->getTranslations(),
            "csrf"=>$csrf,
            "urlPrefix"=>$urlPrefix,
            "user" => $user
        ]);
    }

    public function doctors(Request $request, Response $response, $args = []){
        $user = new User();
        $status = $user->isUserLoggedIn();
        $urlPrefix = empty($this->i18n->getLanguageCodeForUrl()) ? "" : "/".$this->i18n->getLanguageCodeForUrl();

        if(!$status) {
            return $response->withRedirect($urlPrefix."/login");
        }else{
            $user = $user->getCurrentUser();
            switch ($user->getUserLevel()) {
                case User::USER_PATIENT:
                    return $response->withRedirect($urlPrefix."/patient");
                    break;
                case User::USER_DOCTOR:
                    return $response->withRedirect($urlPrefix."/doctor");
                    break;
                case User::USER_UNSPECIFIED:
                    return $response->withRedirect($urlPrefix."/");
                    break;
            }
        }

        $csrf = json_decode($response->getHeader('X-CSRF-Token')[0], true);
        return $this->view->render($response, 'recorder/indexD.html.twig', [
            "languageCode"=>$this->i18n->getLanguageCode(),
            "appName"=>$this->config['main']['appName'],
            "page"=>"recorder_doctors",
            "i18n"=>$this->i18n->getTranslations(),
            "csrf"=>$csrf,
            "urlPrefix"=>$urlPrefix,
            "user" => $user
        ]);
    }

    public function patients(Request $request, Response $response, $args = []){
        $user = new User();
        $status = $user->isUserLoggedIn();
        $urlPrefix = empty($this->i18n->getLanguageCodeForUrl()) ? "" : "/".$this->i18n->getLanguageCodeForUrl();

        if(!$status) {
            return $response->withRedirect($urlPrefix."/login");
        }else{
            $user = $user->getCurrentUser();
            switch ($user->getUserLevel()) {
                case User::USER_PATIENT:
                    return $response->withRedirect($urlPrefix."/patient");
                    break;
                case User::USER_DOCTOR:
                    return $response->withRedirect($urlPrefix."/doctor");
                    break;
                case User::USER_UNSPECIFIED:
                    return $response->withRedirect($urlPrefix."/");
                    break;
            }
        }

        $csrf = json_decode($response->getHeader('X-CSRF-Token')[0], true);
        return $this->view->render($response, 'recorder/indexP.html.twig', [
            "languageCode"=>$this->i18n->getLanguageCode(),
            "appName"=>$this->config['main']['appName'],
            "page"=>"recorder_patients",
            "i18n"=>$this->i18n->getTranslations(),
            "csrf"=>$csrf,
            "urlPrefix"=>$urlPrefix,
            "user" => $user
        ]);
    }

    public function about(Request $request, Response $response, $args = []){
        $user = new User();
        $status = $user->isUserLoggedIn();
        $urlPrefix = empty($this->i18n->getLanguageCodeForUrl()) ? "" : "/".$this->i18n->getLanguageCodeForUrl();

        if(!$status) {
            return $response->withRedirect($urlPrefix."/login");
        }else{
            $user = $user->getCurrentUser();
            switch ($user->getUserLevel()) {
                case User::USER_PATIENT:
                    return $response->withRedirect($urlPrefix."/patient");
                    break;
                case User::USER_DOCTOR:
                    return $response->withRedirect($urlPrefix."/doctor");
                    break;
                case User::USER_UNSPECIFIED:
                    return $response->withRedirect($urlPrefix."/");
                    break;
            }
        }

        $csrf = json_decode($response->getHeader('X-CSRF-Token')[0], true);
        return $this->view->render($response, 'recorder/about.html.twig', [
            "languageCode"=>$this->i18n->getLanguageCode(),
            "appName"=>$this->config['main']['appName'],
            "page"=>"recorder_about",
            "i18n"=>$this->i18n->getTranslations(),
            "csrf"=>$csrf,
            "urlPrefix"=>$urlPrefix,
            "user" => $user
        ]);
    }

}