<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Movie;

try {
    echo "Loading models...\n";
    
    echo "Creating movie instance...\n";
    $movieModel = new Movie();
    
    echo "Fetching movies...\n";
    $movies = $movieModel->getMovies([]);
    
    echo "Movies count: " . count($movies) . "\n";
    
    if (count($movies) > 0) {
        echo "First movie: " . $movies[0]['title'] . "\n";
    }
    
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
