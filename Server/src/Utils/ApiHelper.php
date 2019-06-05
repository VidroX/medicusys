<?php

namespace App\Utils;

class ApiHelper {
    private $db;
    private $config;

    /**
     * ApiHelper constructor.
     */
    public function __construct() {
        $db = new Database();

        $this->db = $db->getDatabase();
        $this->config = include(__DIR__."/../../config/core.php");
    }

    /**
     * Checks given language if it's suitable for API, if not returns the default language
     *
     * @param string $language
     *
     * @return string
     */
    public static function checkLanguageForAPI($language) {
        $config = include(__DIR__."/../../config/core.php");

        $languages = $config['symptoms_api']['availableLanguages'];
        $defaultLanguage = $config['symptoms_api']['defaultLanguage'];

        if(in_array($language, $languages)) {
            return $language;
        }else{
            return $defaultLanguage;
        }
    }

    /**
     * Send POST request
     *
     * @param string $url
     * @param array $data
     * @param string $headers
     *
     * @return array|bool
     */
    private function sendPostRequest($url, $data = null, $headers = null) {
        $handler = curl_init();

        curl_setopt($handler, CURLOPT_POST, true);
        curl_setopt($handler, CURLOPT_URL, $url);
        curl_setopt($handler, CURLOPT_SSL_VERIFYPEER, !$this->config['main']['debugMode']);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);

        if($headers != null && isset($headers) && strlen($headers) > 0) {
            curl_setopt($handler, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $headers));
        }

        if($data != null && isset($data) && count($data) > 0) {
            curl_setopt($handler, CURLOPT_POSTFIELDS, http_build_query($data));
        }else{
            curl_setopt($handler, CURLOPT_POSTFIELDS, '');
        }

        $obj = curl_exec($handler);
        $status = curl_getinfo($handler)['http_code'];

        curl_close($handler);

        if($status != 200) {
            if($this->config['main']['debugMode']) {
                echo $obj;
            }
            return false;
        }

        return $obj ? json_decode($obj, true) : false;
    }

    /**
     * Send GET request
     *
     * @param string $url
     * @param array $data
     * @param string $headers
     *
     * @return array|bool
     */
    private function sendGetRequest($url, $data = null, $headers = null) {
        $handler = curl_init();

        curl_setopt($handler, CURLOPT_URL, $url);
        curl_setopt($handler, CURLOPT_SSL_VERIFYPEER, !$this->config['main']['debugMode']);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);

        if($headers != null && isset($headers) && strlen($headers) > 0) {
            curl_setopt($handler, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $headers));
        }

        $obj = curl_exec($handler);
        $status = curl_getinfo($handler)['http_code'];

        curl_close($handler);

        if($status != 200) {
            if($this->config['main']['debugMode']) {
                echo $obj;
            }
            return false;
        }

        return $obj ? json_decode($obj, true) : false;
    }

    /**
     * Get token from ApiMedic
     *
     * @return string
     */
    private function getToken() {
        try {
            if(isset($_SESSION['ApiMedicaToken']) && strlen($_SESSION['ApiMedicaToken']) > 0 && isset($_SESSION['ValidThrough'])){
                $dateTime = new \DateTime();
                $dateTime2 = new \DateTime();

                $validThrough = $dateTime->setTimestamp($_SESSION['ValidThrough']);

                if($dateTime2 < $validThrough){
                    return (string) $_SESSION['ApiMedicaToken'];
                }
            }
        }catch (\Exception $e) {
            if($this->config['main']['debugMode']) {
                echo $e->getMessage();
                return null;
            }
        }

        $authURL = $this->config['symptoms_api']['urlAuth'] . '/login';
        $username = $this->config['symptoms_api']['user'];
        $password = $this->config['symptoms_api']['password'];

        $computedHash = base64_encode(hash_hmac('md5', $authURL, $password, true));

        $headers = 'Authorization: Bearer ' . $username . ':' . $computedHash;

        $data = [
            'format' => 'json'
        ];

        $obj = $this->sendPostRequest($authURL, $data, $headers);

        if($obj) {
            try {
                $dateTime = new \DateTime();
                $validThrough = (int) $obj['ValidThrough'];

                $dateTime->setTimestamp($dateTime->getTimestamp() + $validThrough);

                $_SESSION['ApiMedicaToken'] = $obj['Token'];
                $_SESSION['ValidThrough'] = (int) $dateTime->getTimestamp();

                return (string) $obj['Token'];
            }catch (\Exception $e) {
                if($this->config['main']['debugMode']) {
                    echo $e->getMessage();
                }
                return null;
            }
        }

        return null;
    }

    /**
     * Refreshes local symptoms database from ApiMedic
     *
     * @param string $language
     *
     * @return bool (true = success, false = otherwise)
     */
    public function refreshSymptomsDatabase($language = null) {
        $token = $this->getToken();
        if(isset($token) && $token != null && strlen($token) > 0) {
            $url = $this->config['symptoms_api']['urlHealthService'] . '/symptoms';

            $db = new Database();
            $dbh = $db->getDatabase();

            if($language == null) {
                $success = false;
                foreach($this->config['symptoms_api']['availableLanguages'] as $lang) {
                    $extraArgs = '?token='.$token.'&format=json&language='.$lang;

                    $symptoms = $this->sendGetRequest($url.$extraArgs);

                    if($symptoms) {
                        foreach ($symptoms as $key=>$symptom) {
                            try {
                                $query = $dbh->prepare("INSERT INTO symptoms (api_id, name) VALUES (:id1, :symptom_name1) ON DUPLICATE KEY UPDATE api_id = :id2, name = :symptom_name2");

                                $dbh->beginTransaction();

                                $query->execute([
                                    ':id1' => (int) $symptom['ID'],
                                    ':id2' => (int) $symptom['ID'],
                                    ':symptom_name1' => (string) $symptom['Name'],
                                    ':symptom_name2' => (string) $symptom['Name']
                                ]);

                                $dbh->commit();

                                $success = true;
                            } catch (\PDOException $ex) {
                                $dbh->rollBack();

                                if ($this->config['main']['debugMode']) {
                                    echo $ex->getMessage();
                                }

                                return false;
                            }
                        }
                    }
                }
                return $success;
            } else {
                $lang = ApiHelper::checkLanguageForAPI($language);

                if($lang != null) {
                    $extraArgs = '?token=' . $token . '&format=json&language=' . $lang;

                    $symptoms = $this->sendGetRequest($url . $extraArgs);

                    if ($symptoms) {
                        $success = false;

                        foreach ($symptoms as $key => $symptom) {
                            try {
                                $query = $dbh->prepare("INSERT INTO symptoms (api_id, name) VALUES (:id1, :symptom_name1) ON DUPLICATE KEY UPDATE api_id = :id2, name = :symptom_name2");

                                $dbh->beginTransaction();

                                $query->execute([
                                    ':id1' => (int)$symptom['ID'],
                                    ':id2' => (int)$symptom['ID'],
                                    ':symptom_name1' => (string)$symptom['Name'],
                                    ':symptom_name2' => (string)$symptom['Name']
                                ]);

                                $dbh->commit();

                                $success = true;
                            } catch (\PDOException $ex) {
                                $dbh->rollBack();

                                if ($this->config['main']['debugMode']) {
                                    echo $ex->getMessage();
                                }

                                return false;
                            }
                        }
                        return $success;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Get symptoms from database
     *
     * @param string $needle
     *
     * @return array
     */
    public function getSymptoms($needle = null) {
        $db = new Database();
        $dbh = $db->getDatabase();

        if($needle == null) {
            $query = $dbh->prepare("SELECT symptoms.name FROM symptoms");
            $query->execute();
        }else{
            $query = $dbh->prepare("SELECT symptoms.name FROM symptoms WHERE name LIKE :needle");
            $query->execute([
                ':needle' => $needle.'%'
            ]);
        }

        $symptoms = [];

        while ($row = $query->fetch()) {
            $symptoms[] = $row['name'];
        }

        return $symptoms;
    }
}