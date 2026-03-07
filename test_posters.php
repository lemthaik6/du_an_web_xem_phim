<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Movie;

try {
    $movieModel = new Movie();
    $movies = $movieModel->getMovies([]);
    
    echo "=== MOVIE DATA CHECK ===\n\n";
    echo "Total movies: " . count($movies) . "\n\n";
    
    foreach ($movies as $movie) {
        echo "Title: " . $movie['title'] . "\n";
        echo "Poster: " . ($movie['poster_url'] ?? 'EMPTY') . "\n\n";
    }
    
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
