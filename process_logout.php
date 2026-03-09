<?php
/**
 * Process Logout - Direct endpoint
 */

define('BASE_PATH', __DIR__);
require BASE_PATH . '/vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Check if AJAX request
    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    // Logout
    unset($_SESSION['auth_user']);

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok'      => true,
            'message' => 'Đăng xuất thành công',
        ], JSON_UNESCAPED_UNICODE);
    } else {
        // Redirect to home
        header('Location: /du_an_ca_nhan/du_an_web_xem_phim/');
        exit;
    }

} catch (\Exception $e) {
    error_log('Logout error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'ok'    => false,
        'error' => 'Lỗi hệ thống',
    ], JSON_UNESCAPED_UNICODE);
}
?>
