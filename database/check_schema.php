<?php
define('BASE_PATH', __DIR__ . '/..');
require BASE_PATH . '/vendor/autoload.php';

use Doctrine\DBAL\DriverManager;

$c = DriverManager::getConnection([
    'user' => 'root',
    'password' => '',
    'dbname' => 'du_an_web_xem_phim',
    'host' => 'localhost',
    'driver' => 'pdo_mysql'
]);

echo "=== MOVIES TABLE ===\n";
$cols = $c->fetchAllAssociative('DESCRIBE movies');
foreach ($cols as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ")\n";
}

echo "\n=== EPISODES TABLE ===\n";
$cols = $c->fetchAllAssociative('DESCRIBE episodes');
foreach ($cols as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ")\n";
}
?>
