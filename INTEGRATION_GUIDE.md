# Hướng dẫn tích hợp API phim ophim1.com

## Tổng quan

Dự án này giúp bạn tích hợp API phim miễn phí từ [ophim1.com](https://ophim1.com) vào hệ thống website xem phim sử dụng PHP thuần với MVC architecture.

## Các file được tạo/cập nhật

### 1. Service Layer
- **`app/Services/MovieApiService.php`** - Service gọi API phim
  - `getLatestMovies($page)` - Lấy danh sách phim mới
  - `getMovieDetail($slug)` - Lấy chi tiết phim
  - `formatMovieForDatabase($data)` - Chuẩn hóa dữ liệu phim
  - `formatEpisodesForDatabase($episodes, $movieId)` - Chuẩn hóa dữ liệu tập

### 2. Model Layer
- **`app/Models/Movie.php`** - Cập nhật model phim
  - `checkSlugExists($slug)` - Kiểm tra slug trùng
  - `createMovie($data)` - Thêm phim mới
  - `addEpisodes($movieId, $episodes)` - Thêm danh sách tập
  - `updateMovie($movieId, $data)` - Cập nhật thông tin phim

- **`app/Models/Episode.php`** - Cập nhật model tập phim
  - `incrementViews($episodeId)` - Tăng lượt xem tập
  - `getById($id)` - Lấy thông tin tập phim
  - `existsById($id)` - Kiểm tra tập tồn tại
  - `deleteByMovieId($movieId)` - Xóa tất cả tập của phim

### 3. Controller Layer
- **`app/Controllers/MovieController.php`** - Cập nhật controller phim
  - `index()` - Hiển thị danh sách phim với filter
  - `show($slug)` - Hiển thị chi tiết phim
  - `watch($slug, $episodeId)` - Xem tập phim (player)

### 4. Cron Script
- **`cron/import_movies.php`** - Script import phim tự động
  - Lấy danh sách phim mới từ API
  - Kiểm tra trùng phim
  - Lưu phim vào database
  - Lấy chi tiết phim
  - Lưu tập phim
  - Ghi log chi tiết

### 5. Views
- **`views/movie/index.blade.php`** - Danh sách phim với filter
- **`views/movie/show.blade.php`** - Chi tiết phim (có sẵn)
- **`views/movie/watch.blade.php`** - Xem phim player (có sẵn)

---

## Cấu trúc bảng Database

Các bảng cần có cấu trúc như sau:

### Bảng `movies`
```sql
CREATE TABLE `movies` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) UNIQUE NOT NULL,
  `poster_url` TEXT,
  `thumb_url` TEXT,
  `description` LONGTEXT,
  `release_year` INT,
  `country` VARCHAR(100),
  `category` VARCHAR(255),
  `views_count` INT DEFAULT 0,
  `is_published` TINYINT DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Bảng `episodes`
```sql
CREATE TABLE `episodes` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `movie_id` INT NOT NULL,
  `episode_name` VARCHAR(255),
  `video_url` TEXT NOT NULL,
  `views_count` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`movie_id`) REFERENCES `movies`(`id`) ON DELETE CASCADE
);
```

---

## Cách sử dụng

### 1. Import phim thủ công

Chạy script import từ terminal:

```bash
php cron/import_movies.php
```

### 2. Setup Cron Job tự động

**Trên Linux/Mac:**

```bash
# Mở crontab
crontab -e

# Thêm dòng này để chạy mỗi 6 tiếng:
0 */6 * * * cd /path/to/du_an_web_xem_phim && php cron/import_movies.php

# Hoặc chạy mỗi 1 tiếng:
0 * * * * cd /path/to/du_an_web_xem_phim && php cron/import_movies.php

# Hoặc chạy mỗi 30 phút:
*/30 * * * * cd /path/to/du_an_web_xem_phim && php cron/import_movies.php
```

**Trên Windows (Task Scheduler):**

1. Mở **Task Scheduler**
2. Click **Create Basic Task**
3. Đặt tên: "Import Movies"
4. Chọn **Triggers** → New:
   - Repeat task every: 6 hours (hoặc số giờ khác)
5. Chọn **Actions** → New:
   - Program: `C:\php\php.exe` (đường dẫn PHP)
   - Arguments: `C:\laragon\www\du_an_ca_nhan\du_an_web_xem_phim\cron\import_movies.php`
   - Start in: `C:\laragon\www\du_an_ca_nhan\du_an_web_xem_phim`

### 3. Xem log import

```bash
# Xem file log
tail -f storage/logs/import_movies.log
```

---

## Quy trình hoạt động

```
┌─────────────────────────────────────────────────────┐
│ 1. Script: cron/import_movies.php                   │
│    ↓ Gọi MovieApiService                            │
├─────────────────────────────────────────────────────┤
│ 2. MovieApiService: getLatestMovies()               │
│    ↓ Gọi API: ophim1.com/danh-sach/phim-moi-cap-nhat│
│    ↓ Nhận mảng phim từ API                          │
├─────────────────────────────────────────────────────┤
│ 3. Mỗi phim:                                        │
│    a) Kiểm tra slug tồn tại (Movie::checkSlugExists)│
│    b) Chuẩn hóa dữ liệu (formatMovieForDatabase)    │
│    c) Lưu phim (Movie::createMovie)                 │
├─────────────────────────────────────────────────────┤
│ 4. MovieApiService: getMovieDetail($slug)           │
│    ↓ Gọi API: ophim1.com/phim/{slug}                │
│    ↓ Nhận chi tiết phim + danh sách tập             │
├─────────────────────────────────────────────────────┤
│ 5. Chuẩn hóa tập (formatEpisodesForDatabase)        │
│    ↓ Lưu tập phim (Movie::addEpisodes)              │
├─────────────────────────────────────────────────────┤
│ 6. Ghi log chi tiết                                 │
│    ↓ storage/logs/import_movies.log                 │
└─────────────────────────────────────────────────────┘
```

---

## API Endpoints

### Danh sách phim mới cập nhật
```
GET https://ophim1.com/danh-sach/phim-moi-cap-nhat?page=1
```

Phản hồi:
```json
{
  "status": true,
  "data": {
    "items": [
      {
        "name": "Tên phim",
        "slug": "ten-phim",
        "poster_url": "url_poster",
        "thumb_url": "url_thumb",
        "publish_year": 2024,
        "country": [{"name": "Việt Nam"}],
        "category": [{"name": "Hành động"}],
        "content": "Mô tả phim"
      }
    ]
  }
}
```

### Chi tiết phim
```
GET https://ophim1.com/phim/{slug}
```

Phản hồi:
```json
{
  "status": true,
  "movie": {
    "name": "Tên phim",
    "slug": "ten-phim",
    "poster_url": "url",
    "...": "..."
  },
  "episodes": [
    {
      "name": "Tập 1",
      "link_embed": "url_iframe"
    }
  ]
}
```

---

## Xử lý lỗi

### Lỗi kết nối API
- Log được ghi vào `storage/logs/import_movies.log`
- Script sẽ tiếp tục nếu một phim lỗi

### Lỗi Database
- Kiểm tra kết nối database
- Nếu slug trùng, phim sẽ bị bỏ qua
- Nếu lỗi insert, log sẽ ghi chi tiết

### Timeout
- Tăng `self::REQUEST_TIMEOUT` trong MovieApiService.php
- Mặc định: 30 giây

---

## Tránh trùng phim

Script tự động kiểm tra slug:
- Trước khi insert phim mới, gọi `Movie::checkSlugExists($slug)`
- Nếu slug đã tồn tại, phim sẽ bị bỏ qua
- Tốc độ lấy dữ liệu API nhanh hơn nếu bỏ qua phim cũ

---

## Tăng lượt xem

### Khi xem phim:
1. MovieController::watch() được gọi
2. Tăng views cho phim: `Movie::incrementViews($movieId)`
3. Tăng views cho tập: `Episode::incrementViews($episodeId)`

### Schema cần update:
```sql
-- Nếu chưa có cột views_count
ALTER TABLE `movies` ADD `views_count` INT DEFAULT 0;
ALTER TABLE `episodes` ADD `views_count` INT DEFAULT 0;

-- Hoặc có sẵn thì không cần
```

---

## Video Player

View watch.blade.php sử dụng HTML5 `<video>` tag:

```html
<video id="movie-player" controls>
    <source src="{{ $videoUrl }}" type="video/mp4">
    Video player
</video>
```

### Hỗ trợ format:
- MP4 (.mp4)
- HLS (.m3u8)
- Embed iframe (auto-play từ link_embed của API)

---

## Ví dụ sử dụng trong code

### Lấy danh sách phim:
```php
<?php
use App\Services\MovieApiService;

$apiService = new MovieApiService();
$movies = $apiService->getLatestMovies(1);

foreach ($movies as $movie) {
    echo $movie['name'];
}
?>
```

### Lấy chi tiết phim:
```php
<?php
use App\Services\MovieApiService;

$apiService = new MovieApiService();
$detail = $apiService->getMovieDetail('ten-phim');

echo $detail['movie']['name'];
echo count($detail['episodes']) . ' tập';
?>
```

### Thêm phim vào database:
```php
<?php
use App\Models\Movie;
use App\Services\MovieApiService;

$movie = new Movie();
$apiService = new MovieApiService();

// Kiểm tra slug
if (!$movie->checkSlugExists('ten-phim')) {
    // Chuẩn hóa dữ liệu
    $data = $apiService->formatMovieForDatabase($apiData);
    
    // Lưu phim
    $movieId = $movie->createMovie($data);
    
    // Lấy chi tiết
    $detail = $apiService->getMovieDetail('ten-phim');
    
    // Lưu tập
    $episodes = $apiService->formatEpisodesForDatabase(
        $detail['episodes'],
        $movieId
    );
    
    $movie->addEpisodes($movieId, $episodes);
}
?>
```

---

## Troubleshooting

### 1. API không phản hồi
- Kiểm tra kết nối internet
- Kiểm tra tường lửa
- Thử gọi API trực tiếp: `curl https://ophim1.com/danh-sach/phim-moi-cap-nhat`

### 2. Phim không lưu vào database
- Kiểm tra kết nối database
- Kiểm tra quyền INSERT trên bảng `movies`, `episodes`
- Xem log: `storage/logs/import_movies.log`
- Xem log PHP: `storage/logs/php-errors.log`

### 3. Cron job không chạy
- Kiểm tra crontab: `crontab -l`
- Chạy test thủ công: `php cron/import_movies.php`
- Kiểm tra quyền file: `chmod 755 cron/import_movies.php`
- Kiểm tra log cron: `grep CRON /var/log/syslog`

### 4. Video không phát
- Kiểm tra URL tron `episodes.video_url`
- Thử truy cập URL trực tiếp trong trình duyệt
- Kiểm tra CORS headers của API

### 5. Hiệu suất chậm
- Tăng giới hạn timeout: `set_time_limit(300)`
- Import từng trang một: `getLatestMovies($page)`
- Optimize database: thêm index trên `slug`

---

## Cấu hình tùy chỉnh

### Thay đổi API base URL
File: `app/Services/MovieApiService.php`
```php
private const API_BASE_URL = 'https://ophim1.com'; // Thay đổi đây
```

### Thay đổi timeout
File: `app/Services/MovieApiService.php`
```php
private const REQUEST_TIMEOUT = 30; // Giây, thay đổi đây
```

### Thay đổi số phim lấy mỗi lần
File: `cron/import_movies.php`
- Mặc định lấy trang 1
- Có thể thay đổi để lấy nhiều trang

---

## Bảo mật

### Prepared Statements
Tất cả các query sử dụng prepared statements để tránh SQL injection:
```php
$qb->setParameter('slug', $slug); // An toàn
```

### Validate dữ liệu
```php
if (empty($slug)) {
    // Bỏ qua phim không có slug
    continue;
}
```

### Error Logging
Lỗi được ghi vào log thay vì hiển thị trên màn hình:
```php
error_log('Error: ' . $e->getMessage());
```

---

## Giấy phép

MIT License - Sử dụng miễn phí

## Support

Nếu gặp vấn đề:
1. Kiểm tra thư mục `storage/logs/` để xem log chi tiết
2. Chạy script thủ công để debug
3. Kiểm tra cấu trúc bảng database

---

**Tác giả:** Senior PHP Developer
**Ngày cập nhật:** 2024
