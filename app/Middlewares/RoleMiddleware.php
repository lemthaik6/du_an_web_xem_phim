<?php

namespace App\Middlewares;

class RoleMiddleware
{
    /**
     * Require a role name, e.g. "admin" or "user".
     * Guest = not logged in.
     */
    public static function require(string $role): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $user = $_SESSION['auth_user'] ?? null;
        $userRole = is_array($user) ? ($user['role'] ?? null) : null;

        if ($userRole !== $role) {
            header('Content-Type: text/html; charset=utf-8');
            http_response_code(403);
            echo '403 Forbidden';
            exit;
        }
    }
}

