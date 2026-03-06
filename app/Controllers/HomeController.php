<?php

namespace App\Controllers;

use App\Models\Movie;

class HomeController
{
    /**
     * Trang chủ:
     * - Banner slider (hiện tại là UI tĩnh)
     * - Các section phim: mới cập nhật, hot, đề xuất
     */
    public function index()
    {
        try {
            $movieModel = new Movie();
            $sections = $movieModel->getHomeSections();
        } catch (\Throwable $e) {
            error_log('HomeController error: ' . $e->getMessage());
            // If there's an error getting movies, provide empty sections so page still displays
            $sections = [
                [
                    'title' => 'Phim mới cập nhật',
                    'slug' => 'new',
                    'movies' => [],
                ],
                [
                    'title' => 'Phim hot',
                    'slug' => 'hot',
                    'movies' => [],
                ],
                [
                    'title' => 'Phim đề xuất',
                    'slug' => 'recommended',
                    'movies' => [],
                ],
            ];
        }

        return view('home', compact('sections'));
    }
}