<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial; background: #f5f5f5; margin: 0; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #333 0%, #555 100%); color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 28px; }
        .card { background: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card h2 { border-bottom: 2px solid #333; padding-bottom: 10px; margin-top: 0; }
        .stat { display: inline-block; width: 22%; margin-right: 2%; padding: 15px; background: #f9f9f9; border-radius: 3px; text-align: center; }
        .stat strong { font-size: 24px; display: block; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 3px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 3px; }
        a { color: #0066cc; text-decoration: none; }
        a:hover { text-decoration: underline; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f0f0f0; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎬 Admin Dashboard</h1>
            <p>Quản lý phim, người dùng và nội dung</p>
        </div>

        <div class="card">
            <h2>Thông Tin Hệ Thống</h2>
            <?php
            define('BASE_PATH', __DIR__);
            require BASE_PATH . '/vendor/autoload.php';
            require BASE_PATH . '/helpers.php';

            use Doctrine\DBAL\DriverManager;

            // Check auth
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $user = $_SESSION['auth_user'] ?? null;

            if (!$user) {
                echo '<div class="error"><strong>❌ Bạn chưa đăng nhập!</strong> <a href="/">Đăng nhập</a></div>';
                echo '</div></div></body></html>';
                exit;
            }

            // Check admin role
            if (($user['role'] ?? null) !== 1) {
                echo '<div class="error"><strong>❌ Bạn không có quyền truy cập Admin!</strong> Hiện tại bạn là: ' . ($user['role'] == 1 ? 'Admin' : 'User') . ' (Role: ' . $user['role'] . ')</div>';
                echo '<p><a href="/">← Quay lại trang chủ</a></p>';
                echo '</div></div></body></html>';
                exit;
            }

            echo '<div class="success"><strong>✅ Xác thực thành công</strong><br>';
            echo 'User: ' . htmlspecialchars($user['name']) . ' (' . htmlspecialchars($user['email']) . ')<br>';
            echo 'Role: Admin (ID: 1)</div>';

            try {
                $dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
                $dotenv->safeLoad();

                $conn = DriverManager::getConnection([
                    'user'      => $_ENV['DB_USERNAME'] ?? 'root',
                    'password'  => $_ENV['DB_PASSWORD'] ?? '',
                    'dbname'    => $_ENV['DB_NAME'] ?? 'du_an_web_xem_phim',
                    'host'      => $_ENV['DB_HOST'] ?? 'localhost',
                    'driver'    => $_ENV['DB_DRIVER'] ?? 'pdo_mysql',
                    'port'      => $_ENV['DB_PORT'] ?? 3306,
                ]);

                // Get stats
                try {
                    $movieCount = $conn->fetchOne("SELECT COUNT(*) FROM movies");
                } catch (\Exception $e) {
                    $movieCount = 0;
                }

                try {
                    $userCount = $conn->fetchOne("SELECT COUNT(*) FROM users");
                } catch (\Exception $e) {
                    $userCount = 0;
                }

                try {
                    $adminCount = $conn->fetchOne("SELECT COUNT(*) FROM users WHERE role_id = 1");
                } catch (\Exception $e) {
                    $adminCount = 0;
                }

                echo '<div style="margin-top: 15px;">';
                echo '<div class="stat"><strong>' . $movieCount . '</strong> Phim</div>';
                echo '<div class="stat"><strong>' . $userCount . '</strong> Người dùng</div>';
                echo '<div class="stat"><strong>' . $adminCount . '</strong> Admin</div>';
                echo '</div>';

            } catch (\Exception $e) {
                echo '<div class="error">Lỗi kết nối CSDL: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            ?>
        </div>

        <div class="card">
            <h2>⚙️ Quản Lý</h2>
            <ul>
                <li><a href="javascript:alert('Chưa phát triển')">📽️ Quản lý Phim</a></li>
                <li><a href="javascript:alert('Chưa phát triển')">👥 Quản lý Người dùng</a></li>
                <li><a href="javascript:alert('Chưa phát triển')">💬 Duyệt Bình luận</a></li>
                <li><a href="javascript:alert('Chưa phát triển')">🏷️ Quản lý Thể loại</a></li>
            </ul>
        </div>

        <div class="card">
            <p style="text-align: center;">
                <a href="/">← Quay lại trang chủ</a>
            </p>
        </div>
    </div>
</body>
</html>
