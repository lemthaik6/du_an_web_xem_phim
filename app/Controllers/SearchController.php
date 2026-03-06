<?php

namespace App\Controllers;

use App\Models\Movie;

class SearchController
{
    public function index()
    {
        $q = $_GET['q'] ?? '';
        $year = $_GET['year'] ?? '';
        $genre = $_GET['genre'] ?? '';
        $country = $_GET['country'] ?? '';

        $filters = [
            'q'       => $q,
            'year'    => $year,
            'country' => $country,
        ];

        // Chuyển genre thành category_id nếu bạn lưu slug thể loại
        if ($genre) {
            // Ở mức tối thiểu, truyền luôn slug để sau này mở rộng; hiện tại Movie::getMovies sẽ không dùng nếu không có bảng movie_category
            $filters['genre_slug'] = $genre;
        }

        // Đơn giản hoá: phân trang dạng ?page=1,2,3...
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 24;

        $movieModel = new Movie();
        $movies = [];
        $total = 0;
        if ($q || $genre || $year || $country) {
            $total = $movieModel->countForSearch($filters);

            // Lấy danh sách phim trang hiện tại
            $movies = $movieModel->getMovies(array_merge($filters, [
                'order' => 'latest',
                'limit' => $perPage,
            ]));
        }

        $filters = compact('q', 'year', 'genre', 'country');

        return view('search.index', [
            'movies'  => $movies,
            'filters' => $filters,
            'page'    => $page,
            'total'   => $total,
            'perPage' => $perPage,
        ]);
    }
}

