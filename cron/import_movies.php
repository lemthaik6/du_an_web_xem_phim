<?php

// Định nghĩa đường dẫn base
define('BASE_PATH', dirname(__DIR__));

// Load environment variables và autoload
require_once BASE_PATH . '/vendor/autoload.php';
$dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

// Import các class cần thiết
use App\Models\Movie;
use App\Services\MovieApiService;

/**
 * Hàm ghi log
 */
function logImport($message, $type = 'info')
{
    $timestamp = date('Y-m-d H:i:s');
    $logFile = BASE_PATH . '/storage/logs/import_movies.log';
    $logMessage = "[$timestamp] [$type] $message" . PHP_EOL;
    
    // Tạo thư mục logs nếu chưa tồn tại
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    echo $logMessage;
}

/**
 * Main import logic
 */
function importMovies()
{
    logImport('===== BẮT ĐẦU IMPORT PHIM =====');
    
    try {
        // Khởi tạo service
        $apiService = new MovieApiService();
        $movieModel = new Movie();
        
        $importedCount = 0;
        $skippedCount = 0;
        $failedCount = 0;
        
        // ===== BƯỚC 1: Lấy danh sách phim mới =====
        logImport('Đang lấy danh sách phim mới từ API...');
        $movies = $apiService->getLatestMovies(1);
        
        if (!$movies || empty($movies)) {
            logImport('Không lấy được danh sách phim từ API', 'error');
            return;
        }
        
        logImport("Tìm thấy " . count($movies) . " phim từ API");
        
        // ===== BƯỚC 2: Xử lý từng phim =====
        foreach ($movies as $movie) {
            $slug = $movie['slug'] ?? '';
            $name = $movie['name'] ?? 'Unknown';
            
            if (empty($slug)) {
                logImport("Phim '$name' không có slug, bỏ qua", 'warning');
                $skippedCount++;
                continue;
            }
            
            // Kiểm tra slug đã tồn tại
            if ($movieModel->checkSlugExists($slug)) {
                logImport("Phim '$name' (slug: $slug) đã tồn tại, bỏ qua");
                $skippedCount++;
                continue;
            }
            
            // Chuẩn hóa dữ liệu phim từ API
            $movieData = $apiService->formatMovieForDatabase($movie);
            
            // Thêm phim mới vào database
            logImport("Đang thêm phim: $name");
            try {
                $movieId = $movieModel->createMovie($movieData);
            } catch (\Throwable $e) {
                logImport("  ✗ Lỗi tạo phim: " . $e->getMessage(), 'error');
                $failedCount++;
                continue;
            }
            
            if (!$movieId) {
                logImport("Thêm phim '$name' thất bại", 'error');
                $failedCount++;
                continue;
            }
            
            // ===== BƯỚC 3: Lấy chi tiết phim =====
            logImport("  Lấy chi tiết phim: $name (ID: $movieId)");
            $movieDetail = $apiService->getMovieDetail($slug);
            
            if (!$movieDetail || empty($movieDetail['episodes'])) {
                logImport("  Không lấy được chi tiết phim '$name'", 'warning');
                $importedCount++;
                continue;
            }
            
            // ===== BƯỚC 4: Lưu danh sách tập =====
            logImport("  Lấy " . count($movieDetail['episodes']) . " tập");
            
            // Chuẩn hóa dữ liệu tập từ API
            $episodes = $apiService->formatEpisodesForDatabase(
                $movieDetail['episodes'],
                $movieId
            );
            
            // Thêm tập vào database
            if ($movieModel->addEpisodes($movieId, $episodes)) {
                logImport("  ✓ Thêm phim '$name' thành công (Episodes: " . count($episodes) . ")");
                $importedCount++;
            } else {
                logImport("  ✗ Thêm tập phim '$name' thất bại", 'error');
                $failedCount++;
            }
        }
        
        // Tóm tắt
        logImport('===== KẾT THÚC IMPORT =====');
        logImport("Phim thêm mới: $importedCount");
        logImport("Phim bỏ qua: $skippedCount");
        logImport("Phim lỗi: $failedCount");
        logImport("Tổng cộng: " . ($importedCount + $skippedCount + $failedCount));
        
    } catch (\Throwable $e) {
        logImport('Lỗi import: ' . $e->getMessage(), 'error');
        logImport('Stack: ' . $e->getTraceAsString(), 'error');
    }
}

// Chạy import
importMovies();
?>
