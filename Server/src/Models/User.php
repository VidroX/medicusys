<?php

namespace App\Models;

use App\Utils\Database;

class User {
    const USER_UNIDENTIFIED = 0;
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

        $query = $db->getDatabase()->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $query->execute([
            ":id"=>$id
        ]);

        while ($row = $query->fetch()) {
            $this->setId($row['id']);
            $this->setFirstName($row['first_name']);
            $this->setLastName($row['last_name']);
            $this->setPatronymic($row['patronymic']);
            $this->setBirthDate($row['birthdate']);
            $this->setMobilePhone($row['mobilephone']);
            $this->setEmail($row['email']);
            $this->setHomeAddress($row['home_address']);
            $this->setActivated($row['activated'] == 1);
        }

        return $this;
    }

    /**
     * @return User User object
     */
    public function getCurrentUser() {
        if(session_status() == PHP_SESSION_ACTIVE && isset($_SESSION['USER']) && !empty($_SESSION['USER']) && $_SESSION['USER'] != null){
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

        $query = $db->getDatabase()->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
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

                $_SESSION['USER'] = $this;

                return $this;
            }else{
                return json_encode([
                    "status"=>1,
                    "message"=>"Invalid password"
                ]);
            }
        }else{
            return json_encode([
                "status"=>0,
                "message"=>"Invalid login or password"
            ]);
        }
    }

    public function logout() {
        if($this->isUserLoggedIn()){
            unset($_SESSION['USER']);
        }
    }

    /**
     * @return int User level (0 = Unidentified, 1 = Patient, 2 = Doctor)
     */
    public function getUserLevel() {
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

            return self::USER_UNIDENTIFIED;
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


}