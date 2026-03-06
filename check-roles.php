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

echo "Available roles:\n";
$roles = $conn->query("SELECT * FROM roles")->fetchAllAssociative();
foreach ($roles as $role) {
    echo "- ID: {$role['id']}, Name: {$role['name']}\n";
}

if (empty($roles)) {
    echo "No roles found. Creating default roles...\n\n";
    
    $now = date('Y-m-d H:i:s');
    $conn->insert('roles', [
        'id' => 1,
        'name' => 'admin',
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    echo "✓ Created admin role (ID: 1)\n";
    
    $conn->insert('roles', [
        'id' => 2,
        'name' => 'user',
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    echo "✓ Created user role (ID: 2)\n";
}
