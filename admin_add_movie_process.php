<?php
// This is the backend logic that was in 'admin_add_movie.php'
require 'db_connect.php';

// Security Check
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: admin.php?error=Unauthorized');
    exit;
}

// Check if form data is sent
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get all form data
    $title = $_POST['movie_title'];
    $description = $_POST['movie_description'];
    $poster_url = $_POST['movie_poster_url'];
    $video_url = $_POST['movie_video_url'];
    $genre = $_POST['movie_genre'];
    $language = $_POST['movie_language'];
    $duration_minutes = $_POST['movie_duration'];

    // Convert duration to seconds
    $duration_seconds = $duration_minutes * 60;
    
    // Begin database transaction
    $pdo->beginTransaction();
    
    try {
        // 1. Insert into 'movies' table
        // We are now using lowercase 'movie' to match the database check constraint
        $stmt = $pdo->prepare("
            INSERT INTO movies (title, description, poster_url, genre, is_series, language, type)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        // 'is_series' is false, 'language' is set, 'type' is 'movie' (lowercase)
        $stmt->execute([$title, $description, $poster_url, $genre, false, $language, 'movie']);
        
        // Get the new movie_id
        $movie_id = $pdo->lastInsertId();
        
        // 2. Insert into 'episodes' table (a movie is just a series with one episode)
        // Note: A movie has no 'season', so we can leave season_id as NULL
        $stmt = $pdo->prepare("
            INSERT INTO episodes (movie_id, episode_number, title, video_url, duration, language)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        // Episode 1, title is same as movie title
        $stmt->execute([$movie_id, 1, $title, $video_url, $duration_seconds, $language]);
        
        // If all good, commit the transaction
        $pdo->commit();
        
        // Redirect back to admin panel with success message
        header('Location: admin.php?success=Movie added successfully!');
        exit;

    } catch (Exception $e) {
        // If anything fails, roll back the transaction
        $pdo->rollBack();
        
        // Redirect with error
        header('Location: admin.php?error=Failed to add movie: ' . urlencode($e->getMessage()));
        exit;
    }
} else {
    // If not a POST request, redirect
    header('Location: admin.php');
    exit;
}
?>

