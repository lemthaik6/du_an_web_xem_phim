<?php
/**
 * Script test import phim từ API ophim1.com
 * 
 * Dùng để test xem API có hoạt động không,
 * và test quy trình import trước khi setup cron job.
 * 
 * Chạy: php cron/test_import.php [page]
 * VD: php cron/test_import.php 1
 */

// Định nghĩa đường dẫn base
define('BASE_PATH', dirname(__DIR__));

// Load environment variables và autoload
require_once BASE_PATH . '/vendor/autoload.php';
$dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

// Import các class cần thiết
use App\Services\MovieApiService;

// Lấy trang từ argument
$page = (int)($argv[1] ?? 1);
$page = max(1, $page);

echo "=================================================\n";
echo "TEST IMPORT PHIM TỪ API ophim1.com\n";
echo "=================================================\n\n";

try {
    echo "[1] Khởi tạo MovieApiService...\n";
    $apiService = new MovieApiService();
    echo "✓ Thành công\n\n";

    echo "[2] Gọi API lấy danh sách phim (trang $page)...\n";
    echo "URL: https://ophim1.com/danh-sach/phim-moi-cap-nhat?page=$page\n";
    
    $movies = $apiService->getLatestMovies($page);

    if (!$movies) {
        echo "✗ Lỗi: Không lấy được danh sách phim\n";
        echo "Debug: Checking error log...\n";
        $logFile = BASE_PATH . '/storage/logs/php-errors.log';
        if (file_exists($logFile)) {
            echo "Last errors:\n";
            echo shell_exec("tail -20 " . escapeshellarg($logFile));
        }
        exit(1);
    }

    echo "✓ Lấy được " . count($movies) . " phim\n\n";

    echo "[3] Chi tiết phim lấy được:\n";
    echo "─────────────────────────────────────────────\n";

    foreach ($movies as $index => $movie) {
        $num = $index + 1;
        $name = $movie['name'] ?? 'Unknown';
        $slug = $movie['slug'] ?? 'N/A';
        $poster = $movie['poster_url'] ?? 'N/A';
        
        echo "\n$num. Tên phim: $name\n";
        echo "   Slug: $slug\n";
        echo "   Poster: " . (strlen($poster) > 50 ? substr($poster, 0, 50) . '...' : $poster) . "\n";

        // Test format dữ liệu
        $formatted = $apiService->formatMovieForDatabase($movie);
        echo "   ✓ Định dạng OK\n";
    }

    echo "\n─────────────────────────────────────────────\n\n";

    echo "[4] Test chi tiết phim (lấy phim đầu tiên):\n";
    $firstMovie = $movies[0] ?? null;
    
    if (!$firstMovie) {
        echo "✗ Không có phim để test\n";
        exit(1);
    }

    $slug = $firstMovie['slug'] ?? '';
    echo "Slug: $slug\n";
    
    if (empty($slug)) {
        echo "✗ Slug trống\n";
        exit(1);
    }

    echo "Đang gọi API chi tiết phim...\n";
    $detail = $apiService->getMovieDetail($slug);

    if (!$detail) {
        echo "✗ Lỗi: Không lấy được chi tiết phim\n";
        exit(1);
    }

    echo "✓ Lấy được chi tiết\n";
    echo "   Tên: " . ($detail['movie']['name'] ?? 'N/A') . "\n";
    echo "   Số tập: " . count($detail['episodes'] ?? []) . " tập\n";

    if (!empty($detail['episodes'])) {
        echo "   Tập đầu: " . ($detail['episodes'][0]['name'] ?? 'N/A') . "\n";
        echo "   Video link: " . (strlen($detail['episodes'][0]['link_embed'] ?? '') > 0 ? 'OK' : 'Không có') . "\n";

        // Test format tập
        $formatted = $apiService->formatEpisodesForDatabase($detail['episodes'], 1);
        echo "   ✓ Định dạng tập OK (" . count($formatted) . " tập)\n";
    }

    echo "\n[✓] TẤT CẢ TEST PASS\n";
    echo "=================================================\n";
    echo "API hoạt động bình thường!\n";
    echo "Bạn có thể chạy import_movies.php để import phim.\n";
    echo "=================================================\n";

} catch (\Throwable $e) {
    echo "\n✗ LỖI: " . $e->getMessage() . "\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
?>
