<?php

namespace App\Controllers;

use App\Models\StatusCodes;
use App\Models\User;
use App\Utils\i18n;
use Slim\Http\Request;
use Slim\Http\Response;

class ApiController {

    private $i18n;
    private $config;
    private $token;

    public function __construct(i18n $i18n) {
        $this->i18n = $i18n;
        $this->config = include(__DIR__."/../../config/core.php");
        $this->token = $this->config['api']['token'];
    }

    public function index(Request $request, Response $response, $args = []){
        return $response->withJson([
            'version'=>$this->config['api']['versionCode']
        ]);
    }

    public function login(Request $request, Response $response, $args = [])
    {
        $parsedBody = $request->getParsedBody();
        if(!isset($parsedBody) || empty($parsedBody)){
            return $response->withJson([
                "status" => 20,
                "message" => StatusCodes::STATUS[20]
            ]);
        }
        if (!isset($parsedBody['token']) || (isset($parsedBody['token']) && $parsedBody['token'] != $this->token)) {
            return $response->withJson([
                "status" => 22,
                "message" => StatusCodes::STATUS[22]
            ]);
        }

        $user = new User();
        if ((isset($parsedBody['login']) && !empty($parsedBody['login'])) && (isset($parsedBody['password']) && !empty($parsedBody['password']))) {
            $login = $parsedBody['login'];
            $pass = $parsedBody['password'];

            $fcmRegToken = null;
            if(isset($parsedBody['fcm_reg_token']) && !empty($parsedBody['fcm_reg_token'])) {
                $fcmRegToken = $parsedBody['fcm_reg_token'];
            }

            $user = $user->auth($login, $pass, true, $fcmRegToken);
            if (!($user instanceof User)) {
                $data = json_decode($user, true);
                if ($data['status'] === 2) {
                    return $response->withJson([
                        "status" => 2,
                        "message" => StatusCodes::STATUS[2]
                    ]);
                } elseif ($data['status'] === 1) {
                    return $response->withJson([
                        "status" => 1,
                        "message" => StatusCodes::STATUS[1]
                    ]);
                }
            }

            return $response->withJson([
                "status" => 18,
                "message" => StatusCodes::STATUS[18],
                "data" => $user->toArray(false)
            ]);
        } else {
            return $response->withJson([
                "status" => 21,
                "message" => StatusCodes::STATUS[21]
            ]);
        }
    }

    public function diagnosis(Request $request, Response $response, $args = [])
    {
        $parsedBody = $request->getParsedBody();
        if(!isset($parsedBody) || empty($parsedBody)){
            return $response->withJson([
                "status" => 20,
                "message" => StatusCodes::STATUS[20]
            ]);
        }
        if (!isset($parsedBody['token']) || (isset($parsedBody['token']) && $parsedBody['token'] != $this->token)) {
            return $response->withJson([
                "status" => 22,
                "message" => StatusCodes::STATUS[22]
            ]);
        }

        $user = new User();
        if ((isset($parsedBody['user_id']) && !empty($parsedBody['user_id'])) && (isset($parsedBody['user_token']) && !empty($parsedBody['user_token']))) {
            $uid = $parsedBody['user_id'];
            $utoken = $parsedBody['user_token'];

            $user = $user->getUser($uid, $utoken);
            if($user != null && $user instanceof User) {
                return $response->withJson([
                    "status" => 27,
                    "message" => StatusCodes::STATUS[27],
                    "data" => $user->getPatientDiagnoses()
                ]);
            } else {
                return $response->withJson([
                    "status" => 53,
                    "message" => StatusCodes::STATUS[53]
                ]);
            }
        } else {
            return $response->withJson([
                "status" => 55,
                "message" => StatusCodes::STATUS[55]
            ]);
        }
    }

    public function recipe(Request $request, Response $response, $args = [])
    {
        $parsedBody = $request->getParsedBody();
        if(!isset($parsedBody) || empty($parsedBody)){
            return $response->withJson([
                "status" => 20,
                "message" => StatusCodes::STATUS[20]
            ]);
        }
        if (!isset($parsedBody['token']) || (isset($parsedBody['token']) && $parsedBody['token'] != $this->token)) {
            return $response->withJson([
                "status" => 22,
                "message" => StatusCodes::STATUS[22]
            ]);
        }

        $user = new User();
        if ((isset($parsedBody['user_id']) && !empty($parsedBody['user_id'])) && (isset($parsedBody['user_token']) && !empty($parsedBody['user_token']))) {
            $uid = $parsedBody['user_id'];
            $utoken = $parsedBody['user_token'];

            if(!isset($parsedBody['diagnosis_id']) || empty($parsedBody['diagnosis_id'])) {
                return $response->withJson([
                    "status" => 45,
                    "message" => StatusCodes::STATUS[45]
                ]);
            }

            $diagnosisId = $parsedBody['diagnosis_id'];

            $user = $user->getUser($uid, $utoken);
            if($user != null && $user instanceof User) {
                return $response->withJson([
                    "status" => 27,
                    "message" => StatusCodes::STATUS[27],
                    "data" => $user->getPatientRecipes($diagnosisId)
                ]);
            } else {
                return $response->withJson([
                    "status" => 53,
                    "message" => StatusCodes::STATUS[53]
                ]);
            }
        } else {
            return $response->withJson([
                "status" => 55,
                "message" => StatusCodes::STATUS[55]
            ]);
        }
    }

    public function fcmTokenUpdate(Request $request, Response $response, $args = [])
    {
        $parsedBody = $request->getParsedBody();
        if(!isset($parsedBody) || empty($parsedBody)){
            return $response->withJson([
                "status" => 20,
                "message" => StatusCodes::STATUS[20]
            ]);
        }
        if (!isset($parsedBody['token']) || (isset($parsedBody['token']) && $parsedBody['token'] != $this->token)) {
            return $response->withJson([
                "status" => 22,
                "message" => StatusCodes::STATUS[22]
            ]);
        }

        $user = new User();
        if ((isset($parsedBody['user_id']) && !empty($parsedBody['user_id'])) && (isset($parsedBody['user_token']) && !empty($parsedBody['user_token']))) {
            $uid = $parsedBody['user_id'];
            $utoken = $parsedBody['user_token'];

            if(!isset($parsedBody['fcm_reg_token']) || empty($parsedBody['fcm_reg_token'])) {
                return $response->withJson([
                    "status" => 59,
                    "message" => StatusCodes::STATUS[59]
                ]);
            }

            $fcmRegToken = $parsedBody['fcm_reg_token'];

            $user = $user->getUser($uid, $utoken);
            if($user != null && $user instanceof User) {
                if($user->updateFCMRegistrationToken($uid, $utoken, $fcmRegToken)) {
                    $user->setFcmRegToken($fcmRegToken);
                    return $response->withJson([
                        "status" => 61,
                        "message" => StatusCodes::STATUS[61],
                        "data" => [
                            "newToken" => $user->getFcmRegToken()
                        ]
                    ]);
                }else{
                    return $response->withJson([
                        "status" => 62,
                        "message" => StatusCodes::STATUS[62]
                    ]);
                }
            } else {
                return $response->withJson([
                    "status" => 53,
                    "message" => StatusCodes::STATUS[53]
                ]);
            }
        } else {
            return $response->withJson([
                "status" => 55,
                "message" => StatusCodes::STATUS[55]
            ]);
        }
    }
}