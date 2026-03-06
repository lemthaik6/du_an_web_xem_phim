<?php

namespace App\Controllers;

use App\Models\Movie as MovieModel;

/**
 * Controller hiển thị chi tiết phim và trang xem phim.
 */
class MovieController
{
    protected MovieModel $movies;

    public function __construct()
    {
        $this->movies = new MovieModel();
    }

    /**
     * Trang chi tiết phim:
     * - Poster, mô tả, thể loại, năm, diễn viên (nếu bảng có)
     * - Danh sách tập
     * - Bình luận (AJAX riêng)
     */
    public function show(string $slug)
    {
        $movie = $this->movies->findBySlug($slug);
        if (!$movie) {
            redirect404();
        }

        $episodes = $this->movies->getEpisodes((int)$movie['id']);
        $relatedMovies = $this->movies->getRelatedMovies((int)$movie['id'], 12);

        // Bình luận sẽ được tải qua API riêng, ở đây chỉ cần mảng rỗng cho view.
        $comments = [];

        return view('movie.show', compact('movie', 'episodes', 'relatedMovies', 'comments'));
    }

    /**
     * Trang xem phim (player):
     * - HTML5 video
     * - Danh sách tập + next/prev
     * - Tăng lượt xem
     */
    public function watch(string $slug, int $episodeNumber)
    {
        $movie = $this->movies->findBySlug($slug);
        if (!$movie) {
            redirect404();
        }

        $episodes = $this->movies->getEpisodes((int)$movie['id']);
        $currentEpisode = $this->movies->getEpisodeByNumber((int)$movie['id'], $episodeNumber);

        if (!$currentEpisode) {
            redirect404();
        }

        // Tăng lượt xem tổng cho phim
        $this->movies->incrementViews((int)$movie['id']);

        // Nguồn video: sử dụng trực tiếp video_url từ bảng episodes
        $sources = [
            [
                'server'  => 'Server chính',
                'quality' => 'HD',
                'url'     => $currentEpisode['video_url'] ?? '',
            ],
        ];

        return view('movie.watch', compact('movie', 'episodes', 'currentEpisode', 'sources'));
    }
}

