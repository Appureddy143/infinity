<?php
// This is the backend logic that was in 'admin_add_series.php'
require 'db_connect.php';

// Security Check
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: admin.php?error=Unauthorized');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Common Series Details
    $title = $_POST['series_title'];
    $description = $_POST['series_description'];
    $poster_url = $_POST['series_poster_url'];
    $genre = $_POST['series_genre'];
    $episode_type = $_POST['episode_type'];

    // All database operations will be in a transaction
    $pdo->beginTransaction();

    try {
        // 1. Insert the main series into the 'movies' table
        // --- THIS QUERY IS NOW FIXED ---
        // Added the 'type' column to the INSERT statement
        $stmt = $pdo->prepare("
            INSERT INTO movies (title, description, poster_url, genre, is_series, type)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        // is_series is true, 'type' is 'Series'
        $stmt->execute([$title, $description, $poster_url, $genre, true, 'Series']); 
        $movie_id = $pdo->lastInsertId();

        // 2. Check which type of episodes to add
        if ($episode_type == 'merged') {
            // --- ADDING A MERGED SEASON FILE ---
            
            // Get merged data
            $season_number = $_POST['merged_season'];
            $ep_title = $_POST['merged_title'];
            $ep_video_url = $_POST['merged_video_url'];
            $ep_language = $_POST['merged_language'];
            $duration_minutes = $_POST['merged_duration'];
            $duration_seconds = $duration_minutes * 60;

            // 2a. Create the season
            $stmt = $pdo->prepare("INSERT INTO seasons (movie_id, season_number) VALUES (?, ?)");
            $stmt->execute([$movie_id, $season_number]);
            $season_id = $pdo->lastInsertId();

            // 2b. Add the single merged episode
            $stmt = $pdo->prepare("
                INSERT INTO episodes (movie_id, season_id, episode_number, title, video_url, duration, language)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            // It's "Episode 1" of this season
            $stmt->execute([$movie_id, $season_id, 1, $ep_title, $ep_video_url, $duration_seconds, $ep_language]);

        } else {
            // --- ADDING EPISODIC FILES ---
            
            // Get episodic data
            $season_number = $_POST['season_number'];
            $ep_titles = $_POST['ep_title'];
            $ep_numbers = $_POST['ep_number'];
            $ep_video_urls = $_POST['ep_video_url'];
            $ep_languages = $_POST['ep_language'];
            $ep_durations = $_POST['ep_duration'];

            // 2a. Create the season
            $stmt = $pdo->prepare("INSERT INTO seasons (movie_id, season_number) VALUES (?, ?)");
            $stmt->execute([$movie_id, $season_number]);
            $season_id = $pdo->lastInsertId();

            // 2b. Loop through each episode and add it
            $stmt = $pdo->prepare("
                INSERT INTO episodes (movie_id, season_id, episode_number, title, video_url, duration, language)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            for ($i = 0; $i < count($ep_titles); $i++) {
                $duration_seconds = $ep_durations[$i] * 60;
                $stmt->execute([
                    $movie_id,
                    $season_id,
                    $ep_numbers[$i],
                    $ep_titles[$i],
                    $ep_video_urls[$i],
                    $duration_seconds,
                    $ep_languages[$i]
                ]);
            }
        }

        // 3. If everything worked, commit the changes
        $pdo->commit();
        header('Location: admin.php?success=Series added successfully!');
        exit;

    } catch (Exception $e) {
        // If anything failed, roll back all changes
        $pdo->rollBack();
        header('Location: admin.php?error=Failed to add series: ' . urlencode($e->getMessage()));
        exit;
    }
} else {
    header('Location: admin.php');
    exit;
}
?>