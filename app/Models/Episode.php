<?php

namespace App\Models;

use App\Model;

/**
 * Model thao tác với bảng episodes, phục vụ cả frontend và admin.
 */
class Episode extends Model
{
    public function listByMovie(int $movieId): array
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

    public function find(int $id): ?array
    {
        $qb = $this->connection->createQueryBuilder();
        $row = $qb->select('e.id', 'e.movie_id', 'e.episode_number', 'e.title', 'e.duration_seconds', 'evs.video_url')
            ->from('episodes', 'e')
            ->leftJoin('e', 'episode_video_sources', 'evs', 'e.id = evs.episode_id')
            ->where('e.id = :id')
            ->setParameter('id', $id)
            ->setMaxResults(1)
            ->fetchAssociative();

        return $row ?: null;
    }

    public function create(array $data): void
    {
        try {
            // Insert into episodes table (without video_url)
            $sql = "INSERT INTO episodes (movie_id, episode_number, title, duration_seconds, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, NOW(), NOW())";
            
            $this->connection->executeStatement($sql, [
                $data['movie_id'] ?? 0,
                $data['episode_number'] ?? 0,
                $data['title'] ?? '',
                $data['duration_seconds'] ?? 0,
            ]);
            
            // If video_url provided, insert into episode_video_sources
            if (!empty($data['video_url'])) {
                $episodeId = $this->connection->lastInsertId();
                $serverId = $this->getOrCreateDefaultVideoServer();
                
                $sql = "INSERT INTO episode_video_sources (episode_id, video_server_id, quality, video_url, is_active, created_at, updated_at) 
                        VALUES (?, ?, ?, ?, 1, NOW(), NOW())";
                
                $this->connection->executeStatement($sql, [
                    $episodeId,
                    $serverId,
                    '720p',
                    $data['video_url'],
                ]);
            }
        } catch (\Throwable $e) {
            error_log('Error creating episode: ' . $e->getMessage());
        }
    }
    
    /**
     * Get or create default video server
     */
    private function getOrCreateDefaultVideoServer(): int
    {
        try {
            $serverId = $this->connection->fetchOne("SELECT id FROM video_servers WHERE name = 'Default' LIMIT 1");
            
            if ($serverId) {
                return $serverId;
            }
            
            $this->connection->executeStatement(
                "INSERT INTO video_servers (name, priority, is_active, created_at, updated_at) VALUES (?, ?, 1, NOW(), NOW())",
                ['Default', 1]
            );
            
            return $this->connection->lastInsertId();
        } catch (\Throwable $e) {
            return 1;
        }
    }

    public function update(int $id, array $data): void
    {
        try {
            // Update episode info
            $this->connection->executeStatement(
                "UPDATE episodes SET episode_number = ?, title = ?, duration_seconds = ?, updated_at = NOW() WHERE id = ?",
                [
                    $data['episode_number'] ?? 0,
                    $data['title'] ?? '',
                    $data['duration_seconds'] ?? 0,
                    $id
                ]
            );
            
            // Update video URL if provided
            if (!empty($data['video_url'])) {
                $serverId = $this->getOrCreateDefaultVideoServer();
                
                // Try to update existing video source
                $existing = $this->connection->fetchOne(
                    "SELECT id FROM episode_video_sources WHERE episode_id = ? LIMIT 1",
                    [$id]
                );
                
                if ($existing) {
                    $this->connection->executeStatement(
                        "UPDATE episode_video_sources SET video_url = ?, updated_at = NOW() WHERE episode_id = ?",
                        [$data['video_url'], $id]
                    );
                } else {
                    // Insert new video source
                    $this->connection->executeStatement(
                        "INSERT INTO episode_video_sources (episode_id, video_server_id, quality, video_url, is_active, created_at, updated_at) 
                         VALUES (?, ?, ?, ?, 1, NOW(), NOW())",
                        [$id, $serverId, '720p', $data['video_url']]
                    );
                }
            }
        } catch (\Throwable $e) {
            error_log('Error updating episode: ' . $e->getMessage());
        }
    }

    public function delete(int $id): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->delete('episodes')
            ->where('id = :id')
            ->setParameter('id', $id)
            ->executeQuery();
    }

    /**
     * Tăng lượt xem cho tập phim
     */
    public function incrementViews(int $episodeId): void
    {
        try {
            $qb = $this->connection->createQueryBuilder();
            $qb->update('episodes')
                ->set('views_count', 'views_count + 1')
                ->where('id = :id')
                ->setParameter('id', $episodeId)
                ->executeQuery();
        } catch (\Throwable $e) {
            error_log('Error incrementing episode views: ' . $e->getMessage());
        }
    }

    /**
     * Lấy thông tin tập phim theo ID
     */
    public function getById(int $id): ?array
    {
        try {
            $qb = $this->connection->createQueryBuilder();
            return $qb->select('e.id', 'e.movie_id', 'e.episode_number', 'e.title', 'e.duration_seconds', 'evs.video_url')
                ->from('episodes', 'e')
                ->leftJoin('e', 'episode_video_sources', 'evs', 'e.id = evs.episode_id')
                ->where('e.id = :id')
                ->setParameter('id', $id)
                ->setMaxResults(1)
                ->fetchAssociative() ?: null;
        } catch (\Throwable $e) {
            error_log('Error fetching episode: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Kiểm tra xem một tập phim có tồn tại hay không
     */
    public function existsById(int $id): bool
    {
        try {
            $qb = $this->connection->createQueryBuilder();
            $count = $qb->select('COUNT(*)')
                ->from('episodes')
                ->where('id = :id')
                ->setParameter('id', $id)
                ->fetchOne();

            return (int)$count > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Xóa tất cả tập phim của một bộ phim (thường dùng khi cập nhật)
     */
    public function deleteByMovieId(int $movieId): bool
    {
        try {
            $qb = $this->connection->createQueryBuilder();
            $qb->delete('episodes')
                ->where('movie_id = :movie_id')
                ->setParameter('movie_id', $movieId)
                ->executeQuery();

            return true;
        } catch (\Throwable $e) {
            error_log('Error deleting episodes: ' . $e->getMessage());
            return false;
        }
    }
}

