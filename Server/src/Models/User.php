<?php

namespace App\Models;

use App\Utils\ArrayUtil;
use App\Utils\Database;

class User {
    const USER_UNSPECIFIED = 0;
    const USER_PATIENT = 1;
    const USER_DOCTOR = 2;
    const USER_RECORDER = 3;

    const GENDER_MALE = 1;
    const GENDER_FEMALE = 2;

    private $id;
    private $firstName;
    private $lastName;
    private $patronymic;
    private $birthDate;
    private $gender;
    private $mobilePhone;
    private $email;
    private $homeAddress;
    private $userToken;
    private $activated;
    private $userLevel;
    private $fcmRegToken;
    private $telegramChatId;

    /**
     * User constructor.
     * @param int $id
     * @param string $firstName
     * @param string $lastName
     * @param string $patronymic
     * @param string $birthDate
     * @param int $gender
     * @param string $mobilePhone
     * @param string $email
     * @param string $homeAddress
     * @param boolean $activated
     * @param int $userLevel
     * @param string $fcmRegToken
     * @param string $userToken
     * @param string $telegramChatId
     */
    public function __construct($id = null, $firstName = null, $lastName = null, $patronymic = null, $birthDate = null, $gender = 1, $mobilePhone = null, $email = null, $homeAddress = null, $activated = false, $userLevel = self::USER_UNSPECIFIED, $fcmRegToken = null, $userToken = null, $telegramChatId = null)
    {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->patronymic = $patronymic;
        $this->birthDate = $birthDate;
        $this->mobilePhone = $mobilePhone;
        $this->email = $email;
        $this->homeAddress = $homeAddress;
        $this->activated = $activated;
        $this->userLevel = $userLevel;
        $this->userToken = $userToken;
        $this->gender = $gender;
        $this->fcmRegToken = $fcmRegToken;
        $this->telegramChatId = $telegramChatId;
    }

    /**
     * @param int $id User ID
     * @return User User object
     */
    public function getUserById($id) {
        $db = new Database();

        $query = $db->getDatabase()->prepare(
            "
                    SELECT 
                        users.*,
                        IFNULL(r.user_id, -1) AS user_recorder,
                        IFNULL(d.user_id, -1) AS user_doctor,
                        IFNULL(p.user_id, -1) AS user_patient
                    FROM users 
                        LEFT JOIN patients p on users.id = p.user_id 
                        LEFT JOIN doctors d on users.id = d.user_id 
                        LEFT JOIN recorders r on users.id = r.user_id 
                    WHERE users.id = :id LIMIT 1
            "
        );
        $query->execute([
            ":id"=>$id
        ]);

        if ($row = $query->fetch()) {
            $this->setId($row['id']);
            $this->setFirstName($row['first_name']);
            $this->setLastName($row['last_name']);
            $this->setPatronymic($row['patronymic']);
            $this->setBirthDate($row['birthdate']);
            $this->setGender($row['gender']);
            $this->setMobilePhone($row['mobilephone']);
            $this->setEmail($row['email']);
            $this->setHomeAddress($row['home_address']);
            $this->setUserToken(null);
            $this->setFcmRegToken($row['fcm_reg_token']);
            $this->setTelegramChatId($row['telegram_chat_id']);
            $this->setActivated($row['activated'] == 1);

            if($row['user_recorder'] != -1){
                $this->setUserLevel(self::USER_RECORDER);
            }elseif($row['user_doctor'] != -1){
                $this->setUserLevel(self::USER_DOCTOR);
            }elseif ($row['user_patient'] != -1){
                $this->setUserLevel(self::USER_PATIENT);
            }else{
                $this->setUserLevel(self::USER_UNSPECIFIED);
            }
        }

        return $this;
    }

    /**
     * @return User User object
     */
    public function getCurrentUser() {
        if(session_status() == PHP_SESSION_ACTIVE && isset($_SESSION['USER']) && !empty($_SESSION['USER']) && $_SESSION['USER'] != null && $_SESSION['USER'] instanceof User){
            return $_SESSION['USER'];
        }else{
            return null;
        }
    }

    /**
     * Check if user is logged in
     *
     * @param bool $api
     *
     * @return bool
     */
    public function isUserLoggedIn($api = false) {
        if($api) {
            return $this != null;
        }else{
            return $this->getCurrentUser() != null;
        }
    }

    /**
     * Get doctor's specialty
     *
     * @return string
     */
    public function getSpecialty() {
        if($this->isUserLoggedIn()){
            if($this->getUserLevel() == self::USER_DOCTOR){
                $db = new Database();

                $query = $db->getDatabase()->prepare("SELECT specialty FROM doctors WHERE user_id = :id");
                $query->execute([
                    ":id"=>$this->getId()
                ]);

                if($row = $query->execute()){
                    return $row['specialty'];
                }
            }
        }

        return null;
    }

    /**
     * Login user in the system
     *
     * @param string $login User's E-Mail or mobile phone
     * @param string $pass User's password
     * @param bool $api if function is being used in the API
     * @param string $fcmRegToken Firebase Cloud Messaging Registration Token
     * @param string $tChatId Telegram chat id (for bot)
     *
     * @return mixed
     */
    public function auth($login, $pass, $api = false, $fcmRegToken = null, $tChatId = null) {
        if($login == null || $pass == null) return null;

        $db = new Database();
        $dbh = $db->getDatabase();

        $type = 0;

        if(preg_match("/^[0-9]{10}$|^[0-9]{12}$/", $login)){
            $type = 1;
            if(preg_match("/^[0-9]{10}$/", $login)){
                $login = '38'.$login;
            }
        }

        if($api) {
            $secureKey = bin2hex(openssl_random_pseudo_bytes(256));
            $userToken = hash_hmac('sha3-256', $login, $secureKey);

            if($fcmRegToken != null) {
                $updateQuery = $dbh->prepare("UPDATE users SET token = :token, fcm_reg_token = :fcmRegToken WHERE email = :login1 OR mobilephone = :login2");
                $updateQuery->execute([
                    ':token' => $userToken,
                    ':fcmRegToken' => $fcmRegToken,
                    ':login1' => $login,
                    ':login2' => $login
                ]);
            }else{
                $updateQuery = $dbh->prepare("UPDATE users SET token = :token WHERE email = :login1 OR mobilephone = :login2");
                $updateQuery->execute([
                    ':token' => $userToken,
                    ':login1' => $login,
                    ':login2' => $login
                ]);
            }
        }

        if($tChatId != null) {
            $updateQuery = $dbh->prepare("UPDATE users SET telegram_chat_id = :tChatId WHERE email = :login1 OR mobilephone = :login2");
            $updateQuery->execute([
                ':tChatId' => $tChatId,
                ':login1' => $login,
                ':login2' => $login
            ]);
        }

        if($type === 0) {
            $query = $dbh->prepare(
                "
                    SELECT 
                        users.*,
                        IFNULL(r.user_id, -1) AS user_recorder,
                        IFNULL(d.user_id, -1) AS user_doctor,
                        IFNULL(p.user_id, -1) AS user_patient
                    FROM users 
                        LEFT JOIN patients p on users.id = p.user_id 
                        LEFT JOIN doctors d on users.id = d.user_id 
                        LEFT JOIN recorders r on users.id = r.user_id 
                    WHERE email = :email LIMIT 1
                    "
            );
            $query->execute([
                ":email" => $login
            ]);
        }else{
            $query = $dbh->prepare(
                "
                    SELECT 
                        users.*,
                        IFNULL(r.user_id, -1) AS user_recorder,
                        IFNULL(d.user_id, -1) AS user_doctor,
                        IFNULL(p.user_id, -1) AS user_patient
                    FROM users 
                        LEFT JOIN patients p on users.id = p.user_id 
                        LEFT JOIN doctors d on users.id = d.user_id 
                        LEFT JOIN recorders r on users.id = r.user_id 
                    WHERE mobilephone = :mobilePhone LIMIT 1
                    "
            );
            $query->execute([
                ":mobilePhone" => $login
            ]);
        }

        if($query->rowCount() > 0){
            $row = $query->fetch();

            if(password_verify($pass, $row['pass'])) {
                $this->setId($row['id']);
                $this->setFirstName($row['first_name']);
                $this->setLastName($row['last_name']);
                $this->setPatronymic($row['patronymic']);
                $this->setBirthDate($row['birthdate']);
                $this->setGender($row['gender']);
                $this->setMobilePhone($row['mobilephone']);
                $this->setEmail($row['email']);
                $this->setHomeAddress($row['home_address']);
                $this->setActivated($row['activated'] == 1);
                $this->setUserToken($row['token']);
                $this->setFcmRegToken($row['fcm_reg_token']);
                $this->setTelegramChatId($row['telegram_chat_id']);

                if($row['user_recorder'] != -1){
                    $this->setUserLevel(self::USER_RECORDER);
                }elseif($row['user_doctor'] != -1){
                    $this->setUserLevel(self::USER_DOCTOR);
                }elseif ($row['user_patient'] != -1){
                    $this->setUserLevel(self::USER_PATIENT);
                }else{
                    $this->setUserLevel(self::USER_UNSPECIFIED);
                }

                if(!$api) {
                    $_SESSION['USER'] = $this;
                }

                return $this;
            }else{
                return json_encode([
                    "status"=>2,
                    "message"=>StatusCodes::STATUS[2]
                ]);
            }
        }else{
            return json_encode([
                "status"=>1,
                "message"=>StatusCodes::STATUS[1]
            ]);
        }
    }

    /**
     * Update user's Firebase Cloud Messaging registration token
     *
     * @param int $userId User's ID
     * @param string $userToken User's token
     * @param string $fcmRegToken New FCM registration token
     *
     * @return bool
     */
    public function updateFCMRegistrationToken($userId, $userToken, $fcmRegToken) {
        if($userId == null || $userToken == null || $fcmRegToken == null) return false;

        $db = new Database();
        $dbh = $db->getDatabase();

        $updateQuery = $dbh->prepare("UPDATE users SET fcm_reg_token = :fcmRegToken WHERE id = :id AND token = :token");

        return $updateQuery->execute([
            ':id' => $userId,
            ':token' => $userToken,
            ':fcmRegToken' => $fcmRegToken,
        ]);
    }

    /**
     * Get user from the database
     *
     * @param int $userId User's ID
     * @param string $userToken User's token
     *
     * @return User
     */
    public function getUser($userId, $userToken) {
        if($userId == null || $userToken == null) return null;

        $db = new Database();
        $dbh = $db->getDatabase();

        $query = $dbh->prepare(
            "
                    SELECT 
                        users.*,
                        IFNULL(r.user_id, -1) AS user_recorder,
                        IFNULL(d.user_id, -1) AS user_doctor,
                        IFNULL(p.user_id, -1) AS user_patient
                    FROM users 
                        LEFT JOIN patients p on users.id = p.user_id 
                        LEFT JOIN doctors d on users.id = d.user_id 
                        LEFT JOIN recorders r on users.id = r.user_id 
                    WHERE users.id = :id AND users.token = :uToken LIMIT 1
                    "
        );
        $query->execute([
            ":id" => $userId,
            ":uToken" => $userToken
        ]);

        if($query->rowCount() > 0){
            $row = $query->fetch();

            $this->setId($row['id']);
            $this->setFirstName($row['first_name']);
            $this->setLastName($row['last_name']);
            $this->setPatronymic($row['patronymic']);
            $this->setBirthDate($row['birthdate']);
            $this->setGender($row['gender']);
            $this->setMobilePhone($row['mobilephone']);
            $this->setEmail($row['email']);
            $this->setHomeAddress($row['home_address']);
            $this->setActivated($row['activated'] == 1);
            $this->setUserToken($row['token']);
            $this->setFcmRegToken($row['fcm_reg_token']);
            $this->setTelegramChatId($row['telegram_chat_id']);

            if($row['user_recorder'] != -1){
                $this->setUserLevel(self::USER_RECORDER);
            }elseif($row['user_doctor'] != -1){
                $this->setUserLevel(self::USER_DOCTOR);
            }elseif ($row['user_patient'] != -1){
                $this->setUserLevel(self::USER_PATIENT);
            }else{
                $this->setUserLevel(self::USER_UNSPECIFIED);
            }

            return $this;
        }else{
            return null;
        }
    }

    /**
     * @return int[]
     */
    private function getDoctorIds() {
        $db = new Database();

        $query = $db->getDatabase()->prepare("SELECT id FROM doctors");
        $query->execute();

        $doctorIds = [];

        while ($row = $query->fetch()){
            $doctorIds[] = $row['id'];
        }

        return $doctorIds;
    }

    /**
     * @return User[]
     */
    public function getRecorders() {
        $db = new Database();

        $query = $db->getDatabase()->prepare("SELECT users.* FROM recorders INNER JOIN users ON recorders.user_id = users.id");
        $query->execute();

        $doctors = [];

        while ($row = $query->fetch()){
            $doctors[] = new User(
                $row['id'],
                $row['first_name'],
                $row['last_name'],
                $row['patronymic'],
                $row['birthdate'],
                $row['gender'],
                $row['mobilephone'],
                $row['email'],
                $row['home_address'],
                $row['activated'],
                self::USER_RECORDER,
                $row['fcm_reg_token'],
                null,
                $row['telegram_chat_id']
            );
        }

        return $doctors;
    }

    /**
     * @return User[]
     */
    public function getDoctors() {
        $db = new Database();

        $query = $db->getDatabase()->prepare("SELECT users.* FROM doctors INNER JOIN users ON doctors.user_id = users.id");
        $query->execute();

        $doctors = [];

        while ($row = $query->fetch()){
            $doctors[] = new User(
                $row['id'],
                $row['first_name'],
                $row['last_name'],
                $row['patronymic'],
                $row['birthdate'],
                $row['gender'],
                $row['mobilephone'],
                $row['email'],
                $row['home_address'],
                $row['activated'],
                self::USER_DOCTOR,
                $row['fcm_reg_token'],
                null,
                $row['telegram_chat_id']
            );
        }

        return $doctors;
    }

    /**
     * @param int $doctorId
     *
     * @return User[]
     */
    public function getPatients($doctorId = null) {
        $db = new Database();
        $dbh = $db->getDatabase();

        if($doctorId != null){
            $query = $dbh->prepare("SELECT users.* FROM patients INNER JOIN users ON patients.user_id = users.id WHERE doctor_id = :id");
            $query->execute([
                ":id"=>$doctorId
            ]);

            $patients = [];

            while ($row = $query->fetch()){
                $patients[] = new User(
                    $row['id'],
                    $row['first_name'],
                    $row['last_name'],
                    $row['patronymic'],
                    $row['birthdate'],
                    $row['gender'],
                    $row['mobilephone'],
                    $row['email'],
                    $row['home_address'],
                    $row['activated'],
                    self::USER_PATIENT,
                    $row['fcm_reg_token'],
                    null,
                    $row['telegram_chat_id']
                );
            }

            return $patients;
        }else{
            $query = $dbh->prepare("SELECT users.* FROM patients INNER JOIN users ON patients.user_id = users.id");
            $query->execute();

            $patients = [];

            while ($row = $query->fetch()){
                $patients[] = new User(
                    $row['id'],
                    $row['first_name'],
                    $row['last_name'],
                    $row['patronymic'],
                    $row['birthdate'],
                    $row['gender'],
                    $row['mobilephone'],
                    $row['email'],
                    $row['home_address'],
                    $row['activated'],
                    self::USER_PATIENT,
                    $row['fcm_reg_token'],
                    null,
                    $row['telegram_chat_id']
                );
            }

            return $patients;
        }
    }

    /**
     * @param int $patientId
     *
     * @return User
     */
    public function getPatientById($patientId) {
        if($patientId != null) {
            $db = new Database();
            $dbh = $db->getDatabase();

            $query = $dbh->prepare("SELECT patients.id AS internal_id, users.* FROM patients INNER JOIN users ON patients.user_id = users.id WHERE users.id = :id");
            $query->execute([
                ":id" => $patientId
            ]);

            if($query->rowCount() > 0) {
                if ($row = $query->fetch()) {
                    return new User(
                        $row['id'],
                        $row['first_name'],
                        $row['last_name'],
                        $row['patronymic'],
                        $row['birthdate'],
                        $row['gender'],
                        $row['mobilephone'],
                        $row['email'],
                        $row['home_address'],
                        $row['activated'],
                        self::USER_PATIENT,
                        $row['fcm_reg_token'],
                        $row['token'],
                        $row['telegram_chat_id']
                    );
                }
            }

            return null;
        }

        return null;
    }

    /**
     * Convert User object to array
     *
     * @param bool $hide
     *
     * @return array
     */
    public function toArray($hide = true) {
        if($hide){
            return [
                'id' => $this->getId(),
                'firstName' => $this->getFirstName(),
                'lastName' => $this->getLastName(),
                'patronymic' => $this->getPatronymic(),
                'birthDate' => $this->getBirthDate(),
                'gender' => $this->getGender(),
                'mobilePhone' => $this->getMobilePhone(),
                'email' => $this->getEmail(),
                'homeAddress' => $this->getHomeAddress(),
                'activated' => $this->isActivated(),
                'userLevel' => $this->getUserLevel(),
                'fcmRegToken' => $this->getFcmRegToken(),
                'telegramChatId' => $this->getTelegramChatId()
            ];
        }else{
            return [
                'id' => $this->getId(),
                'firstName' => $this->getFirstName(),
                'lastName' => $this->getLastName(),
                'patronymic' => $this->getPatronymic(),
                'birthDate' => $this->getBirthDate(),
                'gender' => $this->getGender(),
                'mobilePhone' => $this->getMobilePhone(),
                'email' => $this->getEmail(),
                'homeAddress' => $this->getHomeAddress(),
                'userToken' => $this->getUserToken(),
                'activated' => $this->isActivated(),
                'userLevel' => $this->getUserLevel(),
                'fcmRegToken' => $this->getFcmRegToken(),
                'telegramChatId' => $this->getTelegramChatId()
            ];
        }
    }

    /**
     * Check if doctor has more patients (for table)
     *
     * @param int $page
     * @param bool $searchVal
     * @param string $from
     * @param string $to
     *
     * @return bool
     */
    public function doctorHasMorePatients($page, $searchVal = null, $from = null, $to = null) {
        if($this->isUserLoggedIn()) {
            if($this->getUserLevel() == self::USER_DOCTOR) {
                $id = $this->getUserInternalId($this->getId())['id'];
                if($id == null){
                    return null;
                }

                if($page < 1) {
                    return null;
                }

                $page++;

                $limit = 5;
                if($page == 1){
                    $start = 0;
                }else{
                    $start = ($page * $limit) - $limit;
                }

                $db = new Database();
                $dbh = $db->getDatabase();

                $filterStr = "";
                if($from != null && $to != null){
                    $filterStr = "AND v1.visit_date >= :date1 AND v1.visit_date <= :date2";
                }

                if($searchVal == null) {
                    $query = $dbh->prepare(
                        "
                        SELECT p.id FROM patients p
                        LEFT JOIN visits v1 ON p.id = v1.patient_id AND v1.visit_date = (
                          SELECT v.visit_date FROM visits v WHERE v.patient_id = p.id AND v.visit_date < CURDATE() AND v.visited = 1 ORDER BY v.visit_date DESC LIMIT 1
                        )
                        LEFT JOIN visits v2 ON p.id = v2.patient_id AND v2.visit_date = (
                          SELECT v.visit_date FROM visits v WHERE v.patient_id = p.id AND v.visit_date >= CURDATE() AND v.visited = 0 ORDER BY v.visit_date ASC LIMIT 1
                        )
                        WHERE p.doctor_id = :id {$filterStr}
                        LIMIT :start, :end
                    "
                    );

                    if($filterStr != null && strlen($filterStr) > 0) {
                        $phpDate1 = strtotime($from);
                        $phpDate2 = strtotime($to);
                        $date1 = date( 'Y-m-d H:i:s', $phpDate1 );
                        $date2 = date( 'Y-m-d H:i:s', $phpDate2 );

                        $query->execute([
                            ":id" => $id,
                            ":date1" => $date1,
                            ":date2" => $date2,
                            ":start" => $start,
                            ":end" => $limit
                        ]);
                    }else{
                        $query->execute([
                            ":id" => $id,
                            ":start" => $start,
                            ":end" => $limit
                        ]);
                    }
                }else{
                    $query = $db->getDatabase()->prepare(
                        "
                        SELECT u.id, u.first_name, u.last_name, u.patronymic, v1.visit_date, v2.visit_date FROM patients p
                        INNER JOIN users u on p.user_id = u.id
                        LEFT JOIN visits v1 ON p.id = v1.patient_id AND v1.visit_date = (
                          SELECT v.visit_date FROM visits v WHERE v.patient_id = p.id AND v.visit_date < CURDATE() AND v.visited = 1 ORDER BY v.visit_date DESC LIMIT 1
                        )
                        LEFT JOIN visits v2 ON p.id = v2.patient_id AND v2.visit_date = (
                          SELECT v.visit_date FROM visits v WHERE v.patient_id = p.id AND v.visit_date >= CURDATE() AND v.visited = 0 ORDER BY v.visit_date ASC LIMIT 1
                        )
                        WHERE 
                              p.doctor_id = :id AND 
                              (
                                u.id = :sval1 
                                 OR CONCAT(u.last_name, ' ', u.first_name, ' ', u.patronymic) LIKE :sval2
                                 OR v1.visit_date LIKE :sval3
                                 OR v2.visit_date LIKE :sval4
                              )
                              {$filterStr}
                        LIMIT :start, :end
                    "
                    );

                    $searchVal = '%'.$searchVal.'%';

                    if($filterStr != null && strlen($filterStr) > 0) {
                        $phpDate1 = strtotime($from);
                        $phpDate2 = strtotime($to);
                        $date1 = date( 'Y-m-d H:i:s', $phpDate1 );
                        $date2 = date( 'Y-m-d H:i:s', $phpDate2 );

                        $query->execute([
                            ":id" => $id,
                            ":sval1" => $searchVal,
                            ":sval2" => $searchVal,
                            ":sval3" => $searchVal,
                            ":sval4" => $searchVal,
                            ":date1" => $date1,
                            ":date2" => $date2,
                            ":start" => $start,
                            ":end" => $limit
                        ]);
                    }else{
                        $query->execute([
                            ":id" => $id,
                            ":sval1" => $searchVal,
                            ":sval2" => $searchVal,
                            ":sval3" => $searchVal,
                            ":sval4" => $searchVal,
                            ":start" => $start,
                            ":end" => $limit
                        ]);
                    }
                }

                return $query->rowCount() > 0;
            }
        }

        return null;
    }

    /**
     * Get all patients for logged in doctor (with search and filter)
     *
     * @param int $page
     * @param bool $arrayVariant
     * @param bool $formattedDate
     * @param string $searchVal
     * @param string $from
     * @param string $to
     *
     * @return array
     */
    public function getDoctorPatients($page = 1, $arrayVariant = false, $formattedDate = false, $searchVal = null, $from = null, $to = null) {
        if($page < 1) {
            return null;
        }
        if($this->isUserLoggedIn()) {
            if($this->getUserLevel() == self::USER_DOCTOR) {
                $id = $this->getUserInternalId($this->getId())['id'];
                if($id == null){
                    return null;
                }

                $db = new Database();

                $limit = 5;
                if($page == 1){
                    $start = 0;
                }else{
                    $start = ($page * $limit) - $limit;
                }

                $filterStr = "";
                if($from != null && $to != null){
                    $filterStr = "AND v1.visit_date >= :date1 AND v1.visit_date <= :date2";
                }

                if($searchVal == null) {
                    $query = $db->getDatabase()->prepare(
                        "
                        SELECT u.*, p.id AS internal_id, v1.visit_date AS latest_date, v2.visit_date AS upcoming_date FROM patients p
                        INNER JOIN users u ON p.user_id = u.id
                        LEFT JOIN visits v1 ON p.id = v1.patient_id AND v1.visit_date = (
                          SELECT v.visit_date FROM visits v WHERE v.patient_id = p.id AND v.visit_date < CURDATE() AND v.visited = 1 ORDER BY v.visit_date DESC LIMIT 1
                        )
                        LEFT JOIN visits v2 ON p.id = v2.patient_id AND v2.visit_date = (
                          SELECT v.visit_date FROM visits v WHERE v.patient_id = p.id AND v.visit_date >= CURDATE() AND v.visited = 0 ORDER BY v.visit_date ASC LIMIT 1
                        )
                        WHERE 
                              p.doctor_id = :id
                              {$filterStr}
                        ORDER BY upcoming_date DESC
                        LIMIT :start, :limit
                    "
                    );

                    if($filterStr != null && strlen($filterStr) > 0) {
                        $phpDate1 = strtotime($from);
                        $phpDate2 = strtotime($to);
                        $date1 = date( 'Y-m-d H:i:s', $phpDate1 );
                        $date2 = date( 'Y-m-d H:i:s', $phpDate2 );

                        $query->execute([
                            ":id" => $id,
                            ":date1" => $date1,
                            ":date2" => $date2,
                            ":start" => $start,
                            ":limit" => $limit
                        ]);
                    }else{
                        $query->execute([
                            ":id" => $id,
                            ":start" => $start,
                            ":limit" => $limit
                        ]);
                    }
                }else{
                    $query = $db->getDatabase()->prepare(
                        "
                        SELECT u.*, p.id AS internal_id, v1.visit_date AS latest_date, v2.visit_date AS upcoming_date FROM patients p
                        INNER JOIN users u ON p.user_id = u.id
                        LEFT JOIN visits v1 ON p.id = v1.patient_id AND v1.visit_date = (
                          SELECT v.visit_date FROM visits v WHERE v.patient_id = p.id AND v.visit_date < CURDATE() AND v.visited = 1 ORDER BY v.visit_date DESC LIMIT 1
                        )
                        LEFT JOIN visits v2 ON p.id = v2.patient_id AND v2.visit_date = (
                          SELECT v.visit_date FROM visits v WHERE v.patient_id = p.id AND v.visit_date >= CURDATE() AND v.visited = 0 ORDER BY v.visit_date ASC LIMIT 1
                        )
                        WHERE 
                              p.doctor_id = :id AND 
                              (
                                u.id = :sval1 
                                 OR CONCAT(u.last_name, ' ', u.first_name, ' ', u.patronymic) LIKE :sval2
                                 OR v1.visit_date LIKE :sval3
                                 OR v2.visit_date LIKE :sval4
                              )
                              {$filterStr}
                        ORDER BY upcoming_date DESC
                        LIMIT :start, :limit
                      "
                    );

                    $searchVal = '%'.$searchVal.'%';

                    if($filterStr != null && strlen($filterStr) > 0) {
                        $phpDate1 = strtotime($from);
                        $phpDate2 = strtotime($to);
                        $date1 = date( 'Y-m-d H:i:s', $phpDate1 );
                        $date2 = date( 'Y-m-d H:i:s', $phpDate2 );

                        $query->execute([
                            ":id" => $id,
                            ":sval1" => $searchVal,
                            ":sval2" => $searchVal,
                            ":sval3" => $searchVal,
                            ":sval4" => $searchVal,
                            ":date1" => $date1,
                            ":date2" => $date2,
                            ":start" => $start,
                            ":limit" => $limit
                        ]);
                    }else{
                        $query->execute([
                            ":id" => $id,
                            ":sval1" => $searchVal,
                            ":sval2" => $searchVal,
                            ":sval3" => $searchVal,
                            ":sval4" => $searchVal,
                            ":start" => $start,
                            ":limit" => $limit
                        ]);
                    }
                }

                $patients = [];
                while ($row = $query->fetch()) {
                    $user = new User(
                        $row['id'],
                        $row['first_name'],
                        $row['last_name'],
                        $row['patronymic'],
                        $row['birthdate'],
                        $row['gender'],
                        $row['mobilephone'],
                        $row['email'],
                        $row['home_address'],
                        $row['activated'],
                        self::USER_PATIENT,
                        $row['fcm_reg_token'],
                        $row['token'],
                        $row['telegram_chat_id']
                    );

                    $latestDate = $row['latest_date'];
                    $upcomingDate = $row['upcoming_date'];

                    if($formattedDate) {
                        if(empty($row['latest_date'])){
                            $latestDate = null;
                        }else{
                            $dateTime1 = strtotime($row['latest_date']);
                            $latestDate = date('d.m.Y', $dateTime1);
                        }
                        if(empty($row['upcoming_date'])){
                            $upcomingDate = null;
                        }else{
                            $dateTime2 = strtotime($row['upcoming_date']);
                            $upcomingDate = date('d.m.Y', $dateTime2);
                        }
                    }

                    $patients[] = [
                        'user' => $arrayVariant ? $user->toArray() : $user,
                        'internalId'=>$row['internal_id'],
                        'latestVisit' => $latestDate,
                        'upcomingVisit' => $upcomingDate
                    ];
                }

                usort($patients, function ($a, $b) {
                    $a = $a['upcomingVisit'];
                    $b = $b['upcomingVisit'];

                    if($a && $b) return 0;

                    return strnatcmp($a, $b);
                });

                return array_reverse($patients);
            }
        }

        return null;
    }

    /**
     * Check E-Mail existence in database
     *
     * @param string $email
     *
     * @return bool
     */
    private function checkEmail($email) {
        $db = new Database();

        $query = $db->getDatabase()->prepare("SELECT email FROM users WHERE email = :email LIMIT 1");
        $query->execute([
            ':email'=>$email
        ]);

        return $query->rowCount() > 0;
    }

    /**
     * Check mobile phone existence in database
     *
     * @param string $mobilePhone
     *
     * @return bool
     */
    private function checkMobilePhone($mobilePhone) {
        $db = new Database();

        $query = $db->getDatabase()->prepare("SELECT mobilephone FROM users WHERE mobilephone = :mobilePhone LIMIT 1");
        $query->execute([
            ':mobilePhone'=>$mobilePhone
        ]);

        return $query->rowCount() > 0;
    }

    /**
     * Check if a doctor has access to the patient
     *
     * @param int $patientUID
     *
     * @return bool
     */
    public function checkAccessToPatient($patientUID)
    {
        if ($this->isUserLoggedIn()) {
            if ($this->getUserLevel() == self::USER_DOCTOR) {
                $db = new Database();

                $query = $db->getDatabase()->prepare(
                "
                    SELECT patients.id FROM patients
                    INNER JOIN doctors d on d.user_id = :doctorId
                    INNER JOIN users u ON patients.user_id = :patientId AND patients.doctor_id = d.id
                    LIMIT 1
                "
                );
                $query->execute([
                    ':doctorId' => $this->getId(),
                    ':patientId' => $patientUID
                ]);

                return $query->rowCount() > 0;
            }
        }
        return false;
    }

    /**
     * Check if a patient has access to the diagnosis
     *
     * @param int $patientUID
     * @param int $diagnosisId
     *
     * @return bool
     */
    public function checkPatientAccessToDiagnosis($patientUID, $diagnosisId)
    {
        if ($this->isUserLoggedIn()) {
            if ($this->getUserLevel() == self::USER_DOCTOR) {
                if($patientUID == null || $diagnosisId == null) {
                    return false;
                }

                $db = new Database();

                $query = $db->getDatabase()->prepare(
                    "
                    SELECT d.id FROM patients
                    INNER JOIN users u ON patients.user_id = :patientId
                    INNER JOIN diagnoses d on patients.id = d.patient_id
                    WHERE d.id = :diagnosisId AND d.active = 1
                    LIMIT 1
                "
                );
                $query->execute([
                    ':patientId' => $patientUID,
                    ':diagnosisId' => $diagnosisId
                ]);

                return $query->rowCount() > 0;
            }
        }
        return false;
    }

    /**
     * Check if a patient has access to the recipe
     *
     * @param int $patientUID
     * @param int $recipeId
     *
     * @return bool
     */
    public function checkPatientAccessToRecipe($patientUID, $recipeId)
    {
        if ($this->isUserLoggedIn()) {
            if ($this->getUserLevel() == self::USER_DOCTOR) {
                if($patientUID == null || $recipeId == null) {
                    return false;
                }

                $db = new Database();

                $query = $db->getDatabase()->prepare(
                    "
                    SELECT m.id FROM users
                    INNER JOIN patients p on users.id = p.user_id
                    INNER JOIN diagnoses d on p.id = d.patient_id
                    INNER JOIN medication m on d.id = m.diagnosis_id
                    WHERE users.id = :patientId AND m.id = :recipeId
                    LIMIT 1
                "
                );
                $query->execute([
                    ':patientId' => $patientUID,
                    ':recipeId' => $recipeId
                ]);

                return $query->rowCount() > 0;
            }
        }
        return false;
    }

    /**
     * Check if a doctor has access to the recipe
     *
     * @param int $recipeId
     *
     * @return bool
     */
    public function checkAccessToRecipe($recipeId)
    {
        if ($this->isUserLoggedIn()) {
            if ($this->getUserLevel() == self::USER_DOCTOR) {
                if($recipeId == null) {
                    return false;
                }

                $db = new Database();

                $query = $db->getDatabase()->prepare(
                    "
                    SELECT m.id FROM users
                    INNER JOIN doctors dc on users.id = dc.user_id
                    INNER JOIN diagnoses d on dc.id = d.doctor_id
                    INNER JOIN medication m on d.id = m.diagnosis_id
                    WHERE users.id = :doctorId AND m.id = :recipeId
                    LIMIT 1
                "
                );
                $query->execute([
                    ':doctorId' => $this->getId(),
                    ':recipeId' => $recipeId
                ]);

                return $query->rowCount() > 0;
            }
        }
        return false;
    }

    /**
     * Check if a doctor has access to the diagnosis
     *
     * @param int $diagnosisId
     *
     * @return bool
     */
    public function checkAccessToDiagnosis($diagnosisId)
    {
        if ($this->isUserLoggedIn()) {
            if ($this->getUserLevel() == self::USER_DOCTOR) {
                if($diagnosisId == null) {
                    return false;
                }

                $db = new Database();

                $query = $db->getDatabase()->prepare(
                    "
                    SELECT d2.id FROM users
                    INNER JOIN doctors d on users.id = d.user_id
                    INNER JOIN diagnoses d2 on d.id = d2.doctor_id
                    WHERE d2.id = :diagnosisId AND d.user_id = :id AND d2.active = 1
                    LIMIT 1
                "
                );
                $query->execute([
                    ':diagnosisId' => $diagnosisId,
                    ':id' => $this->getId()
                ]);

                return $query->rowCount() > 0;
            }
        }
        return false;
    }

    /**
     * Register patient in the system
     *
     * @param string $email
     * @param string $pass
     * @param string $firstName
     * @param string $lastName
     * @param string $patronymic
     * @param string $birthDate
     * @param int $gender
     * @param string $mobilePhone
     * @param string $homeAddress
     * @param int $doctorId
     * @param bool $activated
     *
     * @return mixed
     */
    public function registerPatient($email, $pass, $firstName, $lastName, $patronymic, $birthDate, $gender, $mobilePhone, $homeAddress, $doctorId, $activated = false) {
        if($email == null || !filter_var($email, FILTER_VALIDATE_EMAIL)){
            return json_encode([
                "status"=>3,
                "message"=>StatusCodes::STATUS[3]
            ]);
        }
        if($pass == null || ($pass != null && strlen($pass) < 6)){
            return json_encode([
                "status"=>15,
                "message"=>StatusCodes::STATUS[15]
            ]);
        }
        if($firstName == null || ($firstName != null && empty($firstName))){
            return json_encode([
                "status"=>4,
                "message"=>StatusCodes::STATUS[4]
            ]);
        }
        if($lastName == null || ($lastName != null && empty($lastName))){
            return json_encode([
                "status"=>5,
                "message"=>StatusCodes::STATUS[5]
            ]);
        }
        if($patronymic == null || ($patronymic != null && empty($patronymic))){
            return json_encode([
                "status"=>6,
                "message"=>StatusCodes::STATUS[6]
            ]);
        }
        if($birthDate == null || ($birthDate != null && empty($birthDate))){
            return json_encode([
                "status" => 6,
                "message" => StatusCodes::STATUS[7]
            ]);
        }else{
            $date = explode("-", $birthDate);
            if(!checkdate($date[1], $date[2], $date[0])) {
                return json_encode([
                    "status" => 7,
                    "message" => StatusCodes::STATUS[7]
                ]);
            }
        }
        if($gender == null || ($gender != null && $gender < 1 || $gender > 2)){
            return json_encode([
                "status"=>29,
                "message"=>StatusCodes::STATUS[29]
            ]);
        }
        if($mobilePhone == null || ($mobilePhone != null && !preg_match('/^[0-9]{10}$|^[0-9]{12}$/', $mobilePhone))){
            return json_encode([
                "status"=>8,
                "message"=>StatusCodes::STATUS[8]
            ]);
        }
        if($homeAddress == null || ($homeAddress != null && empty($homeAddress))){
            return json_encode([
                "status"=>9,
                "message"=>StatusCodes::STATUS[9]
            ]);
        }
        if($doctorId == null || ($doctorId != null && $doctorId <= 0)){
            return json_encode([
                "status"=>10,
                "message"=>StatusCodes::STATUS[10]
            ]);
        }

        if($this->checkEmail($email)){
            return json_encode([
                "status"=>13,
                "message"=>StatusCodes::STATUS[13]
            ]);
        }

        if(preg_match('/^[0-9]{10}$/', $mobilePhone)){
            $mobilePhone = '38'.$mobilePhone;
        }

        if($this->checkMobilePhone($mobilePhone)){
            return json_encode([
                "status"=>14,
                "message"=>StatusCodes::STATUS[14]
            ]);
        }

        $db = new Database();
        $dbh = $db->getDatabase();

        $query1 = $dbh->prepare(
        "
              INSERT INTO 
                    users (email, pass, first_name, last_name, patronymic, birthdate, gender, mobilephone, home_address, activated, token) 
              VALUES 
                    (:email, :pass, :firstName, :lastName, :patronymic, :birthDate, :gender, :mobilePhone, :homeAddress, :activated, :token)
        ");
        $query2 = $dbh->prepare(
            "
              INSERT INTO 
                    patients (user_id, doctor_id) 
              VALUES 
                    (:id, :doctorId)
        ");

        $hashedPass = password_hash($pass, PASSWORD_DEFAULT);
        $doctorIds = $this->getDoctorIds();

        if(in_array($doctorId, $doctorIds)) {
            try {
                $secureKey = bin2hex(openssl_random_pseudo_bytes(256));
                $userToken = hash_hmac('sha3-256', $mobilePhone, $secureKey);

                $dbh->beginTransaction();

                $query1->execute([
                    ":email" => $email,
                    ":pass" => $hashedPass,
                    ":firstName" => $firstName,
                    ":lastName" => $lastName,
                    ":patronymic" => $patronymic,
                    ":birthDate" => $birthDate,
                    ":gender" => $gender,
                    ":mobilePhone" => $mobilePhone,
                    ":homeAddress" => $homeAddress,
                    ":activated" => $activated,
                    ":token" => $userToken
                ]);

                $insertId = $dbh->lastInsertId();

                $query2->execute([
                    ":id"=>$insertId,
                    ":doctorId"=>$doctorId
                ]);

                $dbh->commit();

                return json_encode([
                    "status" => 11,
                    "message" => StatusCodes::STATUS[11]
                ]);
            } catch (\PDOException $e) {
                $dbh->rollback();

                return json_encode([
                    "status" => 0,
                    "message" => StatusCodes::STATUS[0],
                    'data' => [
                        'errorCode' => $e->getCode(),
                        'message' => $e->getMessage()
                    ]
                ]);
            }
        }else{
            return json_encode([
                "status" => 10,
                "message" => StatusCodes::STATUS[10]
            ]);
        }
    }

    /**
     * Register doctor in the system
     *
     * @param string $email
     * @param string $pass
     * @param string $firstName
     * @param string $lastName
     * @param string $patronymic
     * @param string $birthDate
     * @param int $gender
     * @param string $mobilePhone
     * @param string $homeAddress
     * @param string $specialty
     * @param bool $activated
     *
     * @return mixed
     */
    public function registerDoctor($email, $pass, $firstName, $lastName, $patronymic, $birthDate, $gender, $mobilePhone, $homeAddress, $specialty, $activated = false) {
        if($email == null || !filter_var($email, FILTER_VALIDATE_EMAIL)){
            return json_encode([
                "status"=>3,
                "message"=>StatusCodes::STATUS[3]
            ]);
        }
        if($pass == null || ($pass != null && strlen($pass) < 6)){
            return json_encode([
                "status"=>15,
                "message"=>StatusCodes::STATUS[15]
            ]);
        }
        if($firstName == null || ($firstName != null && empty($firstName))){
            return json_encode([
                "status"=>4,
                "message"=>StatusCodes::STATUS[4]
            ]);
        }
        if($lastName == null || ($lastName != null && empty($lastName))){
            return json_encode([
                "status"=>5,
                "message"=>StatusCodes::STATUS[5]
            ]);
        }
        if($patronymic == null || ($patronymic != null && empty($patronymic))){
            return json_encode([
                "status"=>6,
                "message"=>StatusCodes::STATUS[6]
            ]);
        }
        if($birthDate == null || ($birthDate != null && empty($birthDate))){
            return json_encode([
                "status" => 6,
                "message" => StatusCodes::STATUS[7]
            ]);
        }else{
            $date = explode("-", $birthDate);
            if(!checkdate($date[1], $date[2], $date[0])) {
                return json_encode([
                    "status" => 7,
                    "message" => StatusCodes::STATUS[7]
                ]);
            }
        }
        if($gender == null || ($gender != null && $gender < 1 || $gender > 2)){
            return json_encode([
                "status"=>29,
                "message"=>StatusCodes::STATUS[29]
            ]);
        }
        if($mobilePhone == null || ($mobilePhone != null && !preg_match('/^[0-9]{10}$|^[0-9]{12}$/', $mobilePhone))){
            return json_encode([
                "status"=>8,
                "message"=>StatusCodes::STATUS[8]
            ]);
        }
        if($homeAddress == null || ($homeAddress != null && empty($homeAddress))){
            return json_encode([
                "status"=>9,
                "message"=>StatusCodes::STATUS[9]
            ]);
        }

        if($specialty == null || ($specialty != null && empty($specialty))){
            return json_encode([
                "status"=>16,
                "message"=>StatusCodes::STATUS[16]
            ]);
        }

        if($this->checkEmail($email)){
            return json_encode([
                "status"=>13,
                "message"=>StatusCodes::STATUS[13]
            ]);
        }

        if(preg_match('/^[0-9]{10}$/', $mobilePhone)){
            $mobilePhone = '38'.$mobilePhone;
        }

        if($this->checkMobilePhone($mobilePhone)){
            return json_encode([
                "status"=>14,
                "message"=>StatusCodes::STATUS[14]
            ]);
        }

        $db = new Database();
        $dbh = $db->getDatabase();

        $query1 = $dbh->prepare(
            "
              INSERT INTO 
                    users (email, pass, first_name, last_name, patronymic, birthdate, gender, mobilephone, home_address, activated, token) 
              VALUES 
                    (:email, :pass, :firstName, :lastName, :patronymic, :birthDate, :gender, :mobilePhone, :homeAddress, :activated, :token)
        ");
        $query2 = $dbh->prepare(
            "
              INSERT INTO 
                    doctors (user_id, specialty) 
              VALUES 
                    (:id, :specialty)
        ");

        $hashedPass = password_hash($pass, PASSWORD_DEFAULT);

        try {
            $secureKey = bin2hex(openssl_random_pseudo_bytes(256));
            $userToken = hash_hmac('sha3-256', $mobilePhone, $secureKey);

            $dbh->beginTransaction();

            $query1->execute([
                ":email" => $email,
                ":pass" => $hashedPass,
                ":firstName" => $firstName,
                ":lastName" => $lastName,
                ":patronymic" => $patronymic,
                ":birthDate" => $birthDate,
                ":gender" => $gender,
                ":mobilePhone" => $mobilePhone,
                ":homeAddress" => $homeAddress,
                ":activated" => $activated,
                ":token" => $userToken
            ]);

            $insertId = $dbh->lastInsertId();

            $query2->execute([
                ":id"=>$insertId,
                ":specialty"=>$specialty
            ]);

            $dbh->commit();

            return json_encode([
                "status" => 12,
                "message" => StatusCodes::STATUS[12]
            ]);
        } catch (\PDOException $e) {
            $dbh->rollback();

            return json_encode([
                "status" => 0,
                "message" => StatusCodes::STATUS[0],
                'data' => [
                    'errorCode' => $e->getCode(),
                    'message' => $e->getMessage()
                ]
            ]);
        }
    }

    /**
     * Register recorder in the system
     *
     * @param string $email
     * @param string $pass
     * @param string $firstName
     * @param string $lastName
     * @param string $patronymic
     * @param string $birthDate
     * @param int $gender
     * @param string $mobilePhone
     * @param string $homeAddress
     * @param bool $activated
     *
     * @return mixed
     */
    public function registerRecorder($email, $pass, $firstName, $lastName, $patronymic, $birthDate, $gender, $mobilePhone, $homeAddress, $activated = false) {
        if($email == null || !filter_var($email, FILTER_VALIDATE_EMAIL)){
            return json_encode([
                "status"=>3,
                "message"=>StatusCodes::STATUS[3]
            ]);
        }
        if($pass == null || ($pass != null && strlen($pass) < 6)){
            return json_encode([
                "status"=>15,
                "message"=>StatusCodes::STATUS[15]
            ]);
        }
        if($firstName == null || ($firstName != null && empty($firstName))){
            return json_encode([
                "status"=>4,
                "message"=>StatusCodes::STATUS[4]
            ]);
        }
        if($lastName == null || ($lastName != null && empty($lastName))){
            return json_encode([
                "status"=>5,
                "message"=>StatusCodes::STATUS[5]
            ]);
        }
        if($patronymic == null || ($patronymic != null && empty($patronymic))){
            return json_encode([
                "status"=>6,
                "message"=>StatusCodes::STATUS[6]
            ]);
        }
        if($birthDate == null || ($birthDate != null && empty($birthDate))){
            return json_encode([
                "status" => 6,
                "message" => StatusCodes::STATUS[7]
            ]);
        }else{
            $date = explode("-", $birthDate);
            if(!checkdate($date[1], $date[2], $date[0])) {
                return json_encode([
                    "status" => 7,
                    "message" => StatusCodes::STATUS[7]
                ]);
            }
        }
        if($gender == null || ($gender != null && $gender < 1 || $gender > 2)){
            return json_encode([
                "status"=>29,
                "message"=>StatusCodes::STATUS[29]
            ]);
        }
        if($mobilePhone == null || ($mobilePhone != null && !preg_match('^[0-9]{10}$|^[0-9]{12}$', $mobilePhone))){
            return json_encode([
                "status"=>8,
                "message"=>StatusCodes::STATUS[8]
            ]);
        }
        if($homeAddress == null || ($homeAddress != null && empty($homeAddress))){
            return json_encode([
                "status"=>9,
                "message"=>StatusCodes::STATUS[9]
            ]);
        }

        if($this->checkEmail($email)){
            return json_encode([
                "status"=>13,
                "message"=>StatusCodes::STATUS[13]
            ]);
        }

        if(preg_match('^[0-9]{10}$', $mobilePhone)){
            $mobilePhone = '38'.$mobilePhone;
        }

        if($this->checkMobilePhone($mobilePhone)){
            return json_encode([
                "status"=>14,
                "message"=>StatusCodes::STATUS[14]
            ]);
        }

        $db = new Database();
        $dbh = $db->getDatabase();

        $query1 = $dbh->prepare(
            "
              INSERT INTO 
                    users (email, pass, first_name, last_name, patronymic, birthdate, gender, mobilephone, home_address, activated, token) 
              VALUES 
                    (:email, :pass, :firstName, :lastName, :patronymic, :birthDate, :gender, :mobilePhone, :homeAddress, :activated, :token)
        ");
        $query2 = $dbh->prepare(
            "
              INSERT INTO 
                    recorders (user_id) 
              VALUES 
                    (:id)
        ");

        $hashedPass = password_hash($pass, PASSWORD_DEFAULT);

        try {
            $secureKey = bin2hex(openssl_random_pseudo_bytes(256));
            $userToken = hash_hmac('sha3-256', $mobilePhone, $secureKey);

            $dbh->beginTransaction();

            $query1->execute([
                ":email" => $email,
                ":pass" => $hashedPass,
                ":firstName" => $firstName,
                ":lastName" => $lastName,
                ":patronymic" => $patronymic,
                ":birthDate" => $birthDate,
                ":gender" => $gender,
                ":mobilePhone" => $mobilePhone,
                ":homeAddress" => $homeAddress,
                ":activated" => $activated,
                ":token" => $userToken
            ]);

            $insertId = $dbh->lastInsertId();

            $query2->execute([
                ":id"=>$insertId
            ]);

            $dbh->commit();

            return json_encode([
                "status" => 12,
                "message" => StatusCodes::STATUS[12]
            ]);
        } catch (\PDOException $e) {
            $dbh->rollback();

            return json_encode([
                "status" => 0,
                "message" => StatusCodes::STATUS[0],
                'data' => [
                    'errorCode' => $e->getCode(),
                    'message' => $e->getMessage()
                ]
            ]);
        }
    }

    public function logout() {
        if($this->isUserLoggedIn()){
            $_SESSION['USER'] = null;
            unset($_SESSION['USER']);
        }
    }

    /**
     * Checks user token
     *
     * @param int $userId
     * @param string $userToken
     *
     * @return bool
     */
    private function checkToken($userId, $userToken) {
        $db = new Database();
        $query = $db->getDatabase()->prepare("SELECT id, token FROM users WHERE id=:id AND token=:token LIMIT 1");
        $query->execute([
            ':id'=>$userId,
            ':token'=>$userToken
        ]);

        return $query->rowCount() > 0;
    }

    /**
     * Get user's internal id
     *
     * @param int $id
     *
     * @return array
     */
    private function getUserInternalId($id) {
        $db = new Database();

        $query = $db->getDatabase()->prepare(
            "
                SELECT
                  IFNULL(r.id, IFNULL(d.id, IFNULL(p.id, -1))) AS user_internal_id,
                  IFNULL(r.user_id, -1) AS user_recorder,
                  IFNULL(d.user_id, -1) AS user_doctor,
                  IFNULL(p.user_id, -1) AS user_patient
                FROM users
                       LEFT JOIN patients p on users.id = p.user_id
                       LEFT JOIN doctors d on users.id = d.user_id
                       LEFT JOIN recorders r on users.id = r.user_id
                WHERE users.id = :id LIMIT 1
            "
        );

        $query->execute([
            ":id"=>$id
        ]);

        if($query->rowCount() > 0){
            if($row = $query->fetch()){
                if($row['user_internal_id'] == -1){
                    return null;
                }

                if($row['user_recorder'] != -1){
                    $type = self::USER_RECORDER;
                }elseif($row['user_doctor'] != -1){
                    $type = self::USER_DOCTOR;
                }elseif($row['user_patient'] != -1){
                    $type = self::USER_PATIENT;
                }else{
                    $type = self::USER_UNSPECIFIED;
                }

                return [
                    'type' => $type,
                    'id' => $row['user_internal_id']
                ];
            }
        }

        return null;
    }

    /**
     * Get specific patient visits for doctor
     *
     * @param int $patientId
     * @param bool $convertToInternalId
     * @param bool $upcoming
     *
     * @return Visit[]
     */
    private function getVisitsById($patientId, $convertToInternalId = true, $upcoming = true) {
        if($this->isUserLoggedIn()){
            if($this->getUserLevel() == self::USER_DOCTOR && $patientId != null){
                $doctorInternalId = $this->getUserInternalId($this->getId());
                if($convertToInternalId) {
                    $patientInternalId = $this->getUserInternalId($patientId);
                }else{
                    $patientInternalId = $patientId;
                }

                if($doctorInternalId != null && $patientInternalId != null) {
                    $doctorInternalId = $doctorInternalId['id'];
                    $patientInternalId = $patientInternalId['id'];

                    $db = new Database();

                    if ($upcoming) {
                        $query = $db->getDatabase()->prepare("SELECT * FROM visits WHERE doctor_id = :id AND visit_date >= CURDATE() AND visited = 0 AND patient_id = :patientId");
                    } else {
                        $query = $db->getDatabase()->prepare("SELECT * FROM visits WHERE doctor_id = :id AND patient_id = :patientId");
                    }
                    $query->execute([
                        ':id' => $doctorInternalId,
                        ':patientId' => $patientInternalId
                    ]);

                    $visits = [];
                    while ($row = $query->fetch()) {
                        if ($row['visit_date'] != null) {
                            $visits[] = new Visit($row['id'], $row['doctor_id'], $row['patient_id'], $row['visit_date'], $row['visited'] == 1);
                        }
                    }

                    return $visits;
                }
            }
        }
        return null;
    }

    /**
     * Get symptoms that ARE NOT in the database
     *
     * @param $symptomsArr
     *
     * @return array
     */
    public function getDistinctSymptomsFromArray($symptomsArr) {
        if($this->isUserLoggedIn()) {
            if ($this->getUserLevel() == self::USER_DOCTOR) {
                if($symptomsArr != null) {
                    $db = new Database();
                    $dbh = $db->getDatabase();

                    $symptomsStr = "";
                    foreach ($symptomsArr as $symptom) {
                        $symptomsStr .= $dbh->quote($symptom) . ", ";
                    }

                    $symptomsStr = rtrim($symptomsStr,', ');

                    $query = $dbh->prepare("SELECT symptoms.name FROM symptoms WHERE symptoms.name IN ({$symptomsStr})");

                    $query->execute();

                    $dbSymptoms = [];

                    while($row = $query->fetch()) {
                        $dbSymptoms[] = $row['name'];
                    }

                    $symptoms = [];
                    foreach ($symptomsArr as $symptom) {
                        if(!in_array($symptom, $dbSymptoms)) {
                            $symptoms[] = $symptom;
                        }
                    }

                    return $symptoms;
                }
            }
        }

        return null;
    }

    /**
     * Get symptoms that ARE in the database
     *
     * @param $symptomsArr
     *
     * @return array
     */
    public function getSymptomsFromArray($symptomsArr) {
        if($this->isUserLoggedIn()) {
            if ($this->getUserLevel() == self::USER_DOCTOR) {
                if($symptomsArr != null) {
                    $db = new Database();
                    $dbh = $db->getDatabase();

                    $symptomsStr = "";
                    foreach ($symptomsArr as $symptom) {
                        $symptomsStr .= $dbh->quote($symptom) . ", ";
                    }

                    $symptomsStr = rtrim($symptomsStr,', ');

                    $query = $dbh->prepare("SELECT symptoms.* FROM symptoms WHERE symptoms.name IN ({$symptomsStr})");

                    $query->execute();

                    $dbSymptoms = [];

                    while($row = $query->fetch()) {
                        $dbSymptoms[] = [
                            'id' => $row['id'],
                            'api_id' => $row['api_id'],
                            'name' => $row['name']
                        ];
                    }

                    return $dbSymptoms;
                }
            }
        }

        return null;
    }

    /**
     * Add diagnosis to the patient
     *
     * @param int $patientId
     * @param array $diagnosisData
     *
     * @return bool
     */
    public function addDiagnosis($patientId, $diagnosisData) {
        if($this->isUserLoggedIn()) {
            if ($this->getUserLevel() == self::USER_DOCTOR) {
                if($this->checkAccessToPatient($patientId)) {
                    if($diagnosisData != null) {
                        $db = new Database();
                        $dbh = $db->getDatabase();

                        if($diagnosisData['diagnosis'] == null){
                            return false;
                        }

                        $pId = $this->getUserInternalId($patientId)['id'];
                        $uId = $this->getUserInternalId($this->getId())['id'];

                        if(!isset($pId) || (isset($pId) && $pId == null) || !isset($uId) || (isset($uId) && $uId == null)) {
                            return false;
                        }

                        $newSymptoms = null;
                        if($diagnosisData['symptoms'] != null) {
                            $newSymptoms = $this->getDistinctSymptomsFromArray($diagnosisData['symptoms']);
                            if($newSymptoms != null) {
                                $query1 = $dbh->prepare("INSERT INTO symptoms (api_id, name) VALUES (-1, :name)");
                            }
                        }
                        $query2 = $dbh->prepare("INSERT INTO diagnoses (doctor_id, patient_id, name, detection_date) VALUES (:uId, :pId, :dName, :dDate)");
                        $query3 = $dbh->prepare("INSERT INTO diagnoses_data (diagnosis_id, symptom_id) VALUES (:dId, :sid)");

                        try {
                            $dbh->beginTransaction();

                            if($diagnosisData['symptoms'] != null && $newSymptoms != null) {
                                foreach ($newSymptoms as $symptom) {
                                    $query1->execute([
                                        ':name' => $symptom
                                    ]);
                                }
                            }

                            $query2->execute([
                                ':uId' => $uId,
                                ':pId' => $pId,
                                ':dName' => $diagnosisData['diagnosis'],
                                ':dDate' => date("Y-m-d")
                            ]);

                            $lastInsertId = $dbh->lastInsertId();

                            $dbh->commit();

                            if($diagnosisData['symptoms'] != null) {
                                $dbh->beginTransaction();

                                $newSymptoms2 = $this->getSymptomsFromArray($diagnosisData['symptoms']);

                                if ($newSymptoms2 != null) {
                                    foreach ($newSymptoms2 as $symptom) {
                                        $query3->execute([
                                            ':dId' => $lastInsertId,
                                            ':sid' => $symptom['id']
                                        ]);
                                    }
                                }

                                $dbh->commit();
                            }

                            return true;
                        } catch (\PDOException $ex) {
                            $dbh->rollBack();

                            return false;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Get symptoms from diagnosis
     *
     * @param int $patientId
     * @param int $diagnosisId
     *
     * @return array
     */
    public function getSymptomsForDiagnosis($patientId, $diagnosisId) {
        if ($this->isUserLoggedIn()) {
            if ($this->getUserLevel() == self::USER_DOCTOR) {
                $db = new Database();
                $dbh = $db->getDatabase();

                $query = $dbh->prepare(
                    "
                            SELECT s.name FROM symptoms s
                            INNER JOIN diagnoses_data dd on s.id = dd.symptom_id
                            INNER JOIN diagnoses d ON dd.diagnosis_id = d.id
                            WHERE d.patient_id = :pID AND dd.diagnosis_id = :dID
                        "
                );

                $query->execute([
                    ':pID' => $patientId,
                    ':dID' => $diagnosisId
                ]);

                $symptoms = [];

                while($row = $query->fetch()) {
                    $symptoms[] = $row['name'];
                }

                return $symptoms;
            }
        }

        return null;
    }

    /**
     * Get patient's diagnoses
     *
     * @param int $patientId
     *
     * @return array
     */
    public function getDiagnoses($patientId)
    {
        if ($this->isUserLoggedIn()) {
            if ($this->getUserLevel() == self::USER_DOCTOR) {
                if ($this->checkAccessToPatient($patientId)) {
                    $patientInternalId = $this->getUserInternalId($patientId)['id'];

                    $db = new Database();
                    $dbh = $db->getDatabase();

                    $query = $dbh->prepare(
                        "
                            SELECT d.id, d.name, d.detection_date, dd.symptom_id, s.name AS symptom_name FROM diagnoses d
                            LEFT JOIN diagnoses_data dd ON d.id = dd.diagnosis_id
                            LEFT JOIN symptoms s on dd.symptom_id = s.id
                            WHERE d.patient_id = :pID AND d.active = 1
                        "
                    );

                    $query->execute([
                        ':pID' => $patientInternalId
                    ]);

                    $diagnoses = [];

                    while($row = $query->fetch()) {
                        if(array_key_exists($row['id'], $diagnoses)) {
                            if($row['symptom_id'] != null && $row['symptom_name'] != null) {
                                if($diagnoses[$row['id']]['symptoms'] != null) {
                                    array_push($diagnoses[$row['id']]['symptoms'], $row['symptom_name']);
                                } else {
                                    $diagnoses[$row['id']]['symptoms'] = [$row['symptom_name']];
                                }
                            }
                        }else{
                            $diagnoses[$row['id']]['data'] = [
                                'id' => $row['id'],
                                'name' => $row['name'],
                                'detection_date' => $row['detection_date']
                            ];
                            $diagnoses[$row['id']]['symptoms'] = null;

                            if($row['symptom_id'] != null && $row['symptom_name'] != null) {
                                if($diagnoses[$row['id']]['symptoms'] != null) {
                                    array_push($diagnoses[$row['id']]['symptoms'], $row['symptom_name']);
                                } else {
                                    $diagnoses[$row['id']]['symptoms'] = [$row['symptom_name']];
                                }
                            }
                        }
                    }

                    return $diagnoses;
                }
            }
        }

        return null;
    }

    /**
     * Get patient's diagnoses (for patient)
     *
     * @param bool $api used in API
     * @param bool $arrayVariant response with array
     *
     * @return array
     */
    public function getPatientDiagnoses($api = false, $arrayVariant = false)
    {
        if ($this->isUserLoggedIn($api)) {
            if ($this->getUserLevel() == self::USER_PATIENT) {
                $patientInternalId = $this->getUserInternalId($this->getId())['id'];

                $db = new Database();
                $dbh = $db->getDatabase();

                $query = $dbh->prepare(
                    "
                            SELECT d.id, d.name, d.detection_date, dd.symptom_id, s.name AS symptom_name FROM diagnoses d
                            LEFT JOIN diagnoses_data dd ON d.id = dd.diagnosis_id
                            LEFT JOIN symptoms s on dd.symptom_id = s.id
                            WHERE d.patient_id = :pID AND d.active = 1
                        "
                );

                $query->execute([
                    ':pID' => $patientInternalId
                ]);

                $diagnoses = [];

                while($row = $query->fetch()) {
                    if($arrayVariant) {
                        $key = ArrayUtil::searchForDiagnosis($row['id'], $diagnoses);
                        if (array_key_exists($key, $diagnoses)) {
                            if ($row['symptom_id'] != null && $row['symptom_name'] != null) {
                                array_push($diagnoses[$key]['symptoms'], $row['symptom_name']);
                            }
                        }else{
                            $diagnoses[] = [
                                'data' => [
                                    'id' => $row['id'],
                                    'name' => $row['name'],
                                    'detection_date' => $row['detection_date']
                                ],
                                'symptoms' => $row['symptom_name'] != null ? [
                                    $row['symptom_name']
                                ] : null
                            ];
                        }
                    } else {
                        if (array_key_exists($row['id'], $diagnoses)) {
                            if ($row['symptom_id'] != null && $row['symptom_name'] != null) {
                                array_push($diagnoses[$row['id']]['symptoms'], $row['symptom_name']);
                            }
                        } else {
                            $diagnoses[$row['id']] = [
                                'data' => [
                                    'id' => $row['id'],
                                    'name' => $row['name'],
                                    'detection_date' => $row['detection_date']
                                ],
                                'symptoms' => $row['symptom_name'] != null ? [
                                    $row['symptom_name']
                                ] : null
                            ];
                        }
                    }
                }

                return $diagnoses;
            }
        }

        return null;
    }

    /**
     * Deactivate diagnosis in the database
     *
     * @param $diagnosisId
     *
     * @return bool
     */
    public function deleteDiagnosis($diagnosisId) {
        if ($this->isUserLoggedIn()) {
            if ($this->getUserLevel() == self::USER_DOCTOR) {
                if(!$this->checkAccessToDiagnosis($diagnosisId)) {
                    return false;
                }

                $doctorId = $this->getUserInternalId($this->getId())['id'];

                $db = new Database();
                $dbh = $db->getDatabase();

                $updateQuery = $dbh->prepare("UPDATE diagnoses SET active = 0 WHERE id = :id AND doctor_id = :dId");
                return $updateQuery->execute([
                    ':id' => $diagnosisId,
                    ':dId' => $doctorId
                ]);
            }
        }
        return false;
    }

    /**
     * Get patient's recipes by diagnosis (for doctor)
     *
     * @param int $patientId
     * @param int $diagnosisId
     *
     * @return array
     */
    public function getRecipes($patientId, $diagnosisId) {
        if ($this->isUserLoggedIn()) {
            if ($this->getUserLevel() == self::USER_DOCTOR) {
                if($patientId == null || $diagnosisId == null) {
                    return null;
                }

                $db = new Database();

                $query = $db->getDatabase()->prepare(
                    "
                    SELECT m.* FROM users
                    INNER JOIN patients p on users.id = p.user_id
                    INNER JOIN diagnoses d on p.id = d.patient_id
                    INNER JOIN medication m on d.id = m.diagnosis_id
                    WHERE users.id = :patientId AND m.diagnosis_id = :diagnosisId AND d.active = 1 AND m.active = 1
                "
                );
                $query->execute([
                    ':patientId' => $patientId,
                    ':diagnosisId' => $diagnosisId
                ]);

                $recipe = [];

                while ($row = $query->fetch()) {
                    $recipe[] = [
                        'id' => $row['id'],
                        'rp' => $row['rp'],
                        'dtdn' => $row['dtdn'],
                        'signa' => $row['signa']
                    ];
                }

                return $recipe;
            }
        }
        return null;
    }

    /**
     * Get patient's recipes by diagnosis (for patient)
     *
     * @param int $diagnosisId
     *
     * @return array
     */
    public function getPatientRecipes($diagnosisId) {
        if ($this->isUserLoggedIn(true)) {
            if ($this->getUserLevel() == self::USER_PATIENT) {
                if($diagnosisId == null) {
                    return null;
                }

                $db = new Database();

                $query = $db->getDatabase()->prepare(
                    "
                    SELECT m.* FROM users
                    INNER JOIN patients p on users.id = p.user_id
                    INNER JOIN diagnoses d on p.id = d.patient_id
                    INNER JOIN medication m on d.id = m.diagnosis_id
                    WHERE users.id = :patientId AND m.diagnosis_id = :diagnosisId AND d.active = 1 AND m.active = 1
                "
                );
                $query->execute([
                    ':patientId' => $this->getId(),
                    ':diagnosisId' => $diagnosisId
                ]);

                $recipes = [];

                while ($row = $query->fetch()) {
                    $recipes[] = [
                        'id' => $row['id'],
                        'rp' => $row['rp'],
                        'dtdn' => $row['dtdn'],
                        'signa' => $row['signa']
                    ];
                }

                return $recipes;
            }
        }
        return null;
    }

    /**
     * Get patient's doctor
     *
     * @return int
     */
    public function getPatientDoctor() {
        if ($this->isUserLoggedIn(true)) {
            if ($this->getUserLevel() == self::USER_PATIENT) {
                $db = new Database();

                $query = $db->getDatabase()->prepare(
                    "
                    SELECT p.doctor_id FROM users
                    INNER JOIN patients p on users.id = p.user_id
                    WHERE users.id = :patientId
                    LIMIT 1
                "
                );
                $query->execute([
                    ':patientId' => $this->getId()
                ]);

                $doctorId = null;

                if($query->rowCount() > 0) {
                    if ($row = $query->fetch()) {
                        $doctorId = $row['doctor_id'];
                    }
                }

                return $doctorId;
            }
        }
        return null;
    }

    /**
     * Get patient's appointments
     *
     * @return array
     */
    public function getPatientAppointments() {
        if ($this->isUserLoggedIn(true)) {
            if ($this->getUserLevel() == self::USER_PATIENT) {
                $db = new Database();

                $query = $db->getDatabase()->prepare(
                    "
                    SELECT v.* FROM users
                    INNER JOIN patients p on users.id = p.user_id
                    INNER JOIN visits v ON v.patient_id = p.id
                    WHERE users.id = :patientId AND v.visited = 0 AND visit_date >= DATE(NOW())
                "
                );
                $query->execute([
                    ':patientId' => $this->getId()
                ]);

                $visits = [];

                while ($row = $query->fetch()) {
                    $visits[] = [
                        'id' => $row['id'],
                        'doctor_id' => $row['doctor_id'],
                        'patient_id' => $row['patient_id'],
                        'visit_date' => $row['visit_date']
                    ];
                }

                return $visits;
            }
        }
        return null;
    }

    /**
     * Get doctor's appointments
     *
     * @param int $doctorId
     *
     * @return array
     */
    public function getDoctorAppointments($doctorId) {
        if ($this->isUserLoggedIn(true)) {
            if($doctorId == null) {
                return null;
            }

            $db = new Database();

            $query = $db->getDatabase()->prepare(
                "
                    SELECT v.* FROM users
                    INNER JOIN doctors d on users.id = d.user_id
                    INNER JOIN visits v ON v.doctor_id = d.id
                    WHERE users.id = :doctorId AND v.visited = 0 AND visit_date >= DATE(NOW())
                "
            );
            $query->execute([
                ':doctorId' => $doctorId
            ]);

            $visits = [];

            while ($row = $query->fetch()) {
                $visits[] = [
                    'id' => $row['id'],
                    'doctor_id' => $row['doctor_id'],
                    'patient_id' => $row['patient_id'],
                    'visit_date' => $row['visit_date']
                ];
            }

            return $visits;
        }
        return null;
    }

    /**
     * Make an appointment with a doctor
     *
     * @param string $date
     *
     * @return bool
     */
    public function createAppointment($date) {
        if ($this->isUserLoggedIn(true)) {
            if ($this->getUserLevel() == self::USER_PATIENT) {
                if($date == null) {
                    return false;
                }

                $doctorId = $this->getPatientDoctor();
                if($doctorId == null) {
                    return false;
                }
                $dAppointments = $this->getDoctorAppointments($doctorId);
                if($dAppointments != null) {
                    if(ArrayUtil::searchForAppointment($doctorId, $date, $dAppointments)) {
                        return false;
                    }
                }

                $db = new Database();
                $dbh = $db->getDatabase();

                $query = $dbh->prepare(
                    "
                    INSERT INTO visits (doctor_id, patient_id, visit_date) VALUES (:doctorId, :patientId, :visitDate)
                "
                );

                if($date == null) {
                    return false;
                }

                try {
                    $dbh->beginTransaction();

                    $query->execute([
                        ':doctorId' => $doctorId,
                        ':patientId' => $this->getId(),
                        ':visitDate' => $date
                    ]);

                    $dbh->commit();

                    return true;
                }catch (\PDOException $ex) {
                    $dbh->rollBack();
                    return false;
                }
            }
        }
        return false;
    }

    /**
     * Get patient's recipe by diagnosis
     *
     * @param int $patientId
     * @param int $diagnosisId
     * @param int $recipeId
     *
     * @return array
     */
    public function getRecipe($patientId, $diagnosisId, $recipeId) {
        if ($this->isUserLoggedIn()) {
            if ($this->getUserLevel() == self::USER_DOCTOR) {
                if($patientId == null || $diagnosisId == null || $recipeId == null) {
                    return null;
                }

                $db = new Database();

                $query = $db->getDatabase()->prepare(
                    "
                    SELECT m.* FROM users
                    INNER JOIN patients p on users.id = p.user_id
                    INNER JOIN diagnoses d on p.id = d.patient_id
                    INNER JOIN medication m on d.id = m.diagnosis_id
                    WHERE users.id = :patientId AND m.diagnosis_id = :diagnosisId AND m.id = :recipeId AND d.active = 1 AND m.active = 1
                    LIMIT 1
                "
                );
                $query->execute([
                    ':patientId' => $patientId,
                    ':diagnosisId' => $diagnosisId,
                    ':recipeId' => $recipeId
                ]);

                $recipe = null;

                if ($row = $query->fetch()) {
                    $recipe = [
                        'id' => $row['id'],
                        'rp' => $row['rp'],
                        'dtdn' => $row['dtdn'],
                        'signa' => $row['signa']
                    ];
                }

                return $recipe;
            }
        }
        return null;
    }

    /**
     * Update patient's recipe
     *
     * @param int $recipeId
     * @param int $rp
     * @param int $dtdn
     * @param int $signa
     *
     * @return bool
     */
    public function updateRecipe($recipeId, $rp, $dtdn, $signa) {
        if ($this->isUserLoggedIn()) {
            if ($this->getUserLevel() == self::USER_DOCTOR) {
                if($recipeId == null) {
                    return null;
                }

                $db = new Database();
                $dbh = $db->getDatabase();

                $query = $dbh->prepare("UPDATE medication SET rp = :rp, dtdn = :dtdn, signa = :signa WHERE id = :id");
                return $query->execute([
                    ':id' => $recipeId,
                    ':rp' => $rp,
                    ':dtdn' => $dtdn,
                    ':signa' => $signa
                ]);
            }
        }
        return false;
    }

    /**
     * Add recipe
     *
     * @param int $diagnosisId
     * @param int $rp
     * @param int $dtdn
     * @param int $signa
     *
     * @return bool
     */
    public function addRecipe($diagnosisId, $rp, $dtdn, $signa) {
        if ($this->isUserLoggedIn()) {
            if ($this->getUserLevel() == self::USER_DOCTOR) {
                if($diagnosisId == null) {
                    return null;
                }

                $db = new Database();
                $dbh = $db->getDatabase();

                $query = $dbh->prepare("INSERT INTO medication (diagnosis_id, rp, dtdn, signa) VALUES (:did, :rp, :dtdn, :signa)");
                try {

                    $dbh->beginTransaction();

                    $query->execute([
                        ':did' => $diagnosisId,
                        ':rp' => $rp,
                        ':dtdn' => $dtdn,
                        ':signa' => $signa
                    ]);

                    $dbh->commit();

                    return true;
                } catch (\PDOException $e) {
                    $dbh->rollback();

                    return false;
                }
            }
        }
        return false;
    }

    /**
     * Deactivate patient's recipe
     *
     * @param $recipeId
     *
     * @return bool
     */
    public function deleteRecipe($recipeId) {
        if ($this->isUserLoggedIn()) {
            if ($this->getUserLevel() == self::USER_DOCTOR) {
                if(!$this->checkAccessToRecipe($recipeId)) {
                    return false;
                }

                $db = new Database();
                $dbh = $db->getDatabase();

                $updateQuery = $dbh->prepare("UPDATE medication SET active = 0 WHERE id = :id");
                return $updateQuery->execute([
                    ':id' => $recipeId
                ]);
            }
        }
        return false;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getPatronymic()
    {
        return $this->patronymic;
    }

    /**
     * @return string
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * @return string
     */
    public function getMobilePhone()
    {
        return $this->mobilePhone;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getHomeAddress()
    {
        return $this->homeAddress;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @param string $patronymic
     */
    public function setPatronymic($patronymic)
    {
        $this->patronymic = $patronymic;
    }

    /**
     * @param string $birthDate
     */
    public function setBirthDate($birthDate)
    {
        $this->birthDate = $birthDate;
    }

    /**
     * @param string $mobilePhone
     */
    public function setMobilePhone($mobilePhone)
    {
        $this->mobilePhone = $mobilePhone;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @param string $homeAddress
     */
    public function setHomeAddress($homeAddress)
    {
        $this->homeAddress = $homeAddress;
    }

    /**
     * @return bool
     */
    public function isActivated()
    {
        return $this->activated;
    }

    /**
     * @param bool $activated
     */
    public function setActivated($activated)
    {
        $this->activated = $activated;
    }

    /**
     * @return int
     */
    public function getUserLevel()
    {
        return $this->userLevel;
    }

    /**
     * @param int $userLevel
     */
    public function setUserLevel($userLevel)
    {
        $this->userLevel = $userLevel;
    }

    /**
     * @return string
     */
    public function getUserToken()
    {
        return $this->userToken;
    }

    /**
     * @param string $userToken
     */
    public function setUserToken($userToken)
    {
        $this->userToken = $userToken;
    }

    /**
     * @return int
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param int $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * @return string|null
     */
    public function getFcmRegToken()
    {
        return $this->fcmRegToken;
    }

    /**
     * @param string|null $fcmRegToken
     */
    public function setFcmRegToken($fcmRegToken)
    {
        $this->fcmRegToken = $fcmRegToken;
    }

    /**
     * @return null|string
     */
    public function getTelegramChatId()
    {
        return $this->telegramChatId;
    }

    /**
     * @param null|string $telegramChatId
     */
    public function setTelegramChatId($telegramChatId)
    {
        $this->telegramChatId = $telegramChatId;
    }
}