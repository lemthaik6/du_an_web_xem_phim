<?php

use Bramus\Router\Router;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\MovieController;
use App\Controllers\SearchController;
use App\Controllers\ProfileController;
use App\Controllers\CommentController;
use App\Controllers\FavoriteController;
use App\Controllers\WatchHistoryController;
use App\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Controllers\Admin\MovieController as AdminMovieController;
use App\Controllers\Admin\EpisodeController as AdminEpisodeController;
use App\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Controllers\Admin\UserController as AdminUserController;
use App\Controllers\Admin\CommentController as AdminCommentController;
use App\Controllers\Admin\BannerController as AdminBannerController;

$router = new Router();

// Đây là nơi khai báo các route

// Public pages
$router->get('/', HomeController::class . '@index');
$router->get('/phim/([^/]+)', MovieController::class . '@show'); // slug
$router->get('/xem/([^/]+)/([0-9]+)', MovieController::class . '@watch'); // slug, episode_number
$router->get('/tim-kiem', SearchController::class . '@index');

// Auth (supports modal via AJAX + fallback pages)
$router->get('/dang-nhap', AuthController::class . '@loginPage');
$router->post('/dang-nhap', AuthController::class . '@login');
$router->post('/dang-ky', AuthController::class . '@register');
$router->post('/dang-xuat', AuthController::class . '@logout');

// User pages (requires login)
$router->before('GET|POST', '/tai-khoan.*', function () {
    \App\Middlewares\AuthMiddleware::handle();
});
$router->get('/tai-khoan', ProfileController::class . '@index');

// User actions (AJAX)
$router->before('POST', '/api/(binh-luan|yeu-thich|lich-su-xem).*', function () {
    \App\Middlewares\AuthMiddleware::handle();
});
$router->get('/api/phim/([0-9]+)/binh-luan', CommentController::class . '@index'); // public comments fetch
$router->post('/api/phim/([0-9]+)/binh-luan', CommentController::class . '@store');
$router->post('/api/binh-luan/([0-9]+)/tra-loi', CommentController::class . '@reply');
$router->post('/api/yeu-thich/toggle', FavoriteController::class . '@toggle');
$router->post('/api/lich-su-xem/upsert', WatchHistoryController::class . '@upsert');

// Admin (RBAC: admin only)
$router->before('GET|POST', '/admin(/.*)?', function () {
    \App\Middlewares\AuthMiddleware::handle();
    \App\Middlewares\RoleMiddleware::require('admin');
});

$router->get('/admin', AdminDashboardController::class . '@index');
$router->get('/admin/phim', AdminMovieController::class . '@index');
$router->get('/admin/phim/them', AdminMovieController::class . '@create');
$router->post('/admin/phim/them', AdminMovieController::class . '@store');
$router->get('/admin/phim/([0-9]+)/sua', AdminMovieController::class . '@edit');
$router->post('/admin/phim/([0-9]+)/sua', AdminMovieController::class . '@update');
$router->post('/admin/phim/([0-9]+)/xoa', AdminMovieController::class . '@destroy');

$router->get('/admin/tap-phim', AdminEpisodeController::class . '@index');
$router->get('/admin/the-loai', AdminCategoryController::class . '@index');
$router->get('/admin/nguoi-dung', AdminUserController::class . '@index');
$router->get('/admin/binh-luan', AdminCommentController::class . '@index');
$router->get('/admin/banner', AdminBannerController::class . '@index');

// ------------------------

$router->run();
