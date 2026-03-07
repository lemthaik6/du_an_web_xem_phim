# ophim1.com API Reference

API phim miễn phí từ ophim1.com được sử dụng trong hệ thống này.

---

## **Base URL**

```
https://ophim1.com
```

---

## **Endpoints**

### **1. Danh sách phim mới cập nhật**

**Endpoint:** `GET /danh-sach/phim-moi-cap-nhat`

**Parameters:**
| Param | Type | Default | Description |
|-------|------|---------|-------------|
| page | int | 1 | Trang lấy dữ liệu (20 phim/trang) |

**Example:**
```bash
curl "https://ophim1.com/danh-sach/phim-moi-cap-nhat?page=1"
```

**Response:**
```json
{
  "status": true,
  "data": {
    "items": [
      {
        "id": 1,
        "name": "Avengers: Endgame",
        "slug": "avengers-endgame",
        "origin_name": "Avengers: Endgame",
        "poster_url": "https://..../poster.jpg",
        "thumb_url": "https://..../thumb.jpg",
        "publish_year": 2019,
        "country": [
          {
            "id": 1,
            "name": "Mỹ"
          }
        ],
        "category": [
          {
            "id": 1,
            "name": "Hành động"
          },
          {
            "id": 2,
            "name": "Viễn tưởng"
          }
        ],
        "content": "Sau khi vũ trụ bị Thanos phá hủy...",
        "is_copyright": false
      },
      // ... 19 phim khác
    ],
    "pagination": {
      "currentPage": 1,
      "totalPage": 50,
      "totalItem": 1000
    }
  }
}
```

**Field Description:**
| Field | Type | Description |
|-------|------|-------------|
| id | int | ID phim |
| name | string | Tên phim |
| slug | string | Slug SEO friendly |
| origin_name | string | Tên gốc |
| poster_url | string | URL poster |
| thumb_url | string | URL ảnh thumbnail |
| publish_year | int | Năm phát hành |
| country | array | Danh sách quốc gia |
| category | array | Danh sách thể loại |
| content | string | Mô tả phim |

---

### **2. Chi tiết phim**

**Endpoint:** `GET /phim/{slug}`

**Parameters:**
| Param | Type | Description |
|-------|------|-------------|
| slug | string | Slug của phim (required) |

**Example:**
```bash
curl "https://ophim1.com/phim/avengers-endgame"
```

**Response:**
```json
{
  "status": true,
  "movie": {
    "id": 1,
    "name": "Avengers: Endgame",
    "slug": "avengers-endgame",
    "origin_name": "Avengers: Endgame",
    "poster_url": "https://..../poster.jpg",
    "thumb_url": "https://..../thumb.jpg",
    "banner_url": "https://..../banner.jpg",
    "status": "completed",
    "publish_year": 2019,
    "time": "181 phút",
    "director": "Anthony Russo, Joe Russo",
    "casts": "Robert Downey Jr., Chris Evans,...",
    "content": "Sau khi vũ trụ bị Thanos phá hủy...",
    "country": [
      {"id": 1, "name": "Mỹ"}
    ],
    "category": [
      {"id": 1, "name": "Hành động"},
      {"id": 2, "name": "Viễn tưởng"}
    ],
    "is_copyright": false
  },
  "episodes": [
    [
      {
        "id": 1,
        "name": "Tập 1",
        "slug": "tap-1",
        "link_embed": "https://example.com/embed/1",
        "link_m3u8": "https://example.com/stream/1.m3u8"
      }
    ]
  ]
}
```

**Field Description:**
| Field | Type | Description |
|-------|------|-------------|
| status | string | Trạng thái: "completed", "ongoing" |
| time | string | Thời lượng |
| director | string | Đạo diễn |
| casts | string | Diễn viên |
| episodes | array | Danh sách tập (grouped by season) |

**Episode Field:**
| Field | Type | Description |
|-------|------|-------------|
| id | int | ID tập |
| name | string | Tên tập (Tập 1, Tập 2, ...) |
| slug | string | Slug tập |
| link_embed | string | Link iframe embed |
| link_m3u8 | string | Link stream M3U8 (HLS) |

---

### **3. Danh sách theo danh mục**

**Endpoint:** `GET /danh-sach/{category}`

**Available Categories:**
- `phim-le` - Phim lẻ
- `phim-bo` - Phim bộ
- `phim-hoat-hinh` - Phim hoạt hình
- `phim-chieu-rap` - Phim chiếu rạp

**Parameters:**
| Param | Type | Default | Description |
|-------|------|---------|-------------|
| page | int | 1 | Trang lấy dữ liệu |

**Example:**
```bash
curl "https://ophim1.com/danh-sach/phim-bo?page=1"
```

**Response:**
```json
{
  "status": true,
  "data": {
    "items": [ /* ... */ ]
  }
}
```

---

### **4. Tìm kiếm phim**

**Endpoint:** `GET /v1/api/search`

**Parameters:**
| Param | Type | Description |
|-------|------|-------------|
| keyword | string | Từ khóa tìm kiếm |

**Example:**
```bash
curl "https://ophim1.com/v1/api/search?keyword=avengers"
```

**Response:**
```json
{
  "status": true,
  "data": [
    {
      "id": 1,
      "name": "Avengers: Endgame",
      "slug": "avengers-endgame",
      "poster_url": "https://...",
      "thumb_url": "https://..."
    }
  ]
}
```

---

## **Response Codes**

| Code | Meaning |
|------|---------|
| 200 | OK - Request thành công |
| 404 | Not Found - Phim/trang không tồn tại |
| 500 | Server Error - Lỗi server |

---

## **Response Format**

Tất cả responses trả về JSON với structure:

```json
{
  "status": true/false,
  "data": { /* ... */ },
  "message": "error message if status is false"
}
```

---

## **Rate Limiting**

- Không có rate limit công khai
- Khuyến cáo: chờ ~500ms giữa các request
- Tránh gửi requests quá nhanh (có thể bị block)

---

## **Data Types**

### **Movie Object**
```php
[
    'id' => int,
    'name' => string,
    'slug' => string,
    'origin_name' => string,
    'poster_url' => string,
    'thumb_url' => string,
    'banner_url' => string,
    'publish_year' => int,
    'time' => string,
    'content' => string,
    'status' => string,  // 'completed' or 'ongoing'
    'country' => [
        [
            'id' => int,
            'name' => string
        ]
    ],
    'category' => [
        [
            'id' => int,
            'name' => string
        ]
    ]
]
```

### **Episode Object**
```php
[
    'id' => int,
    'name' => string,         // Tập 1, Tập 2...
    'slug' => string,
    'link_embed' => string,   // Iframe embed URL
    'link_m3u8' => string     // HLS stream URL (optional)
]
```

---

## **Usage in Application**

### **Using MovieApiService**

```php
<?php
use App\Services\MovieApiService;

$apiService = new MovieApiService();

// Get latest movies
$movies = $apiService->getLatestMovies(1);

// Get movie detail
$detail = $apiService->getMovieDetail('avengers-endgame');

// Format for database
$movieData = $apiService->formatMovieForDatabase($movies[0]);
$episodes = $apiService->formatEpisodesForDatabase(
    $detail['episodes'][0],
    1  // movie_id
);
?>
```

---

## **cURL Examples**

### **Get latest movies**
```bash
curl -X GET "https://ophim1.com/danh-sach/phim-moi-cap-nhat?page=1" \
  -H "Accept: application/json" \
  -H "User-Agent: Mozilla/5.0"
```

### **Get movie detail**
```bash
curl -X GET "https://ophim1.com/phim/avengers-endgame" \
  -H "Accept: application/json" \
  -H "User-Agent: Mozilla/5.0"
```

### **Get TV series**
```bash
curl -X GET "https://ophim1.com/danh-sach/phim-bo?page=1" \
  -H "Accept: application/json" \
  -H "User-Agent: Mozilla/5.0"
```

---

## **PHP Example**

```php
<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://ophim1.com/danh-sach/phim-moi-cap-nhat");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if ($data['status']) {
    foreach ($data['data']['items'] as $movie) {
        echo $movie['name'] . "\n";
    }
}
?>
```

---

## **Important Notes**

⚠️ **Disclaimer:**
- API này được cung cấp bởi ophim1.com
- Sử dụng cho mục đích học tập và phát triển ứng dụng cá nhân
- Tôn trọng quyền tác giả và luật pháp địa phương

📌 **Best Practices:**
1. Cache kết quả API để giảm calls
2. Implement retry logic cho failed requests
3. Ghi log tất cả errors
4. Không abuse API (rate limiting)
5. Thêm user-agent header

🔗 **Links:**
- ophim1.com: https://ophim1.com
- Phim xem online: https://ophim.watch (mirror)

---

**Last Updated:** 2024
