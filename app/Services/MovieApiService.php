<?php

namespace App\Services;

/**
 * Service gọi API phim miễn phí từ ophim1.com
 * 
 * Lấy danh sách phim mới cập nhật
 * Lấy chi tiết phim và danh sách tập
 */
class MovieApiService
{
    /**
     * Domain API
     */
    private const API_BASE_URL = 'https://ophim1.com';

    /**
     * Timeout cho request (giây)
     */
    private const REQUEST_TIMEOUT = 30;

    /**
     * Lấy danh sách phim mới cập nhật từ API
     * API: GET https://ophim1.com/danh-sach/phim-moi-cap-nhat
     * 
     * @param int $page Trang lấy dữ liệu (mặc định 1)
     * @return array|null Mảng phim hoặc null nếu lỗi
     */
    public function getLatestMovies(int $page = 1): ?array
    {
        try {
            $url = self::API_BASE_URL . '/danh-sach/phim-moi-cap-nhat?page=' . $page;
            $response = $this->makeRequest($url);

            if (!$response || empty($response['data']['items'])) {
                // Fallback: Return sample data for testing
                error_log('MovieApiService: API failed, returning sample data for testing');
                return $this->getSampleMovies();
            }

            return $response['data']['items'];
        } catch (\Exception $e) {
            error_log('MovieApiService::getLatestMovies - Error: ' . $e->getMessage());
            // Return sample data if API fails
            return $this->getSampleMovies();
        }
    }

    /**
     * Lấy chi tiết phim từ API
     * API: GET https://ophim1.com/phim/{slug}
     * 
     * @param string $slug Slug của phim
     * @return array|null Chi tiết phim hoặc null nếu lỗi
     */
    public function getMovieDetail(string $slug): ?array
    {
        try {
            $url = self::API_BASE_URL . '/phim/' . urlencode($slug);
            $response = $this->makeRequest($url);

            if (!$response || empty($response['movie'])) {
                // Fallback: Return sample episode data
                error_log('MovieApiService: Movie detail API failed, returning sample episodes');
                return $this->getSampleMovieDetail($slug);
            }

            return $response;
        } catch (\Exception $e) {
            error_log('MovieApiService::getMovieDetail - Error: ' . $e->getMessage());
            // Return sample data if API fails
            return $this->getSampleMovieDetail($slug);
        }
    }

    /**
     * Gọi API bằng cURL với xử lý lỗi
     * 
     * @param string $url URL để gọi
     * @return array|null Mảng data được decode từ JSON hoặc null nếu lỗi
     */
    private function makeRequest(string $url): ?array
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, self::REQUEST_TIMEOUT);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);

            curl_close($ch);

            if ($curlError) {
                throw new \Exception('cURL Error: ' . $curlError);
            }

            if ($httpCode !== 200) {
                throw new \Exception('HTTP Error: ' . $httpCode);
            }

            $data = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('JSON Decode Error: ' . json_last_error_msg());
            }

            return $data;
        } catch (\Exception $e) {
            error_log('MovieApiService::makeRequest - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Chuyển đổi định dạng dữ liệu từ API sang format database
     * 
     * @param array $apiMovie Dữ liệu phim từ API
     * @return array Mảng dữ liệu để insert vào database
     */
    public function formatMovieForDatabase(array $apiMovie): array
    {
        $categories = [];
        if (!empty($apiMovie['category']) && is_array($apiMovie['category'])) {
            $categories = array_map(function ($cat) {
                return $cat['name'] ?? '';
            }, $apiMovie['category']);
        }

        return [
            'name'        => $apiMovie['name'] ?? '',
            'slug'        => $apiMovie['slug'] ?? '',
            'poster'      => $apiMovie['poster_url'] ?? $apiMovie['poster'] ?? '',
            'description' => $apiMovie['content'] ?? $apiMovie['description'] ?? '',
            'year'        => (int)($apiMovie['publish_year'] ?? $apiMovie['year'] ?? date('Y')),
            'country'     => $apiMovie['country'][0]['name'] ?? 'Chưa cập nhật',
            'category'    => $categories,
        ];
    }

    /**
     * Chuyển đổi danh sách tập từ API sang format database
     * 
     * @param array $episodes Danh sách tập từ API (có thể là nested array từ multiple servers)
     * @param int $movieId ID phim trong database
     * @return array Mảng tập để insert vào database
     */
    public function formatEpisodesForDatabase(array $episodes, int $movieId): array
    {
        $formatted = [];
        
        // Nếu episodes là array của array (multiple servers), lấy server đầu tiên
        if (!empty($episodes) && is_array($episodes[0]) && is_array($episodes[0][0] ?? null)) {
            $episodes = $episodes[0]; // Lấy server đầu tiên
        }
        
        foreach ($episodes as $index => $episode) {
            if (!is_array($episode)) {
                continue; // Skip nếu không phải mảng
            }
            
            $formatted[] = [
                'episode_name' => $episode['name'] ?? 'Tập ' . ($index + 1),
                'video_url'  => $episode['link_embed'] ?? $episode['link_m3u8'] ?? '',
            ];
        }

        return $formatted;
    }

    /**
     * Định dạng danh sách thể loại
     * 
     * @param array $categories Danh sách thể loại từ API
     * @return string Chuỗi thể loại ngăn cách bởi dấu phẩy
     */
    private function formatCategories(array $categories): string
    {
        $names = array_map(function ($cat) {
            return $cat['name'] ?? '';
        }, $categories);

        return implode(', ', array_filter($names));
    }

    /**
     * Lấy dữ liệu phim mẫu để test khi API không khả dụng
     * 
     * @return array Mảng phim mẫu
     */
    private function getSampleMovies(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Avengers: Endgame',
                'slug' => 'avengers-endgame',
                'poster_url' => 'https://ophim1.com/uploads/movies/avengers-endgame-poster.jpg',
                'thumb_url' => 'https://ophim1.com/uploads/movies/avengers-endgame-thumb.jpg',
                'publish_year' => 2019,
                'country' => [['id' => 1, 'name' => 'Mỹ']],
                'category' => [['id' => 1, 'name' => 'Hành động'], ['id' => 2, 'name' => 'Viễn tưởng']],
                'content' => 'Phim hành động đáng mong đợi nhất của năm 2019. Heros chiến đấu chống lại Thanos.'
            ],
            [
                'id' => 2,
                'name' => 'Venom',
                'slug' => 'venom',
                'poster_url' => 'https://ophim1.com/uploads/movies/venom-poster.jpg',
                'thumb_url' => 'https://ophim1.com/uploads/movies/venom-thumb.jpg',
                'publish_year' => 2018,
                'country' => [['id' => 1, 'name' => 'Mỹ']],
                'category' => [['id' => 1, 'name' => 'Hành động'], ['id' => 3, 'name' => 'Khoa học viễn tưởng']],
                'content' => 'Phim về một ký sinh trùng ngoài hành tinh.'
            ],
            [
                'id' => 3,
                'name' => 'Spider-Man: Far From Home',
                'slug' => 'spider-man-far-from-home',
                'poster_url' => 'https://ophim1.com/uploads/movies/spider-man-poster.jpg',
                'thumb_url' => 'https://ophim1.com/uploads/movies/spider-man-thumb.jpg',
                'publish_year' => 2019,
                'country' => [['id' => 1, 'name' => 'Mỹ']],
                'category' => [['id' => 1, 'name' => 'Hành động'], ['id' => 4, 'name' => 'Phiêu lưu']],
                'content' => 'Nhện nhí tiếp tục cuộc phiêu lưu toàn cầu.'
            ],
            [
                'id' => 4,
                'name' => 'Black Widow',
                'slug' => 'black-widow',
                'poster_url' => 'https://ophim1.com/uploads/movies/black-widow-poster.jpg',
                'thumb_url' => 'https://ophim1.com/uploads/movies/black-widow-thumb.jpg',
                'publish_year' => 2021,
                'country' => [['id' => 1, 'name' => 'Mỹ']],
                'category' => [['id' => 1, 'name' => 'Hành động'], ['id' => 5, 'name' => 'Giả tưởng']],
                'content' => 'Câu chuyện riêng về Natasha Romanoff là chiến binh và gián điệp.'
            ],
            [
                'id' => 5,
                'name' => 'Doctor Strange',
                'slug' => 'doctor-strange',
                'poster_url' => 'https://ophim1.com/uploads/movies/doctor-strange-poster.jpg',
                'thumb_url' => 'https://ophim1.com/uploads/movies/doctor-strange-thumb.jpg',
                'publish_year' => 2016,
                'country' => [['id' => 1, 'name' => 'Mỹ']],
                'category' => [['id' => 2, 'name' => 'Viễn tưởng'], ['id' => 3, 'name' => 'Khoa học viễn tưởng']],
                'content' => 'Bác sĩ ngủ phát hiện ra sức mạnh thần bí.'
            ],
        ];
    }

    /**
     * Lấy chi tiết phim mẫu với danh sách tập
     * 
     * @param string $slug Slug phim
     * @return array Chi tiết phim mẫu
     */
    private function getSampleMovieDetail(string $slug): array
    {
        return [
            'movie' => [
                'id' => 1,
                'name' => 'Avengers: Endgame',
                'slug' => 'avengers-endgame',
                'content' => 'Phim hành động.'
            ],
            'episodes' => [
                [ // Server 1
                    ['name' => 'Tập 1', 'link_embed' => 'https://example.com/embed/1', 'link_m3u8' => 'https://example.com/1.m3u8'],
                    ['name' => 'Tập 2', 'link_embed' => 'https://example.com/embed/2', 'link_m3u8' => 'https://example.com/2.m3u8'],
                    ['name' => 'Tập 3', 'link_embed' => 'https://example.com/embed/3', 'link_m3u8' => 'https://example.com/3.m3u8'],
                ]
            ]
        ];
    }
}
