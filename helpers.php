<?php

use eftec\bladeone\BladeOne;

if (!function_exists('view')) {
    function view($view, $data = [])
    {
        $views = __DIR__ . '/views';
        $cache = __DIR__ . '/storage/compiles';

        // MODE_DEBUG allows to pinpoint troubles.
        $blade = new BladeOne($views, $cache, BladeOne::MODE_DEBUG);

        echo $blade->run($view, $data);
    }
}

if (!function_exists('is_upload')) {
    function is_upload($key)
    {
        return isset($_FILES[$key]) && $_FILES[$key]['size'] > 0;
    }
}

if (!function_exists('redirect')) {
    function redirect($path)
    {
        header('Location: ' . $_ENV['APP_URL'] . $path);
        exit;
    }
}

if (!function_exists('redirect404')) {
    function redirect404()
    {
        header('HTTP/1.1 404 Not Found');
        exit;
    }
}

if (!function_exists('file_url')) {
    function file_url(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $base = $_ENV['APP_URL'] ?: '';

        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('debug')) {
    function debug(...$data)
    {
        echo '<pre>';
        print_r($data);
        die;
    }
}

if (!function_exists('route')) {
    function route($path)
    {
        return $_ENV['APP_URL'] . $path;
    }
}

if (!function_exists('setFlash')) {
    function setFlash($key, $message)
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash'][$key] = $message;
    }
}

if (!function_exists('getFlash')) {
    function getFlash($key, $default = null)
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['flash'][$key])) {
            return $default;
        }

        $value = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);

        return $value;
    }
}

if (!function_exists('auth_user')) {
    function auth_user(): ?array
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $user = $_SESSION['auth_user'] ?? null;
        return is_array($user) ? $user : null;
    }
}

if (!function_exists('is_admin')) {
    function is_admin(): bool
    {
        $user = auth_user();
        return (bool)($user && ($user['role'] ?? null) === 'admin');
    }
}

if (!function_exists('json')) {
    function json(array $payload, int $status = 200): void
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($status);
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
