<?php

namespace App\Models;

use App\Model;

/**
 * Model thao tác với bảng movies và dữ liệu liên quan.
 */
class Movie extends Model
{
    /**
     * Lấy danh sách section cho trang chủ: phim mới, phim hot, đề xuất, theo thể loại.
     * Trả về mảng:
     * [
     *   ['title' => '...', 'slug' => 'new', 'movies' => [...]],
     *   ...
     * ]
     */
    public function getHomeSections(): array
    {
        $sections = [];

        // Phim mới cập nhật
        $sections[] = [
            'title'  => 'Phim mới cập nhật',
            'slug'   => 'new',
            'movies' => $this->getMovies(['order' => 'latest', 'limit' => 20]),
        ];

        // Phim hot (dựa theo lượt xem)
        $sections[] = [
            'title'  => 'Phim hot',
            'slug'   => 'hot',
            'movies' => $this->getMovies(['order' => 'popular', 'limit' => 20]),
        ];

        // Phim đề xuất (có thể là phim rating cao)
        $sections[] = [
            'title'  => 'Phim đề xuất',
            'slug'   => 'recommended',
            'movies' => $this->getMovies(['order' => 'top_rated', 'limit' => 20]),
        ];

        return $sections;
    }

    /**
     * Lấy danh sách phim với bộ lọc đơn giản (search sử dụng lại).
     */
    public function getMovies(array $filters = []): array
    {
        // If no database connection, return empty array
        if (!$this->connection) {
            return [];
        }

        try {
            $qb = $this->connection->createQueryBuilder();
            $qb->select('m.id', 'm.title', 'm.slug', 'm.poster_url', 'm.release_year AS year', 'm.country_id AS country', 'm.views_count')
                ->from('movies', 'm')
                ->where('m.is_published = 1');

            if (!empty($filters['q'])) {
                $qb->andWhere('m.title LIKE :q OR m.original_title LIKE :q')
                    ->setParameter('q', '%' . $filters['q'] . '%');
            }

            if (!empty($filters['year'])) {
                $qb->andWhere('m.release_year = :year')
                    ->setParameter('year', (int)$filters['year']);
            }

            if (!empty($filters['country'])) {
                $qb->andWhere('m.country_id = :country')
                    ->setParameter('country', $filters['country']);
            }

            if (!empty($filters['category_id'])) {
                // Nếu dùng quan hệ many-to-many có bảng trung gian movie_category
                $qb->innerJoin('m', 'movie_category', 'mc', 'mc.movie_id = m.id')
                    ->andWhere('mc.category_id = :cid')
                    ->setParameter('cid', (int)$filters['category_id']);
            }

            // Sắp xếp
            $order = $filters['order'] ?? 'latest';
            switch ($order) {
                case 'popular':
                    $qb->orderBy('m.views_count', 'DESC');
                    break;
                case 'top_rated':
                    $qb->orderBy('m.views_count', 'DESC'); // Fallback since rating_avg is not in movies table
                    break;
                default:
                    $qb->orderBy('m.updated_at', 'DESC');
            }

            if (!empty($filters['limit'])) {
                $qb->setMaxResults((int)$filters['limit']);
            }

            return $qb->fetchAllAssociative();
        } catch (\Throwable $e) {
            error_log('Error fetching movies: ' . $e->getMessage());
            return []; // Return empty array on error instead of throwing
        }
    }

    /**
     * Đếm tổng số phim phù hợp bộ lọc (dùng cho phân trang search).
     */
    public function countForSearch(array $filters = []): int
    {
        try {
            $qb = $this->connection->createQueryBuilder();
            $qb->select('COUNT(*)')
                ->from('movies', 'm')
                ->where('m.is_published = 1');

            if (!empty($filters['q'])) {
                $qb->andWhere('m.title LIKE :q OR m.original_title LIKE :q')
                    ->setParameter('q', '%' . $filters['q'] . '%');
            }
            if (!empty($filters['year'])) {
                $qb->andWhere('m.release_year = :year')
                    ->setParameter('year', (int)$filters['year']);
            }
            if (!empty($filters['country'])) {
                $qb->andWhere('m.country_id = :country')
                    ->setParameter('country', $filters['country']);
            }

            return (int)$qb->fetchOne();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Tìm phim theo slug thân thiện SEO.
     */
    public function findBySlug(string $slug): ?array
    {
        $qb = $this->connection->createQueryBuilder();
        $movie = $qb->select('*')
            ->from('movies')
            ->where('slug = :slug')
            ->setParameter('slug', $slug)
            ->setMaxResults(1)
            ->fetchAssociative();

        if (!$movie) {
            return null;
        }

        // Chuẩn hóa một số field để view sử dụng thuận tiện
        $movie['categories'] = $this->getCategoriesForMovie((int)$movie['id']);
        $movie['countries'] = isset($movie['country']) && $movie['country'] ? [$movie['country']] : [];

        // Nếu bảng rating tách riêng, có thể tổng hợp lại ở đây
        if (!isset($movie['rating_avg']) || !isset($movie['rating_count'])) {
            [$avg, $count] = $this->getRatingSummary((int)$movie['id']);
            $movie['rating_avg'] = $avg;
            $movie['rating_count'] = $count;
        }

        return $movie;
    }

    /**
     * Lấy danh sách thể loại của 1 phim (dùng bảng movie_category + categories).
     */
    public function getCategoriesForMovie(int $movieId): array
    {
        try {
            $qb = $this->connection->createQueryBuilder();
            $rows = $qb->select('c.name')
                ->from('movie_category', 'mc')
                ->innerJoin('mc', 'categories', 'c', 'c.id = mc.category_id')
                ->where('mc.movie_id = :id')
                ->setParameter('id', $movieId)
                ->fetchAllAssociative();

            return array_column($rows, 'name');
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Tóm tắt rating nếu được lưu trong bảng ratings riêng.
     */
    public function getRatingSummary(int $movieId): array
    {
        try {
            $qb = $this->connection->createQueryBuilder();
            $row = $qb->select('COALESCE(AVG(rating),0) AS avg_rating', 'COUNT(*) AS count_rating')
                ->from('ratings')
                ->where('movie_id = :mid')
                ->setParameter('mid', $movieId)
                ->fetchAssociative();

            return [
                round((float)($row['avg_rating'] ?? 0), 1),
                (int)($row['count_rating'] ?? 0),
            ];
        } catch (\Throwable $e) {
            return [0.0, 0];
        }
    }

    /**
     * Lấy danh sách tập phim cho 1 phim.
     */
    public function getEpisodes(int $movieId): array
    {
        $qb = $this->connection->createQueryBuilder();
        return $qb->select('e.id', 'e.movie_id', 'e.episode_number', 'e.title', 'evs.video_url')
            ->from('episodes', 'e')
            ->leftJoin('e', 'episode_video_sources', 'evs', 'e.id = evs.episode_id')
            ->where('e.movie_id = :mid')
            ->setParameter('mid', $movieId)
            ->orderBy('e.episode_number', 'ASC')
            ->fetchAllAssociative();
    }

    /**
     * Lấy 1 tập phim cụ thể theo movie + số tập.
     */
    public function getEpisodeByNumber(int $movieId, int $episodeNumber): ?array
    {
        $qb = $this->connection->createQueryBuilder();
        $ep = $qb->select('e.id', 'e.movie_id', 'e.episode_number', 'e.title', 'e.duration_seconds', 'evs.video_url', 'vs.name as server_name')
            ->from('episodes', 'e')
            ->leftJoin('e', 'episode_video_sources', 'evs', 'e.id = evs.episode_id')
            ->leftJoin('evs', 'video_servers', 'vs', 'vs.id = evs.video_server_id')
            ->where('e.movie_id = :mid AND e.episode_number = :ep')
            ->setParameter('mid', $movieId)
            ->setParameter('ep', $episodeNumber)
            ->setMaxResults(1)
            ->fetchAssociative();

        return $ep ?: null;
    }

    /**
     * Tăng lượt xem cho phim.
     */
    public function incrementViews(int $movieId): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->update('movies')
            ->set('views_count', 'views_count + 1')
            ->where('id = :id')
            ->setParameter('id', $movieId)
            ->executeQuery();
    }

    /**
     * Lấy danh sách phim liên quan (cùng thể loại, loại trừ chính nó).
     */
    public function getRelatedMovies(int $movieId, int $limit = 10): array
    {
        try {
            $qb = $this->connection->createQueryBuilder();
            $qb->select('DISTINCT m.id', 'm.title', 'm.slug', 'm.poster_url')
                ->from('movies', 'm')
                ->innerJoin('m', 'movie_category', 'mc', 'mc.movie_id = m.id')
                ->where('mc.category_id IN (
                    SELECT mc2.category_id FROM movie_category mc2 WHERE mc2.movie_id = :mid
                )')
                ->andWhere('m.id <> :mid')
                ->setParameter('mid', $movieId)
                ->orderBy('m.views_count', 'DESC')
                ->setMaxResults($limit);

            return $qb->fetchAllAssociative();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Kiểm tra slug phim đã tồn tại trong database hay chưa
     * Dùng để tránh trùng phim khi import từ API
     */
    public function checkSlugExists(string $slug): bool
    {
        try {
            $qb = $this->connection->createQueryBuilder();
            $result = $qb->select('COUNT(*)')
                ->from('movies')
                ->where('slug = :slug')
                ->setParameter('slug', $slug)
                ->fetchOne();

            return (int)$result > 0;
        } catch (\Throwable $e) {
            error_log('Error checking slug: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Tạo phim mới từ dữ liệu API
     * Sử dụng prepared statements để tránh SQL injection
     * 
     * @param array $data Dữ liệu phim
     * @return int|null ID phim được tạo hoặc null nếu lỗi
     */
    public function createMovie(array $data): ?int
    {
        try {
            // Map dữ liệu từ API sang tên columns trong database
            $columns = [
                'title'       => $data['name'] ?? '',
                'slug'        => $data['slug'] ?? '',
                'description' => $data['description'] ?? '',
                'poster_url'  => $data['poster'] ?? $data['poster_url'] ?? '',
                'release_year' => (int)($data['year'] ?? date('Y')),
                'is_published' => 1,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ];
            
            // Thêm optional fields
            if (!empty($data['original_title'])) {
                $columns['original_title'] = $data['original_title'];
            }
            
            if (!empty($data['duration'])) {
                $columns['duration_minutes'] = (int)$data['duration'];
            }

            // Build query dinamically
            $columnNames = array_keys($columns);
            $columnList = '`' . implode('`, `', $columnNames) . '`';
            $placeholders = ':' . implode(', :', $columnNames);
            
            $sql = "INSERT INTO movies ($columnList) VALUES ($placeholders)";
            
            $stmt = $this->connection->prepare($sql);
            
            foreach ($columns as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            
            $stmt->executeQuery();

            // Lấy ID của phim vừa tạo
            $movieId = (int)$this->connection->lastInsertId();
            
            // Lưu category nếu có (vào bảng trung gian movie_category)
            if (!empty($data['category']) && is_array($data['category'])) {
                $this->attachCategories($movieId, $data['category']);
            }
            
            return $movieId;
        } catch (\Throwable $e) {
            error_log('Error creating movie: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Gắn categories vào phim
     */
    private function attachCategories(int $movieId, array $categories): void
    {
        try {
            foreach ($categories as $categoryName) {
                // Tìm category theo name
                $cat = $this->connection->fetchAssociative(
                    'SELECT id FROM categories WHERE name = ? LIMIT 1',
                    [$categoryName]
                );
                
                if ($cat) {
                    // Thêm vào bảng trung gian
                    $this->connection->executeQuery(
                        'INSERT INTO movie_category (movie_id, category_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE movie_id = movie_id',
                        [$movieId, $cat['id']]
                    );
                } else {
                    // Tạo category mới nếu chưa tồn tại
                    // Cần cung cấp slug vì nó là bắt buộc
                    $slug = $this->generateSlug($categoryName);
                    
                    $this->connection->executeQuery(
                        'INSERT INTO categories (name, slug, created_at, updated_at) VALUES (?, ?, NOW(), NOW())',
                        [$categoryName, $slug]
                    );
                    
                    $categoryId = $this->connection->lastInsertId();
                    $this->connection->executeQuery(
                        'INSERT INTO movie_category (movie_id, category_id) VALUES (?, ?)',
                        [$movieId, $categoryId]
                    );
                }
            }
        } catch (\Throwable $e) {
            error_log('Error attaching categories: ' . $e->getMessage());
        }
    }
    
    /**
     * Tạo slug từ chuỗi
     */
    private function generateSlug(string $text): string
    {
        // Chuyển thành lowercase
        $text = strtolower($text);
        // Thay thế các ký tự đặc biệt bằng dấu gạch ngang
        $text = preg_replace('[^a-z0-9-]', '-', $text);
        // Loại bỏ các dấu gạch ngang liên tiếp
        $text = preg_replace('[-]{2,}', '-', $text);
        // Loại bỏ dấu gạch ngang ở đầu và cuối
        $text = trim($text, '-');
        
        return $text;
    }

    /**
     * Thêm danh sách tập phim vào database
     * 
     * @param int $movieId ID phim
     * @param array $episodes Danh sách tập phim
     * @return bool True nếu thành công
     */
    public function addEpisodes(int $movieId, array $episodes): bool
    {
        try {
            // Nếu episodes là array của array (multiple servers), lấy server đầu tiên
            if (!empty($episodes) && is_array($episodes[0]) && is_array($episodes[0][0] ?? null)) {
                $episodes = $episodes[0]; // Lấy server đầu tiên
            }

            // Đảm bảo có server video mặc định
            $serverId = $this->getOrCreateDefaultVideoServer();
            error_log("Video server ID: $serverId");
           
            foreach ($episodes as $index => $episode) {
                if (!is_array($episode)) {
                    continue;
                }
                
                $episodeTitle = $episode['name'] ?? $episode['episode_name'] ?? 'Tập ' . ($index + 1);
                $videoUrl = $episode['link_embed'] ?? $episode['link_m3u8'] ?? $episode['video_url'] ?? '';
                
                error_log("Processing episode: $episodeTitle, Video URL: $videoUrl");
                
                // Bước 1: Thêm vào bảng episodes
                $sql = "INSERT INTO episodes (movie_id,  episode_number, title, duration_seconds, created_at, updated_at) 
                        VALUES (?, ?, ?, 0, NOW(), NOW())";
                        
                $result = $this->connection->executeStatement($sql, [
                    $movieId,
                    $index + 1,
                    $episodeTitle
                ]);
                
                // Lấy ID của episode vừa thêm
                $episodeId = $this->connection->lastInsertId();
                error_log("Inserted episode, ID: $episodeId");
                
                // Bước 2: Thêm video source nếu có URL
                if (!empty($videoUrl) && $episodeId) {
                    $sql = "INSERT INTO episode_video_sources (episode_id, video_server_id, quality, video_url, is_active, created_at, updated_at) 
                            VALUES (?, ?, ?, ?, 1, NOW(), NOW())";
                    
                    error_log("Inserting video source for episode $episodeId");
                    $this->connection->executeStatement($sql, [
                        $episodeId,
                        $serverId,
                        '720p',
                        $videoUrl
                    ]);
                }
            }

            return true;
        } catch (\Throwable $e) {
            error_log('Error adding episodes: ' . $e->getMessage());
            error_log('Stack: ' . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Lấy hoặc tạo server video mặc định
     */
    private function getOrCreateDefaultVideoServer(): int
    {
        try {
            // Kiểm tra server đã tồn tại
            $sql = "SELECT id FROM video_servers WHERE name = 'Default' LIMIT 1";
            $serverId = $this->connection->fetchOne($sql);
            
            if ($serverId) {
                return $serverId;
            }
            
            // Tạo server mặc định
            $sql = "INSERT INTO video_servers (name, priority, is_active, created_at, updated_at) 
                    VALUES ('Default', 1, 1, NOW(), NOW())";
            $this->connection->executeStatement($sql);
            
            return $this->connection->lastInsertId();
        } catch (\Throwable $e) {
            error_log('Error creating video server: ' . $e->getMessage());
            return 1; // Assume ID 1 exists or will be used
        }
    }

    /**
     * Cập nhật thông tin phim (dùng khi có update từ API)
     */
    public function updateMovie(int $movieId, array $data): bool
    {
        try {
            $qb = $this->connection->createQueryBuilder();
            $qb->update('movies')
                ->set('title', ':title')
                ->set('description', ':description')
                ->set('poster_url', ':poster')
                ->set('thumb_url', ':thumb')
                ->set('release_year', ':year')
                ->set('country', ':country')
                ->set('category', ':category')
                ->set('updated_at', ':updated_at')
                ->where('id = :id')
                ->setParameter('id', $movieId)
                ->setParameter('title', $data['name'] ?? '')
                ->setParameter('description', $data['description'] ?? '')
                ->setParameter('poster', $data['poster'] ?? '')
                ->setParameter('thumb', $data['thumb'] ?? '')
                ->setParameter('year', (int)($data['year'] ?? date('Y')))
                ->setParameter('country', $data['country'] ?? '')
                ->setParameter('category', $data['category'] ?? '')
                ->setParameter('updated_at', date('Y-m-d H:i:s'))
                ->executeQuery();

            return true;
        } catch (\Throwable $e) {
            error_log('Error updating movie: ' . $e->getMessage());
            return false;
        }
    }
}

