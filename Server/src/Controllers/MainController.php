<?php

namespace App\Controllers;

use App\Utils\i18n;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class MainController
 * @package App\Controllers
 */
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

    public function staff(Request $request, Response $response, $args = []){
        return $this->view->render($response, 'staff.html.twig', [
            "languageCode"=>$this->i18n->getLanguageCode(),
            "appName"=>$this->config['main']['appName'],
            "page"=>"staff",
            "i18n"=>$this->i18n->getTranslations(),
            "urlPrefix"=>empty($this->i18n->getLanguageCodeForUrl()) ? "" : "/".$this->i18n->getLanguageCodeForUrl()
        ]);
    }
    public function contacts(Request $request, Response $response, $args = []){
        return $this->view->render($response, 'contacts.html.twig', [
            "languageCode"=>$this->i18n->getLanguageCode(),
            "appName"=>$this->config['main']['appName'],
            "page"=>"contacts",
            "i18n"=>$this->i18n->getTranslations(),
            "urlPrefix"=>empty($this->i18n->getLanguageCodeForUrl()) ? "" : "/".$this->i18n->getLanguageCodeForUrl()
        ]);
    }
    public function news(Request $request, Response $response, $args = []){
        return $this->view->render($response, 'news.html.twig', [
            "languageCode"=>$this->i18n->getLanguageCode(),
            "appName"=>$this->config['main']['appName'],
            "page"=>"news",
            "i18n"=>$this->i18n->getTranslations(),
            "urlPrefix"=>empty($this->i18n->getLanguageCodeForUrl()) ? "" : "/".$this->i18n->getLanguageCodeForUrl()
        ]);
    }

    public function test(Request $request, Response $response, $args = []){
        return 'Test page';
    }

}