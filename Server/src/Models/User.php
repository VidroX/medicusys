<?php

namespace App\Models;

use App\Utils\Database;

class User {
    const USER_UNSPECIFIED = 0;
    const USER_PATIENT = 1;
    const USER_DOCTOR = 2;

    private $id;
    private $firstName;
    private $lastName;
    private $patronymic;
    private $birthDate;
    private $mobilePhone;
    private $email;
    private $homeAddress;
    private $activated;
    private $userLevel;

    /**
     * User constructor.
     * @param int $id
     * @param string $firstName
     * @param string $lastName
     * @param string $patronymic
     * @param string $birthDate
     * @param string $mobilePhone
     * @param string $email
     * @param string $homeAddress
     * @param boolean $activated
     */
    public function __construct($id = null, $firstName = null, $lastName = null, $patronymic = null, $birthDate = null, $mobilePhone = null, $email = null, $homeAddress = null, $activated = false)
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
                        IFNULL(d.user_id, -1) AS user_doctor,
                        IFNULL(p.user_id, -1) AS user_patient
                    FROM users 
                        LEFT JOIN patients p on users.id = p.user_id 
                        LEFT JOIN doctors d on users.id = d.user_id 
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
            $this->setMobilePhone($row['mobilephone']);
            $this->setEmail($row['email']);
            $this->setHomeAddress($row['home_address']);
            $this->setActivated($row['activated'] == 1);

            if($row['user_doctor'] != -1){
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
     * @return bool
     */
    public function isUserLoggedIn() {
        return $this->getCurrentUser() != null;
    }

    /**
     * @param string $email
     * @param string $pass
     *
     * @return mixed
     */
    public function auth($email, $pass) {
        if($email == null || $pass == null) return null;

        $db = new Database();

        $query = $db->getDatabase()->prepare(
            "
                    SELECT 
                        users.*,
                        IFNULL(d.user_id, -1) AS user_doctor,
                        IFNULL(p.user_id, -1) AS user_patient
                    FROM users 
                        LEFT JOIN patients p on users.id = p.user_id 
                        LEFT JOIN doctors d on users.id = d.user_id 
                    WHERE email = :email LIMIT 1
                    "
        );
        $query->execute([
            ":email"=>$email
        ]);

        if($query->rowCount() > 0){
            $row = $query->fetch();

            if(password_verify($pass, $row['pass'])) {
                $this->setId($row['id']);
                $this->setFirstName($row['first_name']);
                $this->setLastName($row['last_name']);
                $this->setPatronymic($row['patronymic']);
                $this->setBirthDate($row['birthdate']);
                $this->setMobilePhone($row['mobilephone']);
                $this->setEmail($row['email']);
                $this->setHomeAddress($row['home_address']);
                $this->setActivated($row['activated'] == 1);

                if($row['user_doctor'] != -1){
                    $this->setUserLevel(self::USER_DOCTOR);
                }elseif ($row['user_patient'] != -1){
                    $this->setUserLevel(self::USER_PATIENT);
                }else{
                    $this->setUserLevel(self::USER_UNSPECIFIED);
                }

                $_SESSION['USER'] = $this;

                return $this;
            }else{
                return json_encode([
                    "status"=>1,
                    "message"=>StatusCodes::STATUS[2]
                ]);
            }
        }else{
            return json_encode([
                "status"=>0,
                "message"=>StatusCodes::STATUS[1]
            ]);
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
     * @return array
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
                $row['mobilephone'],
                $row['email'],
                $row['home_address'],
                $row['activated']
            );
        }

        return $doctors;
    }

    /**
     * @param int $doctorId
     *
     * @return array
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
                    $row['mobilephone'],
                    $row['email'],
                    $row['home_address'],
                    $row['activated']
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
                    $row['mobilephone'],
                    $row['email'],
                    $row['home_address'],
                    $row['activated']
                );
            }

            return $patients;
        }
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
     * Register patient in the system
     *
     * @param string $email
     * @param string $pass
     * @param string $firstName
     * @param string $lastName
     * @param string $patronymic
     * @param string $birthDate
     * @param string $mobilePhone
     * @param string $homeAddress
     * @param int $doctorId
     * @param bool $activated
     *
     * @return mixed
     */
    public function registerPatient($email, $pass, $firstName, $lastName, $patronymic, $birthDate, $mobilePhone, $homeAddress, $doctorId, $activated = false) {
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
        if($mobilePhone == null || ($mobilePhone != null && strlen($mobilePhone) < 10 || strlen($mobilePhone) > 12)){
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
                    users (email, pass, first_name, last_name, patronymic, birthdate, mobilephone, home_address, activated) 
              VALUES 
                    (:email, :pass, :firstName, :lastName, :patronymic, :birthDate, :mobilePhone, :homeAddress, :activated)
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
                $dbh->beginTransaction();

                $query1->execute([
                    ":email" => $email,
                    ":pass" => $hashedPass,
                    ":firstName" => $firstName,
                    ":lastName" => $lastName,
                    ":patronymic" => $patronymic,
                    ":birthDate" => $birthDate,
                    ":mobilePhone" => $mobilePhone,
                    ":homeAddress" => $homeAddress,
                    ":activated" => $activated,
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
     * @param string $mobilePhone
     * @param string $homeAddress
     * @param bool $activated
     *
     * @return mixed
     */
    public function registerDoctor($email, $pass, $firstName, $lastName, $patronymic, $birthDate, $mobilePhone, $homeAddress, $activated = false) {
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
        if($mobilePhone == null || ($mobilePhone != null && strlen($mobilePhone) < 10 || strlen($mobilePhone) > 12)){
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
                    users (email, pass, first_name, last_name, patronymic, birthdate, mobilephone, home_address, activated) 
              VALUES 
                    (:email, :pass, :firstName, :lastName, :patronymic, :birthDate, :mobilePhone, :homeAddress, :activated)
        ");
        $query2 = $dbh->prepare(
            "
              INSERT INTO 
                    doctors (user_id) 
              VALUES 
                    (:id)
        ");

        $hashedPass = password_hash($pass, PASSWORD_DEFAULT);

        try {
            $dbh->beginTransaction();

            $query1->execute([
                ":email" => $email,
                ":pass" => $hashedPass,
                ":firstName" => $firstName,
                ":lastName" => $lastName,
                ":patronymic" => $patronymic,
                ":birthDate" => $birthDate,
                ":mobilePhone" => $mobilePhone,
                ":homeAddress" => $homeAddress,
                ":activated" => $activated,
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
            unset($_SESSION['USER']);
        }
    }

    /**
     * @param int $uid
     *
     * @return int User level (0 = Unspecified, 1 = Patient, 2 = Doctor)
     */
    private function getUserLevelByIdInternal($uid) {
        if($uid != null && $uid > 0) {
            $db = new Database();

            $query = $db->getDatabase()->prepare("SELECT users.id FROM users INNER JOIN doctors d ON users.id = d.user_id WHERE users.id = :id LIMIT 1");
            $query->execute([
                ":id" => $uid
            ]);

            if ($query->rowCount() > 0) {
                return self::USER_DOCTOR;
            } else {
                $query = $db->getDatabase()->prepare("SELECT users.id FROM users INNER JOIN patients p ON users.id = p.user_id WHERE users.id = :id LIMIT 1");
                $query->execute([
                    "id" => $uid
                ]);

                if ($query->rowCount() > 0) {
                    return self::USER_PATIENT;
                }
            }

            return self::USER_UNSPECIFIED;
        }else{
            return null;
        }
    }

    /**
     * @return int User level (0 = Unspecified, 1 = Patient, 2 = Doctor)
     */
    private function getUserLevelInternal() {
        if($this->isUserLoggedIn()){
            $uid = $this->getId();

            $db = new Database();

            $query = $db->getDatabase()->prepare("SELECT users.id FROM users INNER JOIN doctors d ON users.id = d.user_id WHERE users.id = :id LIMIT 1");
            $query->execute([
                ":id"=>$uid
            ]);

            if($query->rowCount() > 0) {
                return self::USER_DOCTOR;
            }else{
                $query = $db->getDatabase()->prepare("SELECT users.id FROM users INNER JOIN patients p ON users.id = p.user_id WHERE users.id = :id LIMIT 1");
                $query->execute([
                    "id"=>$uid
                ]);

                if($query->rowCount() > 0) {
                    return self::USER_PATIENT;
                }
            }

            return self::USER_UNSPECIFIED;
        }else{
            return null;
        }
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
}