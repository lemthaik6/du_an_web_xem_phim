<?php

namespace App\Controllers;

class SearchController
{
    public function index()
    {
        $q = $_GET['q'] ?? '';
        $year = $_GET['year'] ?? '';
        $genre = $_GET['genre'] ?? '';
        $country = $_GET['country'] ?? '';

        // Dữ liệu demo – sau này thay bằng query DB + pagination
        $movies = [];
        if ($q || $genre || $year || $country) {
            $movies = [
                [
                    'title' => 'Kết quả demo 1',
                    'slug' => 'ket-qua-demo-1',
                    'year' => 2024,
                    'categories' => ['Hành động'],
                ],
                [
                    'title' => 'Kết quả demo 2',
                    'slug' => 'ket-qua-demo-2',
                    'year' => 2023,
                    'categories' => ['Tâm lý'],
                ],
            ];
        }

        $filters = compact('q', 'year', 'genre', 'country');

        return view('search.index', compact('movies', 'filters'));
    }
}

