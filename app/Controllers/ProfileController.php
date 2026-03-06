<?php

namespace App\Controllers;

use App\Model;
use Rakit\Validation\Validator;

class ProfileController extends Model
{
    /**
     * Trang dashboard tài khoản người dùng:
     * - Thông tin cá nhân
     * - Danh sách yêu thích
     * - Lịch sử xem
     */
    public function index()
    {
        $user = auth_user();
        if (!$user) {
            redirect('/dang-nhap');
        }

        $userId = $user['id'];

        // Lấy danh sách phim yêu thích từ bảng favorites + movies
        $favoriteMovies = [];
        try {
            $qb = $this->connection->createQueryBuilder();
            $favoriteMovies = $qb
                ->select('m.id', 'm.title', 'm.slug')
                ->from('favorites', 'f')
                ->innerJoin('f', 'movies', 'm', 'm.id = f.movie_id')
                ->where('f.user_id = :uid')
                ->setParameter('uid', $userId)
                ->orderBy('f.created_at', 'DESC')
                ->setMaxResults(30)
                ->fetchAllAssociative();
        } catch (\Throwable $e) {
            // Nếu bảng chưa sẵn sàng, giữ mảng rỗng để UI vẫn hoạt động
            $favoriteMovies = [];
        }

        // Lịch sử xem: lấy từ bảng views/watch_history
        $watchHistory = [];
        try {
            $qb = $this->connection->createQueryBuilder();
            $watchHistory = $qb
                ->select(
                    'm.id',
                    'm.title',
                    'm.slug',
                    'vh.episode_number',
                    'vh.progress'
                )
                ->from('views', 'vh')
                ->innerJoin('vh', 'movies', 'm', 'm.id = vh.movie_id')
                ->where('vh.user_id = :uid')
                ->setParameter('uid', $userId)
                ->orderBy('vh.updated_at', 'DESC')
                ->setMaxResults(30)
                ->fetchAllAssociative();
        } catch (\Throwable $e) {
            $watchHistory = [];
        }

        return view('user.profile', compact('user', 'favoriteMovies', 'watchHistory'));
    }

    /**
     * Cập nhật thông tin cơ bản của user (tên hiển thị).
     */
    public function updateProfile()
    {
        $user = auth_user();
        if (!$user) {
            redirect('/dang-nhap');
        }

        $validator = new Validator();
        $validation = $validator->make($_POST, [
            'name' => 'required|min:3',
        ]);
        $validation->validate();

        if ($validation->fails()) {
            $errors = $validation->errors()->firstOfAll();
            setFlash('error', reset($errors) ?: 'Dữ liệu không hợp lệ');
            redirect('/tai-khoan');
        }

        $name = trim($_POST['name']);

        try {
            $qb = $this->connection->createQueryBuilder();
            $qb->update('users')
                ->set('name', ':name')
                ->where('id = :id')
                ->setParameter('name', $name)
                ->setParameter('id', $user['id'])
                ->executeQuery();

            // Cập nhật lại session auth_user để header hiển thị đúng
            $_SESSION['auth_user']['name'] = $name;

            setFlash('success', 'Cập nhật thông tin cá nhân thành công.');
        } catch (\Throwable $e) {
            setFlash('error', 'Không thể cập nhật thông tin. Vui lòng thử lại.');
        }

        redirect('/tai-khoan');
    }

    /**
     * Đổi mật khẩu tài khoản hiện tại.
     */
    public function changePassword()
    {
        $user = auth_user();
        if (!$user) {
            redirect('/dang-nhap');
        }

        $validator = new Validator();
        $validation = $validator->make($_POST, [
            'current_password'      => 'required',
            'password'              => 'required|min:6',
            'password_confirmation' => 'required|same:password',
        ]);
        $validation->validate();

        if ($validation->fails()) {
            $errors = $validation->errors()->firstOfAll();
            setFlash('error', reset($errors) ?: 'Dữ liệu không hợp lệ');
            redirect('/tai-khoan');
        }

        try {
            $qb = $this->connection->createQueryBuilder();
            $record = $qb->select('password')
                ->from('users')
                ->where('id = :id')
                ->setParameter('id', $user['id'])
                ->setMaxResults(1)
                ->fetchAssociative();

            $hashed = $record['password'] ?? null;
            if (!$hashed || !password_verify($_POST['current_password'], $hashed)) {
                setFlash('error', 'Mật khẩu hiện tại không đúng.');
                redirect('/tai-khoan');
            }

            $newHash = password_hash($_POST['password'], PASSWORD_BCRYPT);

            $qb = $this->connection->createQueryBuilder();
            $qb->update('users')
                ->set('password', ':pwd')
                ->where('id = :id')
                ->setParameter('pwd', $newHash)
                ->setParameter('id', $user['id'])
                ->executeQuery();

            setFlash('success', 'Đổi mật khẩu thành công.');
        } catch (\Throwable $e) {
            setFlash('error', 'Không thể đổi mật khẩu. Vui lòng thử lại.');
        }

        redirect('/tai-khoan');
    }
}

