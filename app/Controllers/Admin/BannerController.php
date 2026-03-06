<?php

namespace App\Controllers\Admin;

use App\Model;
use App\Controller as BaseController;
use Rakit\Validation\Validator;

/**
 * Quản lý banner trong trang admin:
 * - Danh sách banner
 * - Thêm, sửa, xóa banner cho trang chủ (slider, ads, v.v.)
 */
class BannerController extends Model
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

        $banners = [];
        $total = 0;

        try {
            $qb = $this->connection->createQueryBuilder();
            $qb->select('id', 'title', 'image_url', 'link', 'position', 'is_active', 'created_at')
                ->from('banners')
                ->orderBy('position', 'ASC')
                ->addOrderBy('created_at', 'DESC')
                ->setFirstResult($offset)
                ->setMaxResults($perPage);

            $banners = $qb->fetchAllAssociative();

            $total = (int)$this->connection->createQueryBuilder()
                ->select('COUNT(*)')
                ->from('banners')
                ->fetchOne();
        } catch (\Throwable $e) {
            $this->controller->logError('Banner index error: ' . $e->getMessage());
            setFlash('error', 'Lỗi khi tải danh sách banner');
        }

        return view('admin.banners.index', compact('banners', 'page', 'perPage', 'total'));
    }

    public function create()
    {
        return view('admin.banners.create');
    }

    public function store()
    {
        // Implementation for creating banners
        setFlash('error', 'Chức năng thêm banner đang được phát triển');
        redirect('/admin/banner');
    }

    public function edit($id)
    {
        $banner = null;

        try {
            $qb = $this->connection->createQueryBuilder();
            $banner = $qb->select('*')
                ->from('banners')
                ->where('id = :id')
                ->setParameter('id', $id)
                ->fetchAssociative();

            if (!$banner) {
                redirect404('Banner không tồn tại');
            }
        } catch (\Throwable $e) {
            $this->controller->logError('Banner edit error: ' . $e->getMessage());
            setFlash('error', 'Lỗi khi tải banner');
            redirect('/admin/banner');
        }

        return view('admin.banners.edit', compact('banner'));
    }

    public function update($id)
    {
        // Implementation for updating banners
        setFlash('error', 'Chức năng sửa banner đang được phát triển');
        redirect('/admin/banner');
    }

    public function destroy($id)
    {
        // Implementation for deleting banners
        setFlash('error', 'Chức năng xóa banner đang được phát triển');
        redirect('/admin/banner');
    }
}
