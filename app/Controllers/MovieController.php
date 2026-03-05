<?php

namespace App\Controllers;

use App\Model;

class MovieController extends Model
{
    public function show(string $slug)
    {
        // Demo data – thay bằng query từ DB theo slug
        $movie = [
            'id' => 1,
            'slug' => $slug,
            'title' => 'Phim demo: ' . str_replace('-', ' ', $slug),
            'original_title' => 'Demo Movie',
            'year' => 2024,
            'duration' => 120,
            'countries' => ['Việt Nam'],
            'categories' => ['Hành động', 'Phiêu lưu'],
            'poster_url' => null,
            'banner_url' => null,
            'description' => 'Mô tả phim demo. Sau này sẽ lấy nội dung thật từ cơ sở dữ liệu.',
            'rating_avg' => 4.5,
            'rating_count' => 123,
            'views' => 56789,
            'status' => 'completed',
        ];

        $episodes = [
            ['episode_number' => 1, 'title' => 'Tập 1'],
            ['episode_number' => 2, 'title' => 'Tập 2'],
            ['episode_number' => 3, 'title' => 'Tập 3'],
        ];

        $relatedMovies = [
            ['title' => 'Phim liên quan 1', 'slug' => 'phim-lien-quan-1'],
            ['title' => 'Phim liên quan 2', 'slug' => 'phim-lien-quan-2'],
            ['title' => 'Phim liên quan 3', 'slug' => 'phim-lien-quan-3'],
        ];

        $comments = [];

        return view('movie.show', compact('movie', 'episodes', 'relatedMovies', 'comments'));
    }

    public function watch(string $slug, int $episodeNumber)
    {
        $movie = [
            'id' => 1,
            'title' => 'Phim demo: ' . str_replace('-', ' ', $slug),
            'slug' => $slug,
        ];

        $episodes = [
            ['episode_number' => 1, 'title' => 'Tập 1'],
            ['episode_number' => 2, 'title' => 'Tập 2'],
            ['episode_number' => 3, 'title' => 'Tập 3'],
        ];

        $currentEpisode = null;
        foreach ($episodes as $ep) {
            if ($ep['episode_number'] === $episodeNumber) {
                $currentEpisode = $ep;
                break;
            }
        }

        $sources = [
            [
                'server' => 'Server 1',
                'quality' => '1080p',
                'url' => 'https://example.com/video-demo-1080p',
            ],
            [
                'server' => 'Server 2',
                'quality' => '720p',
                'url' => 'https://example.com/video-demo-720p',
            ],
        ];

        return view('movie.watch', compact('movie', 'episodes', 'currentEpisode', 'sources'));
    }
}

