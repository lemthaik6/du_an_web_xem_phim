# 🚀 Hướng dẫn nhanh - Tích hợp API phim ophim1.com

**⏱️ Thời gian setup: ~5 phút**

---

## **Step 1: Chạy SQL Migration**

Mở phpMyAdmin hoặc MySQL client, chạy file SQL:

```bash
# File: database/migrations/001_setup_ophim_api.sql
# Copy nội dung file vào phpMyAdmin → Run
```

Hoặc dùng command line:
```bash
mysql -u root du_an_web_xem_phim < database/migrations/001_setup_ophim_api.sql
```

---

## **Step 2: Test API Connection**

Chạy script test:

```bash
cd c:\laragon\www\du_an_ca_nhan\du_an_web_xem_phim

php cron/test_import.php
```

✅ Nếu OK, sẽ hiển thị:
- ✓ Thành công
- ✓ Lấy được N phim
- ✓ Chi tiết phim

❌ Nếu lỗi:
- Kiểm tra internet connection
- Kiểm tra cURL support: `php -m | grep -i curl`

---

## **Step 3: Import Phim**

### **Cách 1: Import thủ công (lần đầu)**

```bash
php cron/import_movies.php
```

Sẽ in ra log như:
```
[2024-01-15 10:00:00] [info] ===== BẮT ĐẦU IMPORT PHIM =====
[2024-01-15 10:00:00] [info] Đang lấy danh sách phim mới từ API...
[2024-01-15 10:00:01] [info] Tìm thấy 20 phim từ API
[2024-01-15 10:00:01] [info] Đang thêm phim: Avengers Endgame
[2024-01-15 10:00:02] [info]   ✓ Thêm phim 'Avengers Endgame' thành công
...
[2024-01-15 10:00:30] [info] Phim thêm mới: 15
[2024-01-15 10:00:30] [info] Phim bỏ qua: 5
[2024-01-15 10:00:30] [info] Phim lỗi: 0
```

### **Cách 2: Setup Cron Job (tự động)**

**Trên Linux/Mac (sử dụng crontab):**

```bash
# Mở crontab
crontab -e

# Thêm dòng này (chạy mỗi 6 tiếng):
0 */6 * * * cd /path/to/du_an_web_xem_phim && php cron/import_movies.php >> storage/logs/cron.log 2>&1

# Lưu và thoát (Ctrl+X → Y → Enter)
```

**Trên Windows (Task Scheduler):**

1. Mở **Task Scheduler**
2. Click **Create Basic Task**
3. Name: "Import Movies"
4. Trigger: Repeat every `6 hours`
5. Action:
   - Program: `C:\xampp\php\php.exe` (hoặc php path của bạn)
   - Arguments: `C:\laragon\www\du_an_ca_nhan\du_an_web_xem_phim\cron\import_movies.php`
   - Start in: `C:\laragon\www\du_an_ca_nhan\du_an_web_xem_phim`

---

## **Step 4: Kiểm tra phim đã import**

```bash
# Xem log import
tail -f storage/logs/import_movies.log

# Hoặc truy cập website
http://localhost/du_an_ca_nhan/du_an_web_xem_phim/
```

Trang chủ sẽ hiển thị phim đã import.

---

## **Step 5: Test xem phim**

1. Click vào một phim từ danh sách
2. Click vào tập phim để xem
3. Video player sẽ load từ API

---

## **📁 Cấu trúc file quan trọng**

```
cron/
├── import_movies.php     ← Main import script
├── test_import.php       ← Test connection script
└──

app/
├── Services/
│   └── MovieApiService.php    ← API service
├── Models/
│   ├── Movie.php              ← Updated movie model
│   └── Episode.php            ← Updated episode model
└── Controllers/
    └── MovieController.php    ← Updated controller

views/
└── movie/
    ├── index.blade.php        ← Movie list view (NEW)
    ├── show.blade.php         ← Movie detail view
    └── watch.blade.php        ← Movie player view

storage/
└── logs/
    └── import_movies.log      ← Import log (auto generated)

database/
└── migrations/
    └── 001_setup_ophim_api.sql ← Database migration
```

---

## **⚙️ Cấu hình tùy chỉnh**

### **Thay đổi tần suất import**

File: `cron/import_movies.php`

```php
// Mặc định lấy trang 1 (20 phim)
$movies = $apiService->getLatestMovies(1);

// Để lấy nhiều trang:
// for ($page = 1; $page <= 3; $page++) {
//     $movies = $apiService->getLatestMovies($page);
//     // process...
// }
```

### **Thay đổi timeout API**

File: `app/Services/MovieApiService.php`

```php
private const REQUEST_TIMEOUT = 30;  // Tăng lên 60 nếu chậm
```

---

## **🔍 Troubleshooting**

### **Lỗi: "Không kết nối được API"**

```bash
# Test kết nối
curl https://ophim1.com/danh-sach/phim-moi-cap-nhat

# Hoặc kiểm tra PHP cURL
php -m | grep -i curl
```

### **Lỗi: "Database connection error"**

```bash
# Kiểm tra file .env
cat .env | grep DB_

# Test kết nối
mysql -u root -p du_an_web_xem_phim
```

### **Lỗi: "Slug already exists"**

Bình thường - phim này đã được import rồi. Script sẽ bỏ qua.

### **Không có phim hiển thị**

1. Chạy import: `php cron/import_movies.php`
2. Kiểm tra database: `SELECT COUNT(*) FROM movies;`
3. Xem log: `tail -f storage/logs/import_movies.log`

---

## **📊 Monitoring**

### **Xem số phim đã import**

```bash
mysql -u root du_an_web_xem_phim -e "SELECT COUNT(*) as total FROM movies;"
```

### **Xem số tập phim**

```bash
mysql -u root du_an_web_xem_phim -e "SELECT COUNT(*) as total FROM episodes;"
```

### **Xem lượt xem phim hot**

```bash
mysql -u root du_an_web_xem_phim -e "SELECT title, views_count FROM movies ORDER BY views_count DESC LIMIT 10;"
```

---

## **🎯 Ví dụ API**

### **Danh sách phim**
```
GET https://ophim1.com/danh-sach/phim-moi-cap-nhat
→ Trả về 20 phim mới nhất
```

### **Chi tiết phim**
```
GET https://ophim1.com/phim/avengers-endgame
→ Chi tiết + danh sách tập
```

---

## **✨ Features đã implement**

✅ Gọi API ophim1.com  
✅ Import phim tự động  
✅ Lưu tập phim  
✅ Tránh trùng phim (kiểm tra slug)  
✅ Xem chi tiết phim  
✅ Xem phim + tăng lượt xem  
✅ Danh sách phim với phân trang  
✅ Filter phim  
✅ Cron job auto update  
✅ Log chi tiết  

---

## **📝 Notes**

- ⚠️ Script là **non-blocking** - nếu 1 phim lỗi, script sẽ tiếp tục
- 💾 Tất cả lỗi được ghi vào `storage/logs/import_movies.log`
- 🔐 Dùng **Prepared Statements** - an toàn khỏi SQL injection
- 🚀 API rất nhanh - import 50 phim trong ~10 giây

---

## **📞 Support**

- 📚 Xem: `INTEGRATION_GUIDE.md` để chi tiết
- 🐛 Lỗi? Kiểm tra: `storage/logs/import_movies.log`
- 💬 Cần debug? Chạy: `php cron/test_import.php`

---

**Xong! 🎉 Website của bạn giờ tự động import phim.**

Truy cập: `http://localhost/du_an_ca_nhan/du_an_web_xem_phim`
