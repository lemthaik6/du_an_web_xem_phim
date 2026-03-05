<?php

namespace App\Controllers;

use App\Model;
use Rakit\Validation\Validator;

class AuthController extends Model
{
    // Trang đăng nhập (dùng cho fallback khi không bật modal / direct visit)
    public function loginPage()
    {
        $error = getFlash('error');
        $success = getFlash('success');

        return view('auth.login', compact('error', 'success'));
    }

    public function login()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $validator = new Validator();
        $validation = $validator->make($_POST, [
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);
        $validation->validate();

        if ($validation->fails()) {
            $errors = $validation->errors()->firstOfAll();
            return $this->respondAuthError('Dữ liệu không hợp lệ', $errors);
        }

        $email = $_POST['email'];
        $password = $_POST['password'];

        // Giả định bảng users với các cột: id, name, email, password, role
        $qb = $this->connection->createQueryBuilder();
        $user = $qb->select('*')
            ->from('users')
            ->where('email = :email')
            ->setParameter('email', $email)
            ->setMaxResults(1)
            ->fetchAssociative();

        if (!$user || !password_verify($password, $user['password'] ?? '')) {
            return $this->respondAuthError('Email hoặc mật khẩu không đúng');
        }

        $_SESSION['auth_user'] = [
            'id'    => $user['id'],
            'name'  => $user['name'] ?? ($user['username'] ?? $user['email']),
            'email' => $user['email'],
            'role'  => $user['role'] ?? 'user',
        ];

        if ($this->isAjax()) {
            json([
                'ok'      => true,
                'message' => 'Đăng nhập thành công',
            ]);
        }

        setFlash('success', 'Đăng nhập thành công');
        redirect('/');
    }

    public function register()
    {
        $validator = new Validator();
        $validation = $validator->make($_POST, [
            'name'                  => 'required|min:3',
            'email'                 => 'required|email',
            'password'              => 'required|min:6',
            'password_confirmation' => 'required|same:password',
        ]);
        $validation->validate();

        if ($validation->fails()) {
            $errors = $validation->errors()->firstOfAll();
            return $this->respondAuthError('Dữ liệu không hợp lệ', $errors);
        }

        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        // Kiểm tra tồn tại
        $qb = $this->connection->createQueryBuilder();
        $exists = $qb->select('COUNT(*) as c')
            ->from('users')
            ->where('email = :email')
            ->setParameter('email', $email)
            ->fetchOne();

        if ((int)$exists > 0) {
            return $this->respondAuthError('Email đã được sử dụng');
        }

        // Thêm user mới với role mặc định là user
        $qb = $this->connection->createQueryBuilder();
        $qb->insert('users')
            ->values([
                'name'     => ':name',
                'email'    => ':email',
                'password' => ':password',
                'role'     => ':role',
            ])
            ->setParameter('name', $name)
            ->setParameter('email', $email)
            ->setParameter('password', $password)
            ->setParameter('role', 'user')
            ->executeQuery();

        if ($this->isAjax()) {
            json([
                'ok'      => true,
                'message' => 'Đăng ký thành công, vui lòng đăng nhập',
            ]);
        }

        setFlash('success', 'Đăng ký thành công, vui lòng đăng nhập');
        redirect('/dang-nhap');
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        unset($_SESSION['auth_user']);

        if ($this->isAjax()) {
            json([
                'ok'      => true,
                'message' => 'Đăng xuất thành công',
            ]);
        }

        setFlash('success', 'Đăng xuất thành công');
        redirect('/');
    }

    protected function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    protected function respondAuthError(string $message, array $errors = [])
    {
        if ($this->isAjax()) {
            json([
                'ok'     => false,
                'error'  => $message,
                'fields' => $errors,
            ], 422);
        }

        setFlash('error', $message);
        redirect('/dang-nhap');
    }
}

