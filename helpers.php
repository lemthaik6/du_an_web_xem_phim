<?php
/**
 * Application Helper Functions
 * 
 * Global helper functions for common tasks like rendering views,
 * handling responses, and flash messages.
 */

/**
 * Render a Blade template and return/display its contents
 * 
 * @param string $view Path to view (e.g. 'home', 'admin.dashboard')
 * @param array $data Variables to pass to the view
 * @param bool $return If true, return output instead of echoing
 * @return string|void
 */
if (!function_exists('view')) {
    function view(string $view, array $data = [], bool $return = false)
    {
        try {
            $blade = $GLOBALS['blade'] ?? null;
            
            if (!$blade) {
                throw new \Exception('Blade template engine not initialized');
            }
            
            $output = $blade->run($view, $data);
            
            if ($return) {
                return $output;
            }
            
            echo $output;
            exit;
        } catch (\Exception $e) {
            http_response_code(500);
            echo "Lỗi: " . htmlspecialchars($e->getMessage());
            exit;
        }
    }
}

/**
 * Output a JSON response
 * 
 * @param array $data Data to encode as JSON
 * @param int $statusCode HTTP status code
 * @param int $flags JSON encoding flags
 * @return void
 */
if (!function_exists('json')) {
    function json(array $data, int $statusCode = 200, int $flags = JSON_UNESCAPED_UNICODE)
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data, $flags);
        exit;
    }
}

/**
 * Redirect to a URL
 * 
 * @param string $url URL to redirect to
 * @param int $statusCode HTTP status code (302 temporary, 301 permanent)
 * @return void
 */
if (!function_exists('redirect')) {
    function redirect(string $url, int $statusCode = 302)
    {
        http_response_code($statusCode);
        header('Location: ' . $url);
        exit;
    }
}

/**
 * Redirect with 404 status
 * 
 * @param string $message Optional error message
 * @return void
 */
if (!function_exists('redirect404')) {
    function redirect404(string $message = 'Trang không tồn tại')
    {
        header('Content-Type: text/html; charset=utf-8');
        http_response_code(404);
        echo '<h1>404 - Không tìm thấy</h1><p>' . htmlspecialchars($message) . '</p>';
        exit;
    }
}

/**
 * Retrieve and clear a flash message from session
 * 
 * @param string $key Message key (e.g. 'error', 'success', 'warning')
 * @return string|null The message or null if not found
 */
if (!function_exists('getFlash')) {
    function getFlash(string $key): ?string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $message = $_SESSION['flash'][$key] ?? null;
        
        // Clear the message after retrieving it
        if (isset($_SESSION['flash'][$key])) {
            unset($_SESSION['flash'][$key]);
        }
        
        return $message;
    }
}

/**
 * Store a flash message in session (single use, cleared after display)
 * 
 * @param string $key Message key (e.g. 'error', 'success', 'warning')
 * @param string $message The message to store
 * @return void
 */
if (!function_exists('setFlash')) {
    function setFlash(string $key, string $message)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        
        $_SESSION['flash'][$key] = $message;
    }
}

/**
 * Get the current authenticated user from session
 * 
 * @return array|null User array with id, name, email, role, or null if not logged in
 */
if (!function_exists('auth')) {
    function auth(): ?array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return $_SESSION['auth_user'] ?? null;
    }
}

/**
 * Check if a user is authenticated
 * 
 * @return bool True if logged in
 */
if (!function_exists('isAuthenticated')) {
    function isAuthenticated(): bool
    {
        return auth() !== null;
    }
}

/**
 * Check if authenticated user has a specific role
 * 
 * @param string $role Role to check
 * @return bool
 */
if (!function_exists('hasRole')) {
    function hasRole(string $role): bool
    {
        $user = auth();
        return $user && ($user['role'] ?? null) === $role;
    }
}

/**
 * Get the database connection instance
 * 
 * @return \Doctrine\DBAL\Connection|null
 */
if (!function_exists('getConnection')) {
    function getConnection(): ?\Doctrine\DBAL\Connection
    {
        static $connection = null;
        
        if ($connection === null) {
            try {
                $connectionParams = [
                    'user'      => $_ENV['DB_USERNAME'] ?? 'root',
                    'password'  => $_ENV['DB_PASSWORD'] ?? '',
                    'dbname'    => $_ENV['DB_NAME'] ?? 'du_an_web_xem_phim',
                    'host'      => $_ENV['DB_HOST'] ?? 'localhost',
                    'driver'    => $_ENV['DB_DRIVER'] ?? 'pdo_mysql',
                    'port'      => $_ENV['DB_PORT'] ?? 3306,
                ];
                
                $connection = \Doctrine\DBAL\DriverManager::getConnection($connectionParams);
            } catch (\Exception $e) {
                // Log error but don't crash; some routes are public
            }
        }
        
        return $connection;
    }
}

/**
 * Format a date for display
 * 
 * @param string $date Date string
 * @param string $format Output format (default: d/m/Y H:i)
 * @return string Formatted date
 */
if (!function_exists('formatDate')) {
    function formatDate(string $date, string $format = 'd/m/Y H:i'): string
    {
        try {
            $timestamp = strtotime($date);
            return $timestamp ? date($format, $timestamp) : $date;
        } catch (\Exception $e) {
            return $date;
        }
    }
}

/**
 * Truncate text to specified length with ellipsis
 * 
 * @param string $text Text to truncate
 * @param int $length Maximum length
 * @param string $suffix Suffix to add (default: ...)
 * @return string
 */
if (!function_exists('truncate')) {
    function truncate(string $text, int $length = 100, string $suffix = '...'): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        
        return substr($text, 0, $length) . $suffix;
    }
}

/**
 * Get environment variable with optional default
 * 
 * @param string $key Environment variable name
 * @param mixed $default Default value if not found
 * @return mixed
 */
if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        return $_ENV[$key] ?? $default;
    }
}

/**
 * Escape HTML special characters
 * 
 * @param string $text Text to escape
 * @return string Escaped text
 */
if (!function_exists('esc')) {
    function esc(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Convert title to slug
 * 
 * @param string $title Title (e.g., "The Avengers Endgame")
 * @return string Slug (e.g., "the-avengers-endgame")
 */
if (!function_exists('titleToSlug')) {
    function titleToSlug(string $title): string
    {
        $title = strtolower($title);
        $title = preg_replace('/[^a-z0-9]+/', '-', $title);
        $title = trim($title, '-');
        return $title;
    }
}

/**
 * Generate a URL for a given route path
 * 
 * @param string $path The path (e.g., '/phim/slug' or '/tim-kiem')
 * @param array $params Optional query parameters
 * @return string The full URL
 */
if (!function_exists('route')) {
    function route(string $path, array $params = []): string
    {
        $baseUrl = rtrim($_ENV['APP_URL'] ?? env('APP_URL', 'http://localhost/du_an_ca_nhan/du_an_web_xem_phim/'), '/');
        $url = $baseUrl . $path;
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $url;
    }
}

/**
 * Get file URL (for uploads, posters, etc)
 * 
 * @param string $path File path (e.g., 'uploads/poster.jpg')
 * @return string The full file URL
 */
if (!function_exists('file_url')) {
    function file_url(string $path): string
    {
        if (empty($path)) {
            return '';
        }
        
        // If already a full URL, return as is
        if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
            return $path;
        }
        
        $baseUrl = rtrim($_ENV['APP_URL'] ?? env('APP_URL', 'http://localhost/du_an_ca_nhan/du_an_web_xem_phim/'), '/');
        return $baseUrl . '/' . ltrim($path, '/');
    }
}
