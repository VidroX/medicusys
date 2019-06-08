<?php

namespace App\Controllers;

use App\Models\User;
use App\Utils\i18n;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class MainController
 * @package App\Controllers
 */
class MainController
{

    private $view;
    private $i18n;
    private $config;

    public function __construct(\Slim\Views\Twig $view, i18n $i18n)
    {
        $this->view = $view;
        $this->i18n = $i18n;
        $this->config = include(__DIR__ . "/../../config/core.php");
    }

    public function index(Request $request, Response $response, $args = [])
    {
        $user = new User();
        $status = $user->isUserLoggedIn();
        $urlPrefix = empty($this->i18n->getLanguageCodeForUrl()) ? "" : "/".$this->i18n->getLanguageCodeForUrl();

        if(!$status) {
            $cabinetUrl = $urlPrefix."/login";
        }else{
            $user = $user->getCurrentUser();
            switch ($user->getUserLevel()) {
                case User::USER_RECORDER:
                    $cabinetUrl = $urlPrefix."/recorder";
                    break;
                case User::USER_DOCTOR:
                    $cabinetUrl = $urlPrefix."/doctor";
                    break;
                case User::USER_PATIENT:
                    $cabinetUrl = $urlPrefix."/patient";
                    break;
                case User::USER_UNSPECIFIED:
                    $cabinetUrl = $urlPrefix."/";
                    break;
                default:
                    $cabinetUrl = $urlPrefix."/";
                    break;
            }
        }

        return $this->view->render($response, 'main_page\home.html.twig', [
            "languageCode" => $this->i18n->getLanguageCode(),
            "appName" => $this->config['main']['appName'],
            "page" => "index",
            "i18n" => $this->i18n->getTranslations(),
            "urlPrefix" => $urlPrefix,
            "userLoggedIn" => $status,
            "cabinetUrl"=>$cabinetUrl
        ]);
    }

    public function home(Request $request, Response $response, $args = [])
    {
        $user = new User();
        $status = $user->isUserLoggedIn();
        $urlPrefix = empty($this->i18n->getLanguageCodeForUrl()) ? "" : "/".$this->i18n->getLanguageCodeForUrl();

        if(!$status) {
            $cabinetUrl = $urlPrefix."/login";
        }else{
            $user = $user->getCurrentUser();
            switch ($user->getUserLevel()) {
                case User::USER_RECORDER:
                    $cabinetUrl = $urlPrefix."/recorder";
                    break;
                case User::USER_DOCTOR:
                    $cabinetUrl = $urlPrefix."/doctor";
                    break;
                case User::USER_PATIENT:
                    $cabinetUrl = $urlPrefix."/patient";
                    break;
                case User::USER_UNSPECIFIED:
                    $cabinetUrl = $urlPrefix."/";
                    break;
                default:
                    $cabinetUrl = $urlPrefix."/";
                    break;
            }
        }

        return $this->view->render($response, 'main_page\home.html.twig', [
            "languageCode" => $this->i18n->getLanguageCode(),
            "appName" => $this->config['main']['appName'],
            "page" => "home",
            "i18n" => $this->i18n->getTranslations(),
            "urlPrefix" => $urlPrefix,
            "userLoggedIn" => $status,
            "cabinetUrl"=>$cabinetUrl
        ]);
    }

    public function about(Request $request, Response $response, $args = [])
    {
        $user = new User();
        $status = $user->isUserLoggedIn();
        $urlPrefix = empty($this->i18n->getLanguageCodeForUrl()) ? "" : "/".$this->i18n->getLanguageCodeForUrl();

        if(!$status) {
            $cabinetUrl = $urlPrefix."/login";
        }else{
            $user = $user->getCurrentUser();
            switch ($user->getUserLevel()) {
                case User::USER_RECORDER:
                    $cabinetUrl = $urlPrefix."/recorder";
                    break;
                case User::USER_DOCTOR:
                    $cabinetUrl = $urlPrefix."/doctor";
                    break;
                case User::USER_PATIENT:
                    $cabinetUrl = $urlPrefix."/patient";
                    break;
                case User::USER_UNSPECIFIED:
                    $cabinetUrl = $urlPrefix."/";
                    break;
                default:
                    $cabinetUrl = $urlPrefix."/";
                    break;
            }
        }

        return $this->view->render($response, 'about.html.twig', [
            "languageCode" => $this->i18n->getLanguageCode(),
            "appName" => $this->config['main']['appName'],
            "page" => "about",
            "i18n" => $this->i18n->getTranslations(),
            "urlPrefix" => $urlPrefix,
            "userLoggedIn" => $status,
            "cabinetUrl"=>$cabinetUrl
        ]);
    }

