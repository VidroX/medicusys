<?php

namespace App\Controllers;

use App\Models\StatusCodes;
use App\Models\User;
use App\Utils\ApiHelper;
use App\Utils\i18n;
use Slim\Exception\NotFoundException;
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
        if(!$user->checkAccessToPatient($userId)) {
            throw new NotFoundException($request, $response);
        }

        $patient = $user->getPatientById($userId);

        $patientDiagnoses = $user->getDiagnoses($patient->getId());
        krsort($patientDiagnoses);

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
            "userId"=>$userId,
            "patient"=>$patient,
            "patientDiagnoses" => $patientDiagnoses
        ]);
    }

    public function reportInfo(Request $request, Response $response, $args = []){
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
        if(!$user->checkAccessToPatient($userId)) {
            throw new NotFoundException($request, $response);
        }
        $diagnosisId = (int) $request->getAttribute("diagnosisId");
        if(!isset($diagnosisId) || (isset($diagnosisId) && $diagnosisId <= 0)) {
            return $response->withRedirect($urlPrefix."/doctor/report/".$userId);
        }
        if(!$user->checkPatientAccessToDiagnosis($userId, $diagnosisId)) {
            throw new NotFoundException($request, $response);
        }

        $patient = $user->getPatientById($userId);

        $diagnosis = $user->getDiagnoses($patient->getId())[$diagnosisId];

        $csrfArray = $response->getHeader('X-CSRF-Token');
        if($csrfArray != null) {
            $csrf = json_decode($csrfArray[0], true);
        }else{
            $csrf = [
                'csrf_name' => "",
                'csrf_value' => ""
            ];
        }
        return $this->view->render($response, 'doctor/report_info.html.twig', [
            "languageCode"=>$this->i18n->getLanguageCode(),
            "appName"=>$this->config['main']['appName'],
            "page"=>"doctor_report_info",
            "i18n"=>$this->i18n->getTranslations(),
            "csrf"=>$csrf,
            "urlPrefix"=>$urlPrefix,
            "user"=>$user,
            "userId"=>$userId,
            "patient"=>$patient,
            "diagnosis"=>$diagnosis
        ]);
    }

    public function recipeInfo(Request $request, Response $response, $args = []){
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
        if(!$user->checkAccessToPatient($userId)) {
            throw new NotFoundException($request, $response);
        }
        $diagnosisId = (int) $request->getAttribute("diagnosisId");
        if(!isset($diagnosisId) || (isset($diagnosisId) && $diagnosisId <= 0)) {
            return $response->withRedirect($urlPrefix."/doctor/report/".$userId);
        }
        if(!$user->checkPatientAccessToDiagnosis($userId, $diagnosisId)) {
            throw new NotFoundException($request, $response);
        }

        $patient = $user->getPatientById($userId);
        $recipes = $user->getRecipes($patient->getId(), $diagnosisId);

        $csrfArray = $response->getHeader('X-CSRF-Token');
        if($csrfArray != null) {
            $csrf = json_decode($csrfArray[0], true);
        }else{
            $csrf = [
                'csrf_name' => "",
                'csrf_value' => ""
            ];
        }
        return $this->view->render($response, 'doctor/recipe_info.html.twig', [
            "languageCode"=>$this->i18n->getLanguageCode(),
            "appName"=>$this->config['main']['appName'],
            "page"=>"doctor_report_info",
            "i18n"=>$this->i18n->getTranslations(),
            "csrf"=>$csrf,
            "urlPrefix"=>$urlPrefix,
            "user"=>$user,
            "userId"=>$userId,
            "patient"=>$patient,
            "recipes" => $recipes,
            "diagnosisId" => $diagnosisId
        ]);
    }

    public function recipeAdd(Request $request, Response $response, $args = []){
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
        if(!$user->checkAccessToPatient($userId)) {
            throw new NotFoundException($request, $response);
        }
        $diagnosisId = (int) $request->getAttribute("diagnosisId");
        if(!isset($diagnosisId) || (isset($diagnosisId) && $diagnosisId <= 0)) {
            return $response->withRedirect($urlPrefix."/doctor/report/".$userId);
        }
        if(!$user->checkPatientAccessToDiagnosis($userId, $diagnosisId)) {
            throw new NotFoundException($request, $response);
        }

        $patient = $user->getPatientById($userId);

        $csrfArray = $response->getHeader('X-CSRF-Token');
        if($csrfArray != null) {
            $csrf = json_decode($csrfArray[0], true);
        }else{
            $csrf = [
                'csrf_name' => "",
                'csrf_value' => ""
            ];
        }
        return $this->view->render($response, 'doctor/recipe_add.html.twig', [
            "languageCode"=>$this->i18n->getLanguageCode(),
            "appName"=>$this->config['main']['appName'],
            "page"=>"doctor_report_info",
            "i18n"=>$this->i18n->getTranslations(),
            "csrf"=>$csrf,
            "urlPrefix"=>$urlPrefix,
            "user"=>$user,
            "userId"=>$userId,
            "patient"=>$patient,
            "diagnosisId" => $diagnosisId
        ]);
    }

    public function recipeEdit(Request $request, Response $response, $args = []){
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
        if(!$user->checkAccessToPatient($userId)) {
            throw new NotFoundException($request, $response);
        }
        $diagnosisId = (int) $request->getAttribute("diagnosisId");
        if(!isset($diagnosisId) || (isset($diagnosisId) && $diagnosisId <= 0)) {
            return $response->withRedirect($urlPrefix."/doctor/report/".$userId);
        }
        if(!$user->checkPatientAccessToDiagnosis($userId, $diagnosisId)) {
            throw new NotFoundException($request, $response);
        }
        $recipeId = (int) $request->getAttribute("recipeId");
        if(!isset($recipeId) || (isset($recipeId) && $recipeId <= 0)) {
            return $response->withRedirect($urlPrefix."/doctor/report/".$userId);
        }
        if(!$user->checkPatientAccessToRecipe($userId, $recipeId)) {
            throw new NotFoundException($request, $response);
        }

        $patient = $user->getPatientById($userId);
        $recipe = $user->getRecipe($patient->getId(), $diagnosisId, $recipeId);

        $csrfArray = $response->getHeader('X-CSRF-Token');
        if($csrfArray != null) {
            $csrf = json_decode($csrfArray[0], true);
        }else{
            $csrf = [
                'csrf_name' => "",
                'csrf_value' => ""
            ];
        }
        return $this->view->render($response, 'doctor/recipe_edit.html.twig', [
            "languageCode"=>$this->i18n->getLanguageCode(),
            "appName"=>$this->config['main']['appName'],
            "page"=>"doctor_report_info",
            "i18n"=>$this->i18n->getTranslations(),
            "csrf"=>$csrf,
            "urlPrefix"=>$urlPrefix,
            "user"=>$user,
            "userId"=>$userId,
            "patient"=>$patient,
            "recipe" => $recipe,
            "diagnosisId" => $diagnosisId,
            "recipeId" => $recipeId
        ]);
    }

    public function reportAdd(Request $request, Response $response, $args = []){
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
        if(!$user->checkAccessToPatient($userId)) {
            throw new NotFoundException($request, $response);
        }

        $patient = $user->getPatientById($userId);

        $csrfArray = $response->getHeader('X-CSRF-Token');
        if($csrfArray != null) {
            $csrf = json_decode($csrfArray[0], true);
        }else{
            $csrf = [
                'csrf_name' => "",
                'csrf_value' => ""
            ];
        }

        $language = $this->i18n->getLanguageCode();
        if($language == 'en') {
            $language = 'en-gb';
        }else{
            $language = $this->config['symptoms_api']['defaultLanguage'];
        }

        return $this->view->render($response, 'doctor/report_add.html.twig', [
            "languageCode"=>$this->i18n->getLanguageCode(),
            "languageCodeForApi"=>$language,
            "appName"=>$this->config['main']['appName'],
            "page"=>"doctor_report_add",
            "i18n"=>$this->i18n->getTranslations(),
            "csrf"=>$csrf,
            "urlPrefix"=>$urlPrefix,
            "user"=>$user,
            "userId"=>$userId,
            "patient"=>$patient
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

    public function postReportAdd(Request $request, Response $response, $args = []){
        $user = new User();
        $status = $user->isUserLoggedIn();

        if(!$status) {
            return $response->withStatus(405)->withJson([
                'status'=>24,
                'message'=>StatusCodes::STATUS[24]
            ]);
        }else{
            $user = $user->getCurrentUser();
            if($user->getUserLevel() != User::USER_DOCTOR) {
                return $response->withStatus(405)->withJson([
                    'status'=>25,
                    'message'=>StatusCodes::STATUS[25]
                ]);
            }
        }

        $userId = (int) $request->getParam("patient_user_id");
        if(!isset($userId) || (isset($userId) && $userId <= 0)) {
            return $response->withJson([
                'status'=>31,
                'message'=>StatusCodes::STATUS[31]
            ]);
        }

        $diagnosis = (string) $request->getParam("diagnosis");
        if(!isset($diagnosis) || (isset($diagnosis) && strlen($diagnosis) <= 0)) {
            return $response->withJson([
                'status'=>37,
                'message'=>StatusCodes::STATUS[37]
            ]);
        }

        $symptoms = (string) $request->getParam("symptoms");
        $symptomsArr = [];
        if(isset($symptoms) && strlen($symptoms) > 0) {
            $symptomsArr = json_decode($symptoms, true);
        }

        if(!$user->checkAccessToPatient($userId)) {
            return $response->withJson([
                'status'=>30,
                'message'=>StatusCodes::STATUS[30]
            ]);
        }

        $diagnosisData = [
            'diagnosis' => $diagnosis,
            'symptoms' => count($symptomsArr) > 0 ? $symptomsArr : null
        ];

        if($user->addDiagnosis($userId, $diagnosisData)){
            return $response->withJson([
                'status' => 27,
                'message' => StatusCodes::STATUS[27]
            ]);
        }else{
            return $response->withJson([
                'status' => 38,
                'message' => StatusCodes::STATUS[38]
            ]);
        }
    }

    public function postReportDelete(Request $request, Response $response, $args = []){
        $user = new User();
        $status = $user->isUserLoggedIn();

        if(!$status) {
            return $response->withStatus(405)->withJson([
                'status'=>24,
                'message'=>StatusCodes::STATUS[24]
            ]);
        }else{
            $user = $user->getCurrentUser();
            if($user->getUserLevel() != User::USER_DOCTOR) {
                return $response->withStatus(405)->withJson([
                    'status'=>25,
                    'message'=>StatusCodes::STATUS[25]
                ]);
            }
        }

        $diagnosisId = (int) $request->getParam("diagnosis_id");
        if(!isset($diagnosisId) || (isset($diagnosisId) && $diagnosisId <= 0)) {
            return $response->withJson([
                'status'=>37,
                'message'=>StatusCodes::STATUS[37]
            ]);
        }

        if($user->deleteDiagnosis($diagnosisId)){
            return $response->withJson([
                'status' => 27,
                'message' => StatusCodes::STATUS[27]
            ]);
        }else{
            return $response->withJson([
                'status' => 39,
                'message' => StatusCodes::STATUS[39]
            ]);
        }
    }

    public function getSymptoms(Request $request, Response $response, $args = []){
        $user = new User();
        $status = $user->isUserLoggedIn();

        if(!$status) {
            return $response->withStatus(405)->withJson([
                'status'=>24,
                'message'=>StatusCodes::STATUS[24]
            ]);
        }else{
            $user = $user->getCurrentUser();
            if($user->getUserLevel() != User::USER_DOCTOR) {
                return $response->withStatus(405)->withJson([
                    'status'=>25,
                    'message'=>StatusCodes::STATUS[25]
                ]);
            }
        }

        $apiHelper = new ApiHelper();

        return $response->withJson($apiHelper->getSymptoms());
    }

    public function getDiagnosis(Request $request, Response $response, $args = []){
        $user = new User();
        $status = $user->isUserLoggedIn();

        if(!$status) {
            return $response->withStatus(405)->withJson([
                'status'=>24,
                'message'=>StatusCodes::STATUS[24]
            ]);
        }else{
            $user = $user->getCurrentUser();
            if($user->getUserLevel() != User::USER_DOCTOR) {
                return $response->withStatus(405)->withJson([
                    'status'=>25,
                    'message'=>StatusCodes::STATUS[25]
                ]);
            }
        }
        $symptomIds = (string) $request->getParam("symptom_ids");
        if(!isset($symptomIds) || (isset($symptomIds) && strlen($symptomIds) <= 0)) {
            return $response->withJson([
                'status'=>34,
                'message'=>StatusCodes::STATUS[34]
            ]);
        }
        $gender = (string) $request->getParam("patient_gender");
        if(!isset($gender) || (isset($gender) && strlen($gender) <= 0)) {
            return $response->withJson([
                'status'=>33,
                'message'=>StatusCodes::STATUS[33]
            ]);
        }
        $birthDate = (string) $request->getParam("patient_birthdate");
        if(!isset($birthDate) || (isset($birthDate) && strlen($birthDate) <= 0)) {
            return $response->withJson([
                'status'=>7,
                'message'=>StatusCodes::STATUS[7]
            ]);
        }
        $language = (string) $request->getParam("language");
        if(!isset($language) || (isset($language) && strlen($language) <= 0)) {
            return $response->withJson([
                'status'=>35,
                'message'=>StatusCodes::STATUS[35]
            ]);
        }

        $apiHelper = new ApiHelper();

        $date = (int) explode('-', $birthDate)[0];

        $diagnoses = $apiHelper->getDiagnoses($symptomIds, $gender, $date, $language);

        if($diagnoses != null) {
            return $response->withJson([
                'status' => 27,
                'message' => StatusCodes::STATUS[27],
                'data' => $diagnoses
            ]);
        }else{
            return $response->withJson([
                'status'=>28,
                'message'=>StatusCodes::STATUS[28]
            ]);
        }
    }

    public function postAddRecipe(Request $request, Response $response, $args = []){
        $user = new User();
        $status = $user->isUserLoggedIn();

        if(!$status) {
            return $response->withStatus(405)->withJson([
                'status'=>24,
                'message'=>StatusCodes::STATUS[24]
            ]);
        }else{
            $user = $user->getCurrentUser();
            if($user->getUserLevel() != User::USER_DOCTOR) {
                return $response->withStatus(405)->withJson([
                    'status'=>25,
                    'message'=>StatusCodes::STATUS[25]
                ]);
            }
        }

        $userId = (int) $request->getParam("patient_id");
        if(!isset($userId) || (isset($userId) && strlen($userId) <= 0)) {
            return $response->withJson([
                'status'=>32,
                'message'=>StatusCodes::STATUS[32]
            ]);
        }
        if(!$user->checkAccessToPatient($userId)) {
            return $response->withJson([
                'status'=>30,
                'message'=>StatusCodes::STATUS[30]
            ]);
        }

        $diagnosisId = (int) $request->getParam("diagnosis_id");
        if(!isset($diagnosisId) || (isset($diagnosisId) && $diagnosisId <= 0)) {
            return $response->withJson([
                'status'=>45,
                'message'=>StatusCodes::STATUS[45]
            ]);
        }

        $rp = (string) $request->getParam("rp");
        if(!isset($rp) || (isset($rp) && strlen($rp) <= 0)) {
            return $response->withJson([
                'status'=>46,
                'message'=>StatusCodes::STATUS[46]
            ]);
        }

        $dtdn = (string) $request->getParam("dtdn");
        if(!isset($dtdn) || (isset($dtdn) && strlen($dtdn) <= 0)) {
            return $response->withJson([
                'status'=>47,
                'message'=>StatusCodes::STATUS[47]
            ]);
        }

        $signa = (string) $request->getParam("signa");
        if(!isset($signa) || (isset($signa) && strlen($signa) <= 0)) {
            return $response->withJson([
                'status'=>48,
                'message'=>StatusCodes::STATUS[48]
            ]);
        }

        if(!$user->checkAccessToPatient($userId)) {
            return $response->withJson([
                'status'=>30,
                'message'=>StatusCodes::STATUS[30]
            ]);
        }

        if($user->addRecipe($diagnosisId, $rp, $dtdn, $signa)){
            return $response->withJson([
                'status' => 27,
                'message' => StatusCodes::STATUS[27]
            ]);
        }else{
            return $response->withJson([
                'status' => 49,
                'message' => StatusCodes::STATUS[49]
            ]);
        }
    }

    public function postEditRecipe(Request $request, Response $response, $args = []){
        $user = new User();
        $status = $user->isUserLoggedIn();

        if(!$status) {
            return $response->withStatus(405)->withJson([
                'status'=>24,
                'message'=>StatusCodes::STATUS[24]
            ]);
        }else{
            $user = $user->getCurrentUser();
            if($user->getUserLevel() != User::USER_DOCTOR) {
                return $response->withStatus(405)->withJson([
                    'status'=>25,
                    'message'=>StatusCodes::STATUS[25]
                ]);
            }
        }

        $userId = (int) $request->getParam("patient_id");
        if(!isset($userId) || (isset($userId) && strlen($userId) <= 0)) {
            return $response->withJson([
                'status'=>32,
                'message'=>StatusCodes::STATUS[32]
            ]);
        }
        if(!$user->checkAccessToPatient($userId)) {
            return $response->withJson([
                'status'=>30,
                'message'=>StatusCodes::STATUS[30]
            ]);
        }

        $recipeId = (int) $request->getParam("recipe_id");
        if(!isset($recipeId) || (isset($recipeId) && $recipeId <= 0)) {
            return $response->withJson([
                'status'=>51,
                'message'=>StatusCodes::STATUS[51]
            ]);
        }

        $rp = (string) $request->getParam("rp");
        if(!isset($rp) || (isset($rp) && strlen($rp) <= 0)) {
            return $response->withJson([
                'status'=>46,
                'message'=>StatusCodes::STATUS[46]
            ]);
        }

        $dtdn = (string) $request->getParam("dtdn");
        if(!isset($dtdn) || (isset($dtdn) && strlen($dtdn) <= 0)) {
            return $response->withJson([
                'status'=>47,
                'message'=>StatusCodes::STATUS[47]
            ]);
        }

        $signa = (string) $request->getParam("signa");
        if(!isset($signa) || (isset($signa) && strlen($signa) <= 0)) {
            return $response->withJson([
                'status'=>48,
                'message'=>StatusCodes::STATUS[48]
            ]);
        }

        if(!$user->checkAccessToPatient($userId)) {
            return $response->withJson([
                'status'=>30,
                'message'=>StatusCodes::STATUS[30]
            ]);
        }

        if($user->updateRecipe($recipeId, $rp, $dtdn, $signa)){
            return $response->withJson([
                'status' => 27,
                'message' => StatusCodes::STATUS[27]
            ]);
        }else{
            return $response->withJson([
                'status' => 49,
                'message' => StatusCodes::STATUS[50]
            ]);
        }
    }

    public function postDeleteRecipe(Request $request, Response $response, $args = []){
        $user = new User();
        $status = $user->isUserLoggedIn();

        if(!$status) {
            return $response->withStatus(405)->withJson([
                'status'=>24,
                'message'=>StatusCodes::STATUS[24]
            ]);
        }else{
            $user = $user->getCurrentUser();
            if($user->getUserLevel() != User::USER_DOCTOR) {
                return $response->withStatus(405)->withJson([
                    'status'=>25,
                    'message'=>StatusCodes::STATUS[25]
                ]);
            }
        }

        $userId = (int) $request->getParam("patient_id");
        if(!isset($userId) || (isset($userId) && strlen($userId) <= 0)) {
            return $response->withJson([
                'status'=>32,
                'message'=>StatusCodes::STATUS[32]
            ]);
        }
        if(!$user->checkAccessToPatient($userId)) {
            return $response->withJson([
                'status'=>30,
                'message'=>StatusCodes::STATUS[30]
            ]);
        }

        $recipeId = (int) $request->getParam("recipe_id");
        if(!isset($recipeId) || (isset($recipeId) && $recipeId <= 0)) {
            return $response->withJson([
                'status'=>51,
                'message'=>StatusCodes::STATUS[51]
            ]);
        }

        if(!$user->checkAccessToPatient($userId)) {
            return $response->withJson([
                'status'=>30,
                'message'=>StatusCodes::STATUS[30]
            ]);
        }

        if($user->deleteRecipe($recipeId)){
            return $response->withJson([
                'status' => 27,
                'message' => StatusCodes::STATUS[27]
            ]);
        }else{
            return $response->withJson([
                'status' => 52,
                'message' => StatusCodes::STATUS[52]
            ]);
        }
    }

}