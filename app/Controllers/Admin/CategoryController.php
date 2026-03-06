<?php

namespace App\Controllers\Admin;

use App\Model;
use App\Controller as BaseController;
use Rakit\Validation\Validator;

/**
 * Quản lý thể loại (categories) trong trang admin:
 * - Danh sách, thêm, sửa, xóa thể loại
 */
class CategoryController extends Model
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

        $categories = [];
        $total = 0;

        try {
            $qb = $this->connection->createQueryBuilder();
            $qb->select('id', 'name', 'slug', 'description', 'created_at')
                ->from('categories')
                ->orderBy('created_at', 'DESC')
                ->setFirstResult($offset)
                ->setMaxResults($perPage);

            $categories = $qb->fetchAllAssociative();

            $total = (int)$this->connection->createQueryBuilder()
                ->select('COUNT(*)')
                ->from('categories')
                ->fetchOne();
        } catch (\Throwable $e) {
            $this->controller->logError('Category index error: ' . $e->getMessage());
            setFlash('error', 'Lỗi khi tải danh sách thể loại');
        }

        return view('admin.categories.index', compact('categories', 'page', 'perPage', 'total'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store()
    {
        // Implementation for creating categories
        setFlash('error', 'Chức năng thêm thể loại đang được phát triển');
        redirect('/admin/the-loai');
    }

    public function edit($id)
    {
        $category = null;

        try {
            $qb = $this->connection->createQueryBuilder();
            $category = $qb->select('*')
                ->from('categories')
                ->where('id = :id')
                ->setParameter('id', $id)
                ->fetchAssociative();

            if (!$category) {
                redirect404('Thể loại không tồn tại');
            }
        } catch (\Throwable $e) {
            $this->controller->logError('Category edit error: ' . $e->getMessage());
            setFlash('error', 'Lỗi khi tải thể loại');
            redirect('/admin/the-loai');
        }

        return view('admin.categories.edit', compact('category'));
    }

    public function update($id)
    {
        // Implementation for updating categories
        setFlash('error', 'Chức năng sửa thể loại đang được phát triển');
        redirect('/admin/the-loai');
    }

    public function destroy($id)
    {
        // Implementation for deleting categories
        setFlash('error', 'Chức năng xóa thể loại đang được phát triển');
        redirect('/admin/the-loai');
    }
}
