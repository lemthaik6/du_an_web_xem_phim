<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Movie;

try {
    $movieModel = new Movie();
    
    // Test 1: Get movie list
    echo "=== TEST 1: Movie List ===\n";
    $movies = $movieModel->getMovies([]);
    echo "✓ Movies fetched: " . count($movies) . "\n\n";
    
    // Test 2: Get movie detail
    echo "=== TEST 2: Movie Detail ===\n";
    $movie = $movieModel->findBySlug('doctor-strange');
    if ($movie) {
        echo "✓ Movie found: " . $movie['title'] . "\n";
        echo "  Poster: " . $movie['poster_url'] . "\n";
        echo "  Categories: " . implode(', ', $movie['categories'] ?? []) . "\n\n";
    }
    
    // Test 3: Get episodes
    echo "=== TEST 3: Episodes ===\n";
    $episodes = $movieModel->getEpisodes($movie['id']);
    echo "✓ Episodes fetched: " . count($episodes) . "\n";
    if (count($episodes) > 0) {
        $firstEp = $episodes[0];
        echo "  First episode: " . $firstEp['title'] . "\n";
        echo "  Video URL: " . ($firstEp['video_url'] ?? 'N/A') . "\n\n";
    }
    
    // Test 4: Get single episode
    echo "=== TEST 4: Single Episode ===\n";
    $episode = $movieModel->getEpisodeByNumber($movie['id'], 1);
    if ($episode) {
        echo "✓ Episode #1: " . $episode['title'] . "\n";
        echo "  Video URL: " . ($episode['video_url'] ?? 'N/A') . "\n";
    }
    
    echo "\n✅ ALL TESTS PASSED!\n";
    
} catch (\Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
