<?php
namespace App\Controllers;

class HomeController
{
    public function index()
    {
        // Dữ liệu demo: sau này thay bằng query từ DB (phim mới, hot, đề xuất, theo thể loại)
        $sections = [
            [
                'title' => 'Phim mới cập nhật',
                'slug'  => 'new',
                'movies' => [],
            ],
            [
                'title' => 'Phim hot',
                'slug'  => 'hot',
                'movies' => [],
            ],
        ];

        return view('home', compact('sections'));
    }
}