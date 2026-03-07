<?php

namespace App\Controllers;

use App\Models\Movie as MovieModel;
use App\Models\Episode as EpisodeModel;

/**
 * Controller hiển thị danh sách phim và chi tiết phim.
 * 
 * Chức năng:
 * - index(): Hiển thị danh sách phim
 * - show($slug): Hiển thị chi tiết phim
 * - watch($slug, $episodeId): Xem tập phim (player)
 */
class MovieController
{
    protected MovieModel $movies;
    protected EpisodeModel $episodes;

    public function __construct()
    {
        $this->movies = new MovieModel();
        $this->episodes = new EpisodeModel();
    }

    /**
     * Hiển thị danh sách phim (gọi từ header search hoặc filter page)
     */
    public function index()
    {
        // Lấy tham số filter từ query string
        $page = (int)($_GET['page'] ?? 1);
        $page = max(1, $page);

        // Lấy danh sách phim với filter
        $filters = [
            'q'       => $_GET['q'] ?? '',
            'year'    => $_GET['year'] ?? '',
            'country' => $_GET['country'] ?? '',
            'limit'   => 20,
        ];

        $movies = $this->movies->getMovies($filters);
        $total = $this->movies->countForSearch($filters);
        $perPage = 20;
        $totalPages = ceil($total / $perPage);

        return view('movie.index', compact('movies', 'page', 'totalPages', 'total', 'perPage', 'filters'));
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
            redirect404('Phim không tồn tại');
        }

        // Lấy danh sách tập phim
        $episodes = $this->movies->getEpisodes((int)$movie['id']);
        
        // Lấy phim liên quan (cùng thể loại)
        $relatedMovies = $this->movies->getRelatedMovies((int)$movie['id'], 12);

        // Bình luận sẽ được tải qua API AJAX riêng, ở đây chỉ cần mảng rỗng cho view.
        $comments = [];

        return view('movie.show', compact('movie', 'episodes', 'relatedMovies', 'comments'));
    }

    /**
     * Trang xem phim (player):
     * - HTML5 video player
     * - Danh sách tập + next/prev
     * - Tăng lượt xem
     * 
     * @param string $slug Slug của phim
     * @param int $episodeId ID của tập muốn xem
     */
    public function watch(string $slug, int $episodeId)
    {
        // Lấy thông tin phim theo slug
        $movie = $this->movies->findBySlug($slug);
        if (!$movie) {
            redirect404('Phim không tồn tại');
        }

        // Lấy thông tin tập phim
        $currentEpisode = $this->episodes->getById($episodeId);
        if (!$currentEpisode || (int)$currentEpisode['movie_id'] !== (int)$movie['id']) {
            redirect404('Tập phim không tồn tại hoặc không thuộc phim này');
        }

        // Lấy danh sách tất cả tập của phim
        $episodes = $this->movies->getEpisodes((int)$movie['id']);

        // Tăng lượt xem cho phim
        $this->movies->incrementViews((int)$movie['id']);

        // Tăng lượt xem cho tập phim (nếu bảng episodes có views_count)
        $this->episodes->incrementViews($episodeId);

        // Chuẩn bị nguồn video cho player
        // Video URL từ API thường là embed link hoặc m3u8
        $videoUrl = $currentEpisode['video_url'] ?? '';
        
        $sources = [
            [
                'server'  => 'Nguồn chính',
                'quality' => 'HD',
                'url'     => $videoUrl,
            ],
        ];

        return view('movie.watch', compact('movie', 'episodes', 'currentEpisode', 'sources'));
    }
}


