<?php

namespace App\Controllers;

use App\Model;
use Rakit\Validation\Validator;

/**
 * API bình luận cho phim:
 * - GET /api/phim/{id}/binh-luan
 * - POST /api/phim/{id}/binh-luan
 * - POST /api/binh-luan/{id}/tra-loi
 */
class CommentController extends Model
{
    /**
     * Lấy danh sách bình luận cho 1 phim (bao gồm trả lời cấp 1).
     */
    public function index(int $movieId)
    {
        try {
            $qb = $this->connection->createQueryBuilder();
            $rows = $qb->select(
                    'c.id',
                    'c.content',
                    'c.parent_id',
                    'c.created_at',
                    'u.name AS user_name'
                )
                ->from('comments', 'c')
                ->innerJoin('c', 'users', 'u', 'u.id = c.user_id')
                ->where('c.movie_id = :mid')
                ->andWhere('c.is_deleted = 0')
                ->setParameter('mid', $movieId)
                ->orderBy('c.created_at', 'ASC')
                ->fetchAllAssociative();

            // Gom thành cây đơn giản: parent + replies (1 cấp)
            $byId = [];
            foreach ($rows as $row) {
                $row['replies'] = [];
                $byId[$row['id']] = $row;
            }

            $root = [];
            foreach ($byId as $id => &$comment) {
                if ($comment['parent_id']) {
                    $parentId = $comment['parent_id'];
                    if (isset($byId[$parentId])) {
                        $byId[$parentId]['replies'][] = $comment;
                    }
                } else {
                    $root[] = $comment;
                }
            }

            json([
                'ok'       => true,
                'comments' => $root,
            ]);
        } catch (\Throwable $e) {
            json([
                'ok'    => false,
                'error' => 'Không thể tải bình luận.',
            ], 500);
        }
    }

    /**
     * Thêm bình luận mới cho phim.
     */
    public function store(int $movieId)
    {
        $user = auth_user();
        if (!$user) {
            json([
                'ok'    => false,
                'error' => 'Bạn cần đăng nhập để bình luận.',
            ], 401);
        }

        $validator = new Validator();
        $validation = $validator->make($_POST, [
            'content' => 'required|min:3',
        ]);
        $validation->validate();

        if ($validation->fails()) {
            $errors = $validation->errors()->firstOfAll();
            json([
                'ok'     => false,
                'error'  => reset($errors) ?: 'Nội dung không hợp lệ',
                'fields' => $errors,
            ], 422);
        }

        $content = trim($_POST['content']);

        try {
            $qb = $this->connection->createQueryBuilder();
            $qb->insert('comments')
                ->values([
                    'movie_id'   => ':mid',
                    'user_id'    => ':uid',
                    'content'    => ':content',
                    'parent_id'  => 'NULL',
                    'is_deleted' => '0',
                    'created_at' => ':created_at',
                ])
                ->setParameter('mid', $movieId)
                ->setParameter('uid', $user['id'])
                ->setParameter('content', $content)
                ->setParameter('created_at', date('Y-m-d H:i:s'))
                ->executeQuery();

            json([
                'ok'      => true,
                'message' => 'Đã gửi bình luận.',
            ]);
        } catch (\Throwable $e) {
            json([
                'ok'    => false,
                'error' => 'Không thể gửi bình luận.',
            ], 500);
        }
    }

    /**
     * Trả lời 1 bình luận (cấp 1).
     */
    public function reply(int $commentId)
    {
        $user = auth_user();
        if (!$user) {
            json([
                'ok'    => false,
                'error' => 'Bạn cần đăng nhập để bình luận.',
            ], 401);
        }

        $validator = new Validator();
        $validation = $validator->make($_POST, [
            'content' => 'required|min:3',
        ]);
        $validation->validate();

        if ($validation->fails()) {
            $errors = $validation->errors()->firstOfAll();
            json([
                'ok'     => false,
                'error'  => reset($errors) ?: 'Nội dung không hợp lệ',
                'fields' => $errors,
            ], 422);
        }

        try {
            // Lấy thông tin comment gốc để biết movie_id
            $qb = $this->connection->createQueryBuilder();
            $parent = $qb->select('movie_id')
                ->from('comments')
                ->where('id = :id')
                ->setParameter('id', $commentId)
                ->setMaxResults(1)
                ->fetchAssociative();

            if (!$parent) {
                json([
                    'ok'    => false,
                    'error' => 'Bình luận không tồn tại.',
                ], 404);
            }

            $content = trim($_POST['content']);

            $qb = $this->connection->createQueryBuilder();
            $qb->insert('comments')
                ->values([
                    'movie_id'   => ':mid',
                    'user_id'    => ':uid',
                    'content'    => ':content',
                    'parent_id'  => ':parent_id',
                    'is_deleted' => '0',
                    'created_at' => ':created_at',
                ])
                ->setParameter('mid', $parent['movie_id'])
                ->setParameter('uid', $user['id'])
                ->setParameter('content', $content)
                ->setParameter('parent_id', $commentId)
                ->setParameter('created_at', date('Y-m-d H:i:s'))
                ->executeQuery();

            json([
                'ok'      => true,
                'message' => 'Đã gửi trả lời.',
            ]);
        } catch (\Throwable $e) {
            json([
                'ok'    => false,
                'error' => 'Không thể gửi trả lời.',
            ], 500);
        }
    }
}

