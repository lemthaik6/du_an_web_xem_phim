<?php

namespace App;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;

class Model
{
    protected $connection;

    public function __construct()
    {
        $connectionParams = [
            'user'      => $_ENV['DB_USERNAME'] ?? $_SERVER['DB_USERNAME'] ?? 'root',
            'password'  => $_ENV['DB_PASSWORD'] ?? $_SERVER['DB_PASSWORD'] ?? '',
            'dbname'    => $_ENV['DB_NAME'] ?? $_SERVER['DB_NAME'] ?? 'du_an_web_xem_phim',
            'host'      => $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? 'localhost',
            'driver'    => $_ENV['DB_DRIVER'] ?? $_SERVER['DB_DRIVER'] ?? 'pdo_mysql',
            'port'      => $_ENV['DB_PORT'] ?? $_SERVER['DB_PORT'] ?? 3306,
        ];

        try {
            $this->connection = DriverManager::getConnection($connectionParams);
        } catch (Exception $e) {
            // Log the error but don't die - allow graceful degradation
            error_log('Database connection error: ' . $e->getMessage());
            $this->connection = null;
        }
    }

    public function __destruct()
    {
        $this->connection = null;
    }
}
