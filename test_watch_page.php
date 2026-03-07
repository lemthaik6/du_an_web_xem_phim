<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Movie;
use App\Models\Episode;

echo "=== TEST 3: COMPLETE WATCH PAGE SIMULATION ===\n\n";

try {
    $movieModel = new Movie();
    $episodeModel = new Episode();
    
    // Simulate the watch controller behavior
    $slug = 'doctor-strange';
    
    echo "Step 1: Get movie by slug...\n";
    $movie = $movieModel->findBySlug($slug);
    if (!$movie) {
        throw new Exception("Movie not found");
    }
    echo "✓ Movie: " . $movie['title'] . "\n";
    echo "  Poster: " . substr($movie['poster_url'], 0, 50) . "...\n\n";
    
    echo "Step 2: Get all episodes for this movie...\n";
    $episodes = $movieModel->getEpisodes((int)$movie['id']);
    echo "✓ Episodes: " . count($episodes) . "\n";
    if (count($episodes) > 0) {
        echo "  First: " . $episodes[0]['title'] . "\n";
        echo "  Last: " . $episodes[count($episodes)-1]['title'] . "\n\n";
    }
    
    echo "Step 3: Get first episode details...\n";
    $firstEpisode = $episodeModel->getById($episodes[0]['id']);
    if (!$firstEpisode) {
        throw new Exception("Episode not found");
    }
    echo "✓ Episode: " . $firstEpisode['title'] . "\n";
    echo "  Video URL: " . ($firstEpisode['video_url'] ?? 'NO URL') . "\n\n";
    
    echo "Step 4: Prepare video sources for player...\n";
    $videoUrl = $firstEpisode['video_url'] ?? '';
    $sources = [
        [
            'server'  => 'Nguồn chính',
            'quality' => 'HD',
            'url'     => $videoUrl,
        ],
    ];
    
    if (!empty($sources[0]['url'])) {
        echo "✓ Video source ready: " . substr($sources[0]['url'], 0, 50) . "...\n\n";
    } else {
        echo "⚠ No video URL (will show placeholder)\n\n";
    }
    
    echo "Step 5: Verify player data structure...\n";
    $playerData = compact('movie', 'episodes', 'firstEpisode' => $firstEpisode, 'sources');
    echo "✓ Ready to render watch page\n";
    echo "  Movie title: " . $playerData['movie']['title'] . "\n";
    echo "  Episode count: " . count($playerData['episodes']) . "\n";
    echo "  Video sources: " . count($playerData['sources']) . "\n";
    echo "  Source URL available: " . (!empty($playerData['sources'][0]['url']) ? 'YES' : 'NO') . "\n\n";
    
    echo "✅ TEST 3 PASSED! Watch page will render correctly.\n";
    
} catch (\Throwable $e) {
    echo "❌ TEST 3 FAILED: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
    exit(1);
}
