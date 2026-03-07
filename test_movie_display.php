<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Movie;

try {
    $movieModel = new Movie();
    
    echo "=== TESTING MOVIE DISPLAY DATA ===\n\n";
    
    // Get movies with basic API
    $movies = $movieModel->getMovies([]);
    
    echo "Movies found: " . count($movies) . "\n\n";
    
    if (count($movies) > 0) {
        echo "Sample movie data:\n";
        $movie = $movies[0];
        echo "Title: " . $movie['title'] . "\n";
        echo "Slug: " . $movie['slug'] . "\n";
        echo "Poster: " . $movie['poster_url'] . "\n";
        echo "Year: " . $movie['year'] . "\n";
        echo "Views: " . $movie['views_count'] . "\n";
        
        // Get episodes for this movie
        $episodes = $movieModel->getEpisodes($movie['id']);
        echo "\nEpisodes: " . count($episodes) . "\n";
        if (count($episodes) > 0) {
            echo "First episode: " . $episodes[0]['title'] . "\n";
        }
    }
    
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
