<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$connectionParams = [
    'user'      => $_ENV['DB_USERNAME'] ?? 'root',
    'password'  => $_ENV['DB_PASSWORD'] ?? '',
    'dbname'    => $_ENV['DB_NAME'] ?? 'du_an_web_xem_phim',
    'host'      => $_ENV['DB_HOST'] ?? 'localhost',
    'driver'    => $_ENV['DB_DRIVER'] ?? 'pdo_mysql',
    'port'      => $_ENV['DB_PORT'] ?? 3306,
];

$conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams);
$result = $conn->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='users' AND TABLE_SCHEMA=DATABASE()")->fetchAllAssociative();

echo "Columns in users table:\n";
foreach ($result as $row) {
    echo "- " . $row['COLUMN_NAME'] . "\n";
}
