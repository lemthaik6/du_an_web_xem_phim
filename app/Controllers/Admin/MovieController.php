<?php

namespace App\Controllers\Admin;

use App\Model;
use App\Controller as BaseController;
use App\Models\Category;
use Rakit\Validation\Validator;

/**
 * Quản lý phim trong trang admin:
 * - Danh sách, thêm, sửa, xóa
 * - Upload poster / banner / trailer
 */
class MovieController extends Model
{
    protected BaseController $uploader;

    public function __construct()
    {
        parent::__construct();
        $this->uploader = new BaseController();
    }

    public function index()
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $qb = $this->connection->createQueryBuilder();
        $qb->select('id', 'title', 'slug', 'year', 'country', 'is_published', 'views_count')
            ->from('movies')
            ->orderBy('created_at', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($perPage);

        $movies = $qb->fetchAllAssociative();

        $total = (int)$this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from('movies')
            ->fetchOne();

        return view('admin.movies.index', compact('movies', 'page', 'perPage', 'total'));
    }

    public function create()
    {
        $categories = [];
        try {
            $categories = (new Category())->all();
        } catch (\Throwable $e) {
            $categories = [];
        }

        return view('admin.movies.create', compact('categories'));
    }

    public function store()
    {
        $validator = new Validator();
        $validation = $validator->make($_POST, [
            'title'       => 'required|min:3',
            'slug'        => 'required|min:3',
            'year'        => 'integer',
            'country'     => 'max:100',
            'description' => 'required|min:10',
        ]);
        $validation->validate();

        if ($validation->fails()) {
            $errors = $validation->errors()->firstOfAll();
            setFlash('error', reset($errors) ?: 'Dữ liệu không hợp lệ');
            redirect('/admin/phim/them');
        }

        $title = trim($_POST['title']);
        $slug = trim($_POST['slug']);
        $year = (int)($_POST['year'] ?? 0);
        $country = trim($_POST['country'] ?? '');
        $description = trim($_POST['description']);
        $status = $_POST['status'] ?? 'ongoing';
        $isPublished = isset($_POST['is_published']) ? 1 : 0;

        $posterPath = null;
        $bannerPath = null;
        $trailerUrl = $_POST['trailer_url'] ?? null;

        try {
            if (is_upload('poster')) {
                $posterPath = $this->uploader->uploadFile($_FILES['poster'], 'posters');
            }
            if (is_upload('banner')) {
                $bannerPath = $this->uploader->uploadFile($_FILES['banner'], 'banners');
            }

            $qb = $this->connection->createQueryBuilder();
            $qb->insert('movies')
                ->values([
                    'title'       => ':title',
                    'slug'        => ':slug',
                    'year'        => ':year',
                    'country'     => ':country',
                    'description' => ':description',
                    'status'      => ':status',
                    'is_published'=> ':is_published',
                    'poster_url'  => ':poster',
                    'banner_url'  => ':banner',
                    'trailer_url' => ':trailer',
                    'created_at'  => ':created_at',
                    'updated_at'  => ':updated_at',
                ])
                ->setParameter('title', $title)
                ->setParameter('slug', $slug)
                ->setParameter('year', $year ?: null)
                ->setParameter('country', $country ?: null)
                ->setParameter('description', $description)
                ->setParameter('status', $status)
                ->setParameter('is_published', $isPublished)
                ->setParameter('poster', $posterPath)
                ->setParameter('banner', $bannerPath)
                ->setParameter('trailer', $trailerUrl)
                ->setParameter('created_at', date('Y-m-d H:i:s'))
                ->setParameter('updated_at', date('Y-m-d H:i:s'))
                ->executeQuery();

            $movieId = (int)$this->connection->lastInsertId();

            // Lưu thể loại vào bảng movie_category nếu tồn tại
            $categoryIds = $_POST['category_ids'] ?? [];
            if (!empty($categoryIds) && is_array($categoryIds)) {
                foreach ($categoryIds as $cid) {
                    try {
                        $qb = $this->connection->createQueryBuilder();
                        $qb->insert('movie_category')
                            ->values([
                                'movie_id'    => ':mid',
                                'category_id' => ':cid',
                            ])
                            ->setParameter('mid', $movieId)
                            ->setParameter('cid', (int)$cid)
                            ->executeQuery();
                    } catch (\Throwable $e) {
                        // Nếu bảng chưa tồn tại thì bỏ qua
                    }
                }
            }

            setFlash('success', 'Thêm phim mới thành công.');
        } catch (\Throwable $e) {
            setFlash('error', 'Không thể thêm phim. Vui lòng thử lại.');
        }

        redirect('/admin/phim');
    }

    public function edit(int $id)
    {
        $qb = $this->connection->createQueryBuilder();
        $movie = $qb->select('*')
            ->from('movies')
            ->where('id = :id')
            ->setParameter('id', $id)
            ->setMaxResults(1)
            ->fetchAssociative();

        if (!$movie) {
            setFlash('error', 'Phim không tồn tại.');
            redirect('/admin/phim');
        }

        $categories = [];
        $selectedCategories = [];
        try {
            $categories = (new Category())->all();

            $qb = $this->connection->createQueryBuilder();
            $rows = $qb->select('category_id')
                ->from('movie_category')
                ->where('movie_id = :mid')
                ->setParameter('mid', $id)
                ->fetchAllAssociative();
            $selectedCategories = array_map('intval', array_column($rows, 'category_id'));
        } catch (\Throwable $e) {
            $categories = [];
            $selectedCategories = [];
        }

        return view('admin.movies.edit', compact('movie', 'categories', 'selectedCategories'));
    }

    public function update(int $id)
    {
        $validator = new Validator();
        $validation = $validator->make($_POST, [
            'title'       => 'required|min:3',
            'slug'        => 'required|min:3',
            'year'        => 'integer',
            'country'     => 'max:100',
            'description' => 'required|min:10',
        ]);
        $validation->validate();

        if ($validation->fails()) {
            $errors = $validation->errors()->firstOfAll();
            setFlash('error', reset($errors) ?: 'Dữ liệu không hợp lệ');
            redirect('/admin/phim/' . $id . '/sua');
        }

        $title = trim($_POST['title']);
        $slug = trim($_POST['slug']);
        $year = (int)($_POST['year'] ?? 0);
        $country = trim($_POST['country'] ?? '');
        $description = trim($_POST['description']);
        $status = $_POST['status'] ?? 'ongoing';
        $isPublished = isset($_POST['is_published']) ? 1 : 0;
        $trailerUrl = $_POST['trailer_url'] ?? null;

        $posterPath = $_POST['poster_url_old'] ?? null;
        $bannerPath = $_POST['banner_url_old'] ?? null;

        try {
            if (is_upload('poster')) {
                $posterPath = $this->uploader->uploadFile($_FILES['poster'], 'posters');
            }
            if (is_upload('banner')) {
                $bannerPath = $this->uploader->uploadFile($_FILES['banner'], 'banners');
            }

            $qb = $this->connection->createQueryBuilder();
            $qb->update('movies')
                ->set('title', ':title')
                ->set('slug', ':slug')
                ->set('year', ':year')
                ->set('country', ':country')
                ->set('description', ':description')
                ->set('status', ':status')
                ->set('is_published', ':is_published')
                ->set('poster_url', ':poster')
                ->set('banner_url', ':banner')
                ->set('trailer_url', ':trailer')
                ->set('updated_at', ':updated_at')
                ->where('id = :id')
                ->setParameter('id', $id)
                ->setParameter('title', $title)
                ->setParameter('slug', $slug)
                ->setParameter('year', $year ?: null)
                ->setParameter('country', $country ?: null)
                ->setParameter('description', $description)
                ->setParameter('status', $status)
                ->setParameter('is_published', $isPublished)
                ->setParameter('poster', $posterPath)
                ->setParameter('banner', $bannerPath)
                ->setParameter('trailer', $trailerUrl)
                ->setParameter('updated_at', date('Y-m-d H:i:s'))
                ->executeQuery();

            // Cập nhật lại thể loại
            $categoryIds = $_POST['category_ids'] ?? [];
            try {
                $qb = $this->connection->createQueryBuilder();
                $qb->delete('movie_category')
                    ->where('movie_id = :mid')
                    ->setParameter('mid', $id)
                    ->executeQuery();

                if (!empty($categoryIds) && is_array($categoryIds)) {
                    foreach ($categoryIds as $cid) {
                        $qb = $this->connection->createQueryBuilder();
                        $qb->insert('movie_category')
                            ->values([
                                'movie_id'    => ':mid',
                                'category_id' => ':cid',
                            ])
                            ->setParameter('mid', $id)
                            ->setParameter('cid', (int)$cid)
                            ->executeQuery();
                    }
                }
            } catch (\Throwable $e) {
                // Nếu không có bảng liên kết thể loại thì bỏ qua
            }

            setFlash('success', 'Cập nhật phim thành công.');
        } catch (\Throwable $e) {
            setFlash('error', 'Không thể cập nhật phim. Vui lòng thử lại.');
        }

        redirect('/admin/phim');
    }

    public function destroy(int $id)
    {
        try {
            // Xóa quan hệ thể loại
            try {
                $qb = $this->connection->createQueryBuilder();
                $qb->delete('movie_category')
                    ->where('movie_id = :mid')
                    ->setParameter('mid', $id)
                    ->executeQuery();
            } catch (\Throwable $e) {
            }

            // Xóa tập phim
            try {
                $qb = $this->connection->createQueryBuilder();
                $qb->delete('episodes')
                    ->where('movie_id = :mid')
                    ->setParameter('mid', $id)
                    ->executeQuery();
            } catch (\Throwable $e) {
            }

            // Xóa phim
            $qb = $this->connection->createQueryBuilder();
            $qb->delete('movies')
                ->where('id = :id')
                ->setParameter('id', $id)
                ->executeQuery();

            setFlash('success', 'Đã xóa phim.');
        } catch (\Throwable $e) {
            setFlash('error', 'Không thể xóa phim.');
        }

        redirect('/admin/phim');
    }
}

