<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Episode;
use App\Models\Movie;

echo "=== TEST 2: AUTH FUNCTION & EPISODE VIDEOS ===\n\n";

try {
    // Test auth_user function exists
    echo "✓ Testing auth_user() function...\n";
    $user = auth_user();
    echo "  Current user: " . ($user ? $user['name'] : 'Guest') . "\n\n";
    
    // Test Episode model with video URLs
    $episodeModel = new Episode();
    $movieModel = new Movie();
    
    // Get first movie
    $movies = $movieModel->getMovies([]);
    if (count($movies) > 0) {
        $movie = $movies[0];
        echo "✓ Movie: " . $movie['title'] . "\n";
        
        // Get episodes with video URLs
        $episodes = $movieModel->getEpisodes($movie['id']);
        echo "  Episodes found: " . count($episodes) . "\n";
        
        if (count($episodes) > 0) {
            $ep = $episodes[0];
            echo "  First episode: " . $ep['title'] . "\n";
            echo "  Video URL: " . ($ep['video_url'] ?? 'EMPTY') . "\n\n";
        }
        
        // Test getById from Episode model
        if (count($episodes) > 0 && !empty($ep['id'])) {
            echo "✓ Testing Episode::getById()...\n";
            $singleEp = $episodeModel->getById($ep['id']);
            if ($singleEp) {
                echo "  Episode ID: " . $singleEp['id'] . "\n";
                echo "  Video URL: " . ($singleEp['video_url'] ?? 'EMPTY') . "\n";
            }
        }
    }
    
    echo "\n✅ TEST 2 PASSED!\n";
    
} catch (\Throwable $e) {
    echo "❌ TEST 2 FAILED: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
