<?php

namespace App\Controllers;

use App\Model;

/**
 * API toggle phim yêu thích cho user hiện tại.
 */
class FavoriteController extends Model
{
    public function toggle()
    {
        $user = auth_user();
        if (!$user) {
            json([
                'ok'    => false,
                'error' => 'Bạn cần đăng nhập để lưu phim yêu thích.',
            ], 401);
        }

        $movieId = (int)($_POST['movie_id'] ?? 0);
        if ($movieId <= 0) {
            json([
                'ok'    => false,
                'error' => 'Thiếu thông tin phim.',
            ], 422);
        }

        try {
            $qb = $this->connection->createQueryBuilder();
            $exists = $qb->select('id')
                ->from('favorites')
                ->where('user_id = :uid AND movie_id = :mid')
                ->setParameter('uid', $user['id'])
                ->setParameter('mid', $movieId)
                ->setMaxResults(1)
                ->fetchAssociative();

            if ($exists) {
                // Bỏ yêu thích
                $qb = $this->connection->createQueryBuilder();
                $qb->delete('favorites')
                    ->where('id = :id')
                    ->setParameter('id', $exists['id'])
                    ->executeQuery();

                json([
                    'ok'          => true,
                    'isFavorite'  => false,
                    'message'     => 'Đã xóa khỏi danh sách yêu thích.',
                ]);
            } else {
                // Thêm yêu thích
                $qb = $this->connection->createQueryBuilder();
                $qb->insert('favorites')
                    ->values([
                        'user_id'    => ':uid',
                        'movie_id'   => ':mid',
                        'created_at' => ':created_at',
                    ])
                    ->setParameter('uid', $user['id'])
                    ->setParameter('mid', $movieId)
                    ->setParameter('created_at', date('Y-m-d H:i:s'))
                    ->executeQuery();

                json([
                    'ok'          => true,
                    'isFavorite'  => true,
                    'message'     => 'Đã thêm vào danh sách yêu thích.',
                ]);
            }
        } catch (\Throwable $e) {
            json([
                'ok'    => false,
                'error' => 'Không thể cập nhật danh sách yêu thích.',
            ], 500);
        }
    }
}

