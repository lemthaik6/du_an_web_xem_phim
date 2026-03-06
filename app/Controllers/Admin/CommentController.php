<?php

namespace App\Controllers\Admin;

use App\Model;
use App\Controller as BaseController;
use Rakit\Validation\Validator;

/**
 * Quản lý bình luận (comments) trong trang admin:
 * - Danh sách bình luận
 * - Duyệt / từ chối / xóa bình luận
 */
class CommentController extends Model
{
    protected BaseController $controller;

    public function __construct()
    {
        parent::__construct();
        $this->controller = new BaseController();
    }

    public function index()
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $comments = [];
        $total = 0;

        try {
            $qb = $this->connection->createQueryBuilder();
            $qb->select('c.id', 'c.content', 'c.movie_id', 'm.title as movie_title', 'c.user_id', 'u.name as user_name', 'c.is_approved', 'c.created_at')
                ->from('comments', 'c')
                ->leftJoin('c', 'movies', 'm', 'c.movie_id = m.id')
                ->leftJoin('c', 'users', 'u', 'c.user_id = u.id')
                ->orderBy('c.created_at', 'DESC')
                ->setFirstResult($offset)
                ->setMaxResults($perPage);

            $comments = $qb->fetchAllAssociative();

            $total = (int)$this->connection->createQueryBuilder()
                ->select('COUNT(*)')
                ->from('comments')
                ->fetchOne();
        } catch (\Throwable $e) {
            $this->controller->logError('Comment index error: ' . $e->getMessage());
            setFlash('error', 'Lỗi khi tải danh sách bình luận');
        }

        return view('admin.comments.index', compact('comments', 'page', 'perPage', 'total'));
    }

    public function approve($id)
    {
        // Implementation for approving comments
        setFlash('error', 'Chức năng duyệt bình luận đang được phát triển');
        redirect('/admin/binh-luan');
    }

    public function reject($id)
    {
        // Implementation for rejecting comments
        setFlash('error', 'Chức năng từ chối bình luận đang được phát triển');
        redirect('/admin/binh-luan');
    }

    public function destroy($id)
    {
        // Implementation for deleting comments
        setFlash('error', 'Chức năng xóa bình luận đang được phát triển');
        redirect('/admin/binh-luan');
    }
}
