<?php

namespace App\Utils;

use App\Models\User;
use PDO;

class Database {
    private $config;
    private $pdo;

    /**
     * Database constructor.
     */
    public function __construct() {
        $this->config = include(__DIR__."/../../config/core.php");

        $dsn = "mysql:host={$this->config['db']['host']};dbname={$this->config['db']['dbName']};charset={$this->config['db']['charset']}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $this->pdo = new PDO($dsn, $this->config['db']['user'], $this->config['db']['pass'], $options);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    /**
     * @return PDO
     */
    public function getDatabase() {
        return $this->pdo;
    }
}