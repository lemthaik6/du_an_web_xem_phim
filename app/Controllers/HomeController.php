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
        $movieModel = new Movie();
        $sections = $movieModel->getHomeSections();

        return view('home', compact('sections'));
    }
}