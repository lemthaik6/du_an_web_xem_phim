<?php

namespace App\Controllers;

use App\Model;

/**
 * API lưu / cập nhật lịch sử xem (tiến độ) cho user hiện tại.
 */
class WatchHistoryController extends Model
{
    /**
     * Upsert bản ghi lịch sử xem:
     * - movie_id
     * - episode_number
     * - progress (0-100)
     */
    public function upsert()
    {
        $user = auth_user();
        if (!$user) {
            json([
                'ok'    => false,
                'error' => 'Bạn cần đăng nhập để lưu lịch sử xem.',
            ], 401);
        }

        $movieId = (int)($_POST['movie_id'] ?? 0);
        $episodeNumber = (int)($_POST['episode_number'] ?? 0);
        $progress = min(100, max(0, (int)($_POST['progress'] ?? 0)));

        if ($movieId <= 0 || $episodeNumber <= 0) {
            json([
                'ok'    => false,
                'error' => 'Thiếu thông tin phim hoặc tập phim.',
            ], 422);
        }

        try {
            $qb = $this->connection->createQueryBuilder();
            $existing = $qb->select('id')
                ->from('views')
                ->where('user_id = :uid AND movie_id = :mid AND episode_number = :ep')
                ->setParameter('uid', $user['id'])
                ->setParameter('mid', $movieId)
                ->setParameter('ep', $episodeNumber)
                ->setMaxResults(1)
                ->fetchAssociative();

            if ($existing) {
                $qb = $this->connection->createQueryBuilder();
                $qb->update('views')
                    ->set('progress', ':progress')
                    ->set('updated_at', ':updated_at')
                    ->where('id = :id')
                    ->setParameter('progress', $progress)
                    ->setParameter('updated_at', date('Y-m-d H:i:s'))
                    ->setParameter('id', $existing['id'])
                    ->executeQuery();
            } else {
                $qb = $this->connection->createQueryBuilder();
                $qb->insert('views')
                    ->values([
                        'user_id'       => ':uid',
                        'movie_id'      => ':mid',
                        'episode_number'=> ':ep',
                        'progress'      => ':progress',
                        'updated_at'    => ':updated_at',
                    ])
                    ->setParameter('uid', $user['id'])
                    ->setParameter('mid', $movieId)
                    ->setParameter('ep', $episodeNumber)
                    ->setParameter('progress', $progress)
                    ->setParameter('updated_at', date('Y-m-d H:i:s'))
                    ->executeQuery();
            }

            json([
                'ok'      => true,
                'message' => 'Đã lưu tiến độ xem.',
            ]);
        } catch (\Throwable $e) {
            json([
                'ok'    => false,
                'error' => 'Không thể lưu lịch sử xem.',
            ], 500);
        }
    }
}

