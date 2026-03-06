<?php

namespace App\Controllers\Admin;

use App\Model;
use App\Controller as BaseController;
use Rakit\Validation\Validator;

/**
 * Quản lý tập phim (episodes) trong trang admin:
 * - Danh sách, thêm, sửa, xóa tập phim
 */
class EpisodeController extends Model
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

        $episodes = [];
        $total = 0;

        try {
            $qb = $this->connection->createQueryBuilder();
            $qb->select('e.id', 'e.episode_number', 'e.title', 'e.movie_id', 'm.title as movie_title', 'e.video_url', 'e.created_at')
                ->from('episodes', 'e')
                ->leftJoin('e', 'movies', 'm', 'e.movie_id = m.id')
                ->orderBy('e.movie_id', 'DESC')
                ->addOrderBy('e.episode_number', 'ASC')
                ->setFirstResult($offset)
                ->setMaxResults($perPage);

            $episodes = $qb->fetchAllAssociative();

            $total = (int)$this->connection->createQueryBuilder()
                ->select('COUNT(*)')
                ->from('episodes')
                ->fetchOne();
        } catch (\Throwable $e) {
            $this->controller->logError('Episode index error: ' . $e->getMessage());
            setFlash('error', 'Lỗi khi tải danh sách tập phim');
        }

        return view('admin.episodes.index', compact('episodes', 'page', 'perPage', 'total'));
    }

    public function create()
    {
        $movies = [];
        try {
            $qb = $this->connection->createQueryBuilder();
            $movies = $qb->select('id', 'title')
                ->from('movies')
                ->fetchAllAssociative();
        } catch (\Throwable $e) {
            $this->controller->logError('Failed to fetch movies: ' . $e->getMessage());
        }

        return view('admin.episodes.create', compact('movies'));
    }

    public function store()
    {
        // Implementation for creating episodes
        setFlash('error', 'Chức năng thêm tập phim đang được phát triển');
        redirect('/admin/tap-phim');
    }

    public function edit($id)
    {
        $movie = null;
        $movies = [];

        try {
            $qb = $this->connection->createQueryBuilder();
            $movie = $qb->select('*')
                ->from('episodes')
                ->where('id = :id')
                ->setParameter('id', $id)
                ->fetchAssociative();

            if (!$movie) {
                redirect404('Tập phim không tồn tại');
            }

            $qb = $this->connection->createQueryBuilder();
            $movies = $qb->select('id', 'title')
                ->from('movies')
                ->fetchAllAssociative();
        } catch (\Throwable $e) {
            $this->controller->logError('Episode edit error: ' . $e->getMessage());
            setFlash('error', 'Lỗi khi tải tập phim');
            redirect('/admin/tap-phim');
        }

        return view('admin.episodes.edit', compact('movie', 'movies'));
    }

    public function update($id)
    {
        // Implementation for updating episodes
        setFlash('error', 'Chức năng sửa tập phim đang được phát triển');
        redirect('/admin/tap-phim');
    }

    public function destroy($id)
    {
        // Implementation for deleting episodes
        setFlash('error', 'Chức năng xóa tập phim đang được phát triển');
        redirect('/admin/tap-phim');
    }
}
