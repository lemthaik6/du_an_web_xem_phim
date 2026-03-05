<?php

namespace App\Controllers;

use App\Model;

class ProfileController extends Model
{
    public function index()
    {
        $user = auth_user();
        if (!$user) {
            redirect('/dang-nhap');
        }

        // Dữ liệu demo – sau này thay bằng query từ favorites/watch_history
        $favoriteMovies = [
            ['title' => 'Phim yêu thích 1', 'slug' => 'phim-yeu-thich-1'],
            ['title' => 'Phim yêu thích 2', 'slug' => 'phim-yeu-thich-2'],
        ];

        $watchHistory = [
            ['title' => 'Phim đã xem 1', 'slug' => 'phim-da-xem-1', 'episode' => 3, 'progress' => 65],
            ['title' => 'Phim đã xem 2', 'slug' => 'phim-da-xem-2', 'episode' => 1, 'progress' => 20],
        ];

        return view('user.profile', compact('user', 'favoriteMovies', 'watchHistory'));
    }
}

