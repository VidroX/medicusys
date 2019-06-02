<?php

namespace App\Controllers;

use App\Models\StatusCodes;
use App\Models\User;
use App\Utils\i18n;
use Slim\Http\Request;
use Slim\Http\Response;

class DoctorController {

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
                case User::USER_RECORDER:
                    return $response->withRedirect($urlPrefix."/recorder");
                    break;
                case User::USER_UNSPECIFIED:
                    return $response->withRedirect($urlPrefix."/");
                    break;
            }
        }

        $tablePage = 1;
        $pageParam = (int) $request->getParam("page");
        if(isset($pageParam) && !empty($pageParam) && $pageParam > 0) {
            $tablePage = $pageParam;
        }

        $csrfArray = $response->getHeader('X-CSRF-Token');
        if($csrfArray != null) {
            $csrf = json_decode($csrfArray[0], true);
        }else{
            $csrf = [
                'csrf_name' => "",
                'csrf_value' => ""
            ];
        }
        return $this->view->render($response, 'doctor/index.html.twig', [
            "languageCode"=>$this->i18n->getLanguageCode(),
            "appName"=>$this->config['main']['appName'],
            "page"=>"doctor_index",
            "i18n"=>$this->i18n->getTranslations(),
            "csrf"=>$csrf,
            "urlPrefix"=>$urlPrefix,
            "tablePage"=>$tablePage,
            "user"=>$user
        ]);
    }

    public function report(Request $request, Response $response, $args = []){
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
                case User::USER_RECORDER:
                    return $response->withRedirect($urlPrefix."/recorder");
                    break;
                case User::USER_UNSPECIFIED:
                    return $response->withRedirect($urlPrefix."/");
                    break;
            }
        }

        $userId = (int) $request->getAttribute("id");
        if(!isset($userId) || (isset($userId) && $userId <= 0)) {
            return $response->withRedirect($urlPrefix."/doctor");
        }

        $csrfArray = $response->getHeader('X-CSRF-Token');
        if($csrfArray != null) {
            $csrf = json_decode($csrfArray[0], true);
        }else{
            $csrf = [
                'csrf_name' => "",
                'csrf_value' => ""
            ];
        }
        return $this->view->render($response, 'doctor/report.html.twig', [
            "languageCode"=>$this->i18n->getLanguageCode(),
            "appName"=>$this->config['main']['appName'],
            "page"=>"doctor_report",
            "i18n"=>$this->i18n->getTranslations(),
            "csrf"=>$csrf,
            "urlPrefix"=>$urlPrefix,
            "user"=>$user,
            "userId"=>$userId
        ]);
    }

    /*
     *
     * POST requests
     *
     */

    public function tableGet(Request $request, Response $response, $args = []){
        $user = new User();
        $status = $user->isUserLoggedIn();

        if(!$status) {
            return $response->withStatus(405)->withJson([
                "status" => 24,
                "message" => StatusCodes::STATUS[24]
            ]);
        }else{
            $user = $user->getCurrentUser();
            if($user->getUserLevel() != User::USER_DOCTOR) {
                return $response->withStatus(405)->withJson([
                    "status" => 25,
                    "message" => StatusCodes::STATUS[25]
                ]);
            }
        }

        $page = (int) $request->getParam('page');
        $searchVal = (string) $request->getParam('searchVal');
        $fromVal = (string) $request->getParam('from');
        $toVal = (string) $request->getParam('to');

        if (!isset($page) || (isset($page) && $page < 1)) {
            return $response->withJson([
                "status" => 26,
                "message" => StatusCodes::STATUS[26]
            ]);
        }
        if (!isset($page) || (isset($page) && strlen($searchVal) <= 0)) {
            $searchVal = null;
        }
        if (!isset($fromVal) || (isset($fromVal) && strlen($fromVal) != 10)) {
            $fromVal = null;
        }
        if (!isset($toVal) || (isset($toVal) && strlen($toVal) != 10)) {
            $toVal = null;
        }

        $patients = $user->getDoctorPatients($page, true, true, $searchVal, $fromVal, $toVal);

        return $response->withJson([
            "status" => 27,
            "message" => StatusCodes::STATUS[27],
            "data" => [
                "page" => $page,
                "rows" => $patients,
                "hasNext" => $user->doctorHasMorePatients($page, $searchVal, $fromVal, $toVal)
            ]
        ]);
    }

}