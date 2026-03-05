<?php

namespace App\Middlewares;

class AuthMiddleware
{
    public static function handle(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['auth_user'])) {
            // If it's an AJAX request, return JSON to allow modal login.
            $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
                && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

            if ($isAjax || str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(401);
                echo json_encode([
                    'ok' => false,
                    'message' => 'Bạn cần đăng nhập để thực hiện thao tác này.',
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            setFlash('error', 'Bạn cần đăng nhập để truy cập.');
            redirect('/dang-nhap');
        }
    }
}

