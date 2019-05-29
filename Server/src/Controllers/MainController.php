<?php

namespace App\Controllers;

use App\Utils\i18n;
use Slim\Http\Request;
use Slim\Http\Response;

class MainController {

    private $view;
    private $i18n;
    private $config;

    public function __construct(\Slim\Views\Twig $view, i18n $i18n) {
        $this->view = $view;
        $this->i18n = $i18n;
        $this->config = include(__DIR__."/../../config/core.php");
    }

    public function index(Request $request, Response $response, $args = []){
        return $this->view->render($response, 'index.html.twig', [
            "languageCode"=>$this->i18n->getLanguageCode(),
            "appName"=>$this->config['main']['appName'],
            "page"=>"home",
            "i18n"=>$this->i18n->getTranslations(),
            "urlPrefix"=>empty($this->i18n->getLanguageCodeForUrl()) ? "" : "/".$this->i18n->getLanguageCodeForUrl()
        ]);
    }

    public function about(Request $request, Response $response, $args = []){
        return $this->view->render($response, 'about.html.twig', [
            "languageCode"=>$this->i18n->getLanguageCode(),
            "appName"=>$this->config['main']['appName'],
            "page"=>"about",
            "i18n"=>$this->i18n->getTranslations(),
            "urlPrefix"=>empty($this->i18n->getLanguageCodeForUrl()) ? "" : "/".$this->i18n->getLanguageCodeForUrl()
        ]);
    }

    public function test(Request $request, Response $response, $args = []){
        return 'Test page';
    }

}