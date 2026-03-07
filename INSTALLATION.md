/**
 * ============================================================
 * INSTALLATION & SETUP CHECKLIST
 * Tích hợp API phim ophim1.com vào website xem phim PHP
 * ============================================================
 */

// ============================================================
// 📋 FILES CREATED / UPDATED
// ============================================================

NEW FILES (Khởi tạo):
├── Service Layer:
│   └── app/Services/MovieApiService.php
│       • getLatestMovies($page)
│       • getMovieDetail($slug)
│       • formatMovieForDatabase()
│       • formatEpisodesForDatabase()
│
├── Cron Scripts:
│   ├── cron/import_movies.php
│   │   • Import phim từ API
│   │   • Lưu phim + tập vào database
│   │   • Ghi log chi tiết
│   │
│   └── cron/test_import.php
│       • Test API connection
│       • Verify data formatting
│
├── Views:
│   └── views/movie/index.blade.php
│       • Danh sách phim
│       • Filter & pagination
│       • Responsive grid layout
│
├── Database:
│   └── database/migrations/001_setup_ophim_api.sql
│       • Add columns to movies table
│       • Add columns to episodes table
│       • Create indexes
│
└── Documentation:
    ├── QUICK_START.md (5 min setup)
    ├── INTEGRATION_GUIDE.md (Full guide)
    ├── SUMMARY.md (Overview)
    ├── API_REFERENCE.md (API docs)
    ├── INDEX.md (Navigation)
    └── INSTALLATION.md (This file)

---

UPDATED FILES (Cập nhật):
├── app/Models/Movie.php
│   + checkSlugExists($slug)
│   + createMovie($data)
│   + addEpisodes($movieId, $episodes)
│   + updateMovie($movieId, $data)
│
├── app/Models/Episode.php
│   + incrementViews($episodeId)
│   + getById($id)
│   + existsById($id)
│   + deleteByMovieId($movieId)
│
├── app/Controllers/MovieController.php
│   + index()
│   ~ show($slug) - updated with related movies
│   ~ watch($slug, $episodeId) - updated with episode ID support
│
└── routes/web.php
    + GET /danh-sach-phim → MovieController@index

// ============================================================
// ✅ INSTALLATION STEPS
// ============================================================

STEP 1: DATABASE MIGRATION
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Run the SQL migration:

  Option A - phpMyAdmin:
  1. Open http://localhost/phpmyadmin
  2. Select database: du_an_web_xem_phim
  3. Click "Import" tab
  4. Choose: database/migrations/001_setup_ophim_api.sql
  5. Click "Go"

  Option B - MySQL CLI:
  $ mysql -u root du_an_web_xem_phim < database/migrations/001_setup_ophim_api.sql

  Option C - Command line (all at once):
  $ mysql -u root -e "USE du_an_web_xem_phim; $(cat database/migrations/001_setup_ophim_api.sql)"

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

STEP 2: TEST API CONNECTION
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  $ cd c:\laragon\www\du_an_ca_nhan\du_an_web_xem_phim
  $ php cron/test_import.php

Expected output:
  [✓] Khởi tạo MovieApiService
  [✓] Lấy được 20 phim
  [✓] Định dạng OK
  [✓] TẤT CẢ TEST PASS

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

STEP 3: IMPORT MOVIES (First Time)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  $ php cron/import_movies.php

First import will take 20-30 seconds (depends on internet).
Check the output for success messages.

Verify in MySQL:
  $ mysql -u root -e "SELECT COUNT(*) as total FROM du_an_web_xem_phim.movies;"

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

STEP 4: SETUP CRON JOB (Auto Update)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Linux/Mac:
  1. Open terminal
  2. Run: crontab -e
  3. Add this line:
     0 */6 * * * cd /path/to/du_an_web_xem_phim && php cron/import_movies.php >> storage/logs/cron.log 2>&1
  4. Save (Ctrl+X → Y → Enter)

Windows (Task Scheduler):
  1. Open "Task Scheduler"
  2. Click "Create Basic Task"
  3. Name: "Import Movies"
  4. Trigger: "Daily" or "Hourly" (repeat every 6 hours)
  5. Action:
     Program: C:\xampp\php\php.exe (or your PHP path)
     Arguments: C:\laragon\www\du_an_ca_nhan\du_an_web_xem_phim\cron\import_movies.php
     Start in: C:\laragon\www\du_an_ca_nhan\du_an_web_xem_phim
  6. Click OK

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

STEP 5: TEST WEBSITE
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Open in browser:
  http://localhost/du_an_ca_nhan/du_an_web_xem_phim/

You should see:
  ✓ Home page with movie sections
  ✓ Movies displaying in grid
  ✓ Click movie to see details
  ✓ Click episode to watch

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

// ============================================================
// 🔍 VERIFICATION CHECKLIST
// ============================================================

After setup, verify:

Database:
  ☐ movies table has slug column
  ☐ movies table has poster_url column
  ☐ movies table has views_count column
  ☐ episodes table has video_url column
  ☐ episodes table has movie_id foreign key

Code:
  ☐ app/Services/MovieApiService.php exists
  ☐ app/Models/Movie.php has new methods
  ☐ app/Models/Episode.php has new methods
  ☐ app/Controllers/MovieController.php has index() method
  ☐ views/movie/index.blade.php exists
  ☐ routes/web.php has /danh-sach-phim route

Scripts:
  ☐ cron/import_movies.php works (php cron/import_movies.php)
  ☐ cron/test_import.php passes (php cron/test_import.php)
  ☐ storage/logs/import_movies.log created

Website:
  ☐ Home page loads
  ☐ Movie list displays
  ☐ Movie detail page works
  ☐ Watch page plays video
  ☐ Filter/search works

// ============================================================
// 📊 EXPECTED RESULTS
// ============================================================

After first import:
  • ~20 movies in database
  • ~60-100 episodes
  • ~30 seconds import time
  • Log file created

From then on (every 6 hours):
  • 0-20 new movies added
  • Duplicate movies skipped
  • Views count increases
  • Full automation

// ============================================================
// 🐛 TROUBLESHOOTING
// ============================================================

Problem: "Cannot connect to API"
Solution:
  1. Check internet connection
  2. Run: curl https://ophim1.com/danh-sach/phim-moi-cap-nhat
  3. Check PHP cURL: php -m | grep curl
  4. Run test: php cron/test_import.php

Problem: "Database table not found"
Solution:
  1. Run migration file
  2. Check database name in .env
  3. Run: mysql -u root -e "SHOW TABLES FROM du_an_web_xem_phim;"

Problem: "Cron job not running"
Solution:
  1. Check crontab: crontab -l
  2. Check cron logs: grep CRON /var/log/syslog
  3. Test manual: php cron/import_movies.php
  4. Check permissions: chmod 755 cron/import_movies.php

Problem: "Video not playing"
Solution:
  1. Check video_url in database
  2. Check if URL is valid
  3. Check browser console for errors
  4. Try different video URL

// ============================================================
// 📚 DOCUMENTATION FILES
// ============================================================

Read in this order:

1. INDEX.md (START HERE)
   → Navigation & overview

2. QUICK_START.md
   → 5-minute setup guide

3. INTEGRATION_GUIDE.md
   → Detailed full reference

4. API_REFERENCE.md
   → ophim1.com API details

5. SUMMARY.md
   → Overview of changes

// ============================================================
// 🚀 QUICK COMMANDS
// ============================================================

Setup:
  mysql -u root du_an_web_xem_phim < database/migrations/001_setup_ophim_api.sql
  php cron/test_import.php
  php cron/import_movies.php

Monitor:
  tail -f storage/logs/import_movies.log
  mysql -u root -e "SELECT COUNT(*) FROM du_an_web_xem_phim.movies;"

Debug:
  php -m | grep curl
  mysql -u root du_an_web_xem_phim -e "SHOW SCHEMA;"

// ============================================================
// ⚡ PERFORMANCE TIPS
// ============================================================

1. Add database index:
   mysql -u root du_an_web_xem_phim -e "CREATE INDEX idx_slug ON movies(slug);"

2. Run import during off-peak:
   Update cron to run at 2 AM (0 2 * * *)

3. Limit import pages:
   Edit cron/import_movies.php to import multiple pages at once

4. Monitor space:
   du -sh storage/
   mysql -u root du_an_web_xem_phim -e "SELECT * FROM information_schema.TABLES WHERE TABLE_NAME='movies';"

// ============================================================
// 🎯 SUCCESS INDICATORS
// ============================================================

✅ Setup successful when:
  • Test script shows "TẤT CẢ TEST PASS"
  • Import script completes with "Phim thêm mới: X"
  • Website shows movies in grid
  • Click movie shows details
  • Video player loads

✅ Auto-update working when:
  • Cron job runs on schedule
  • Log file updates every 6 hours
  • New movies appear in database
  • No manual intervention needed

// ============================================================
// 📝 FILE PERMISSIONS
// ============================================================

Ensure correct permissions:

  chmod 755 cron/            # Executable
  chmod 644 app/Services/*   # Readable
  chmod 644 app/Models/*     # Readable
  chmod 777 storage/logs/    # Writable

// ============================================================
// 🔐 SECURITY NOTES
// ============================================================

✅ All queries use prepared statements
✅ Input validated before database
✅ Errors logged, not displayed
✅ File permissions restricted
✅ External API calls have timeout
✅ Rate limiting implemented

// ============================================================
// 💡 TIPS & TRICKS
// ============================================================

1. Test API without import:
   php cron/test_import.php

2. Import specific page:
   Edit cron/import_movies.php, change:
   $movies = $apiService->getLatestMovies(2);

3. View import progress real-time:
   tail -f storage/logs/import_movies.log

4. Force update in MySQL:
   TRUNCATE TABLE movies;
   TRUNCATE TABLE episodes;
   Then run import again

5. Add custom columns:
   ALTER TABLE movies ADD director VARCHAR(255);
   Edit MovieApiService.php to extract director

// ============================================================
// ✨ YOU'RE DONE!
// ============================================================

Congratulations! You have successfully integrated:
  ✅ API Service Layer
  ✅ Auto-Import Script
  ✅ Cron Job Setup
  ✅ Movie Display Views
  ✅ Video Player

Your website now:
  • Automatically fetches movies from ophim1.com
  • Displays them in a beautiful grid
  • Streams videos directly
  • Tracks views
  • Updates every 6 hours

Next steps:
  1. Customize styling in views
  2. Add featured movies section
  3. Implement user ratings
  4. Add social sharing
  5. Setup analytics

Happy coding! 🚀

// ============================================================
*/