    public function staff(Request $request, Response $response, $args = [])
    {
        $user = new User();
        $status = $user->isUserLoggedIn();
        $urlPrefix = empty($this->i18n->getLanguageCodeForUrl()) ? "" : "/".$this->i18n->getLanguageCodeForUrl();

        if(!$status) {
            $cabinetUrl = $urlPrefix."/login";
        }else{
            $user = $user->getCurrentUser();
            switch ($user->getUserLevel()) {
                case User::USER_RECORDER:
                    $cabinetUrl = $urlPrefix."/recorder";
                    break;
                case User::USER_DOCTOR:
                    $cabinetUrl = $urlPrefix."/doctor";
                    break;
                case User::USER_PATIENT:
                    $cabinetUrl = $urlPrefix."/patient";
                    break;
                case User::USER_UNSPECIFIED:
                    $cabinetUrl = $urlPrefix."/";
                    break;
                default:
                    $cabinetUrl = $urlPrefix."/";
                    break;
            }
        }

        return $this->view->render($response, 'main_page\staff.html.twig', [
            "languageCode" => $this->i18n->getLanguageCode(),
            "appName" => $this->config['main']['appName'],
            "page" => "staff",
            "i18n" => $this->i18n->getTranslations(),
            "urlPrefix" => $urlPrefix,
            "userLoggedIn" => $status,
            "cabinetUrl"=>$cabinetUrl
        ]);
    }

    public function contacts(Request $request, Response $response, $args = [])
    {
        $user = new User();
        $status = $user->isUserLoggedIn();
        $urlPrefix = empty($this->i18n->getLanguageCodeForUrl()) ? "" : "/".$this->i18n->getLanguageCodeForUrl();

        if(!$status) {
            $cabinetUrl = $urlPrefix."/login";
        }else{
            $user = $user->getCurrentUser();
            switch ($user->getUserLevel()) {
                case User::USER_PATIENT:
                    $cabinetUrl = $urlPrefix."/patient";
                    break;
                case User::USER_RECORDER:
                    $cabinetUrl = $urlPrefix."/recorder";
                    break;
                case User::USER_UNSPECIFIED:
                    $cabinetUrl = $urlPrefix."/";
                    break;
                default:
                    $cabinetUrl = "none";
                    break;
            }
        }

        return $this->view->render($response, 'main_page\contacts.html.twig', [
            "languageCode" => $this->i18n->getLanguageCode(),
            "appName" => $this->config['main']['appName'],
            "page" => "contacts",
            "i18n" => $this->i18n->getTranslations(),
            "urlPrefix" => $urlPrefix,
            "userLoggedIn" => $status,
            "cabinetUrl"=>$cabinetUrl
        ]);
    }

    public function news(Request $request, Response $response, $args = [])
    {
        $user = new User();
        $status = $user->isUserLoggedIn();
        $urlPrefix = empty($this->i18n->getLanguageCodeForUrl()) ? "" : "/".$this->i18n->getLanguageCodeForUrl();

        if(!$status) {
            $cabinetUrl = $urlPrefix."/login";
        }else{
            $user = $user->getCurrentUser();
            switch ($user->getUserLevel()) {
                case User::USER_RECORDER:
                    $cabinetUrl = $urlPrefix."/recorder";
                    break;
                case User::USER_DOCTOR:
                    $cabinetUrl = $urlPrefix."/doctor";
                    break;
                case User::USER_PATIENT:
                    $cabinetUrl = $urlPrefix."/patient";
                    break;
                case User::USER_UNSPECIFIED:
                    $cabinetUrl = $urlPrefix."/";
                    break;
                default:
                    $cabinetUrl = $urlPrefix."/";
                    break;
            }
        }

        return $this->view->render($response, 'main_page\news.html.twig', [
            "languageCode" => $this->i18n->getLanguageCode(),
            "appName" => $this->config['main']['appName'],
            "page" => "news",
            "i18n" => $this->i18n->getTranslations(),
            "urlPrefix" => $urlPrefix,
            "userLoggedIn" => $status,
            "cabinetUrl"=>$cabinetUrl
        ]);
    }

    public function test(Request $request, Response $response, $args = [])
    {
        return 'Test page';
    }

}