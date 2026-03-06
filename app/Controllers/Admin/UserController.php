<?php

namespace App\Controllers\Admin;

use App\Model;
use App\Controller as BaseController;
use Rakit\Validation\Validator;

/**
 * Quản lý người dùng (users) trong trang admin:
 * - Danh sách, thêm, sửa, xóa người dùng
 * - Quản lý vai trò (role)
 */
class UserController extends Model
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

        $users = [];
        $total = 0;

        try {
            $qb = $this->connection->createQueryBuilder();
            $qb->select('id', 'name', 'email', 'role', 'created_at', 'updated_at')
                ->from('users')
                ->orderBy('created_at', 'DESC')
                ->setFirstResult($offset)
                ->setMaxResults($perPage);

            $users = $qb->fetchAllAssociative();

            $total = (int)$this->connection->createQueryBuilder()
                ->select('COUNT(*)')
                ->from('users')
                ->fetchOne();
        } catch (\Throwable $e) {
            $this->controller->logError('User index error: ' . $e->getMessage());
            setFlash('error', 'Lỗi khi tải danh sách người dùng');
        }

        return view('admin.users.index', compact('users', 'page', 'perPage', 'total'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store()
    {
        // Implementation for creating users
        setFlash('error', 'Chức năng thêm người dùng đang được phát triển');
        redirect('/admin/nguoi-dung');
    }

    public function edit($id)
    {
        $user = null;

        try {
            $qb = $this->connection->createQueryBuilder();
            $user = $qb->select('*')
                ->from('users')
                ->where('id = :id')
                ->setParameter('id', $id)
                ->fetchAssociative();

            if (!$user) {
                redirect404('Người dùng không tồn tại');
            }
        } catch (\Throwable $e) {
            $this->controller->logError('User edit error: ' . $e->getMessage());
            setFlash('error', 'Lỗi khi tải người dùng');
            redirect('/admin/nguoi-dung');
        }

        return view('admin.users.edit', compact('user'));
    }

    public function update($id)
    {
        // Implementation for updating users
        setFlash('error', 'Chức năng sửa người dùng đang được phát triển');
        redirect('/admin/nguoi-dung');
    }

    public function destroy($id)
    {
        // Prevent self-deletion
        $currentUser = auth();
        if ($currentUser && (int)$currentUser['id'] === (int)$id) {
            setFlash('error', 'Không thể xóa tài khoản của chính mình');
            redirect('/admin/nguoi-dung');
        }

        // Implementation for deleting users
        setFlash('error', 'Chức năng xóa người dùng đang được phát triển');
        redirect('/admin/nguoi-dung');
    }
}
