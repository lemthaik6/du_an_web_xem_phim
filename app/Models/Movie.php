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
        $movie['countries'] = $movie['country'] ? [$movie['country']] : [];

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
        return $qb->select('id', 'movie_id', 'episode_number', 'title', 'video_url')
            ->from('episodes')
            ->where('movie_id = :mid')
            ->setParameter('mid', $movieId)
            ->orderBy('episode_number', 'ASC')
            ->fetchAllAssociative();
    }

    /**
     * Lấy 1 tập phim cụ thể theo movie + số tập.
     */
    public function getEpisodeByNumber(int $movieId, int $episodeNumber): ?array
    {
        $qb = $this->connection->createQueryBuilder();
        $ep = $qb->select('*')
            ->from('episodes')
            ->where('movie_id = :mid AND episode_number = :ep')
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
}

