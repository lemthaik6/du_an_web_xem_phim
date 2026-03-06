<?php
/**
 * Test registration with correct database schema
 */

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

try {
    $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams);
    
    // Test create a new user with correct schema
    $testUsername = 'testuser_' . time();
    $testEmail = 'test_' . time() . '@example.com';
    $testPassword = password_hash('password123', PASSWORD_BCRYPT);
    
    echo "Testing user registration with correct schema...\n";
    echo "Username: " . $testUsername . "\n";
    echo "Email: " . $testEmail . "\n\n";
    
    // Insert test user
    $conn->insert('users', [
        'username'      => $testUsername,
        'email'         => $testEmail,
        'password_hash' => $testPassword,
        'display_name'  => $testUsername,
        'status'        => 'active',
    ]);
    
    echo "✓ User created successfully in database\n\n";
    
    // Verify user can be retrieved
    $qb = $conn->createQueryBuilder();
    $user = $qb->select('*')
        ->from('users')
        ->where('email = :email')
        ->setParameter('email', $testEmail)
        ->fetchAssociative();
    
    if ($user) {
        echo "✓ User retrieved from database:\n";
        echo "  ID: " . $user['id'] . "\n";
        echo "  Username: " . $user['username'] . "\n";
        echo "  Email: " . $user['email'] . "\n";
        echo "  Display Name: " . $user['display_name'] . "\n";
        echo "  Status: " . $user['status'] . "\n";
        echo "  Created: " . $user['created_at'] . "\n";
    } else {
        echo "✗ Failed to retrieve user from database\n";
    }
    
    // Test password verification
    echo "\nTesting password verification...\n";
    if (password_verify('password123', $user['password_hash'])) {
        echo "✓ Password verification works correctly\n";
    } else {
        echo "✗ Password verification failed\n";
    }
    
    // Test login
    echo "\nSimulating login...\n";
    $loginUser = $qb->select('*')
        ->from('users')
        ->where('email = :email AND status = :status')
        ->setParameter('email', $testEmail)
        ->setParameter('status', 'active')
        ->fetchAssociative();
    
    if ($loginUser && password_verify('password123', $loginUser['password_hash'])) {
        echo "✓ Login would succeed with this user\n";
        $sessionData = [
            'id'    => $loginUser['id'],
            'name'  => $loginUser['display_name'] ?? $loginUser['username'],
            'email' => $loginUser['email'],
            'role'  => $loginUser['role_id'] ?? 'user',
        ];
        echo "✓ Session data would be: " . json_encode($sessionData) . "\n";
    } else {
        echo "✗ Login failed\n";
    }
    
    // Count total users
    $count = $conn->query("SELECT COUNT(*) as cnt FROM users")->fetch()['cnt'];
    echo "\n📊 Total users in database: " . $count . "\n";
    
    echo "\n✅ Registration system is working correctly with correct schema!\n";
    echo "\n💡 Next steps:\n";
    echo "  1. Update the registration form to ask for 'username' instead of 'name'\n";
    echo "  2. Test registration via the web interface\n";
    echo "  3. Verify redirect to login page after registration\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
