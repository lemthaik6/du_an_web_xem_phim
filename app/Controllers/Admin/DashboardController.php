<?php

namespace App\Controllers\Admin;

use App\Model;

class DashboardController extends Model
{
    public function index()
    {
        // Thử thống kê nhanh; nếu bảng chưa tồn tại sẽ rơi vào 0 (demo-friendly)
        $stats = [
            'movies' => 0,
            'users' => 0,
            'views' => 0,
        ];

        try {
            $conn = $this->connection;
            $stats['movies'] = (int) $conn->createQueryBuilder()
                ->select('COUNT(*)')
                ->from('movies')
                ->fetchOne();
        } catch (\Throwable $e) {
        }

        try {
            $conn = $this->connection;
            $stats['users'] = (int) $conn->createQueryBuilder()
                ->select('COUNT(*)')
                ->from('users')
                ->fetchOne();
        } catch (\Throwable $e) {
        }

        try {
            $conn = $this->connection;
            $stats['views'] = (int) $conn->createQueryBuilder()
                ->select('COALESCE(SUM(views_count),0)')
                ->from('movies')
                ->fetchOne();
        } catch (\Throwable $e) {
        }

        return view('admin.dashboard', compact('stats'));
    }
}

