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

    public function __construct(i18n $i18n) {
        $this->i18n = $i18n;
        $this->config = include(__DIR__."/../../config/core.php");
    }

    public function index(Request $request, Response $response, $args = []){
        return $response->withJson([
            'version'=>$this->config['api']['versionCode']
        ]);
    }

    public function login(Request $request, Response $response, $args = []){
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

                $user = $user->auth($login, $pass, true);
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
                    "data" => $user->getData()
                ]);
            }else{
                return $response->withJson([
                    "status" => 21,
                    "message" => StatusCodes::STATUS[21]
                ]);
            }
        }else{
            return $response->withJson([
                "status" => 20,
                "message" => StatusCodes::STATUS[20]
            ]);
        }
    }
}