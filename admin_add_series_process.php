<?php
// This is the backend logic that was in 'admin_add_series.php'
require 'db_connect.php';

// Security Check
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: admin.php?error=Unauthorized');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // All database operations will be in a transaction
    $pdo->beginTransaction();

    try {
        // --- NEW SERVER-SIDE VALIDATION ---
        // 1. Validate Common Series Details
        $title = $_POST['series_title'] ?? null;
        $description = $_POST['series_description'] ?? null;
        $poster_url = $_POST['series_poster_url'] ?? null;
        $genre = $_POST['series_genre'] ?? null;
        $episode_type = $_POST['episode_type'] ?? null;

        if (empty($title) || empty($description) || empty($poster_url) || empty($genre) || empty($episode_type)) {
            throw new Exception("All series fields (title, description, poster, genre) are required.");
        }

        // 2. Insert the main series into the 'movies' table
        $stmt = $pdo->prepare("
            INSERT INTO movies (title, description, poster_url, genre, is_series, type)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$title, $description, $poster_url, $genre, true, 'series']); 
        $movie_id = $pdo->lastInsertId();

        // 3. Check which type of episodes to add
        if ($episode_type == 'merged') {
            // --- ADDING A MERGED SEASON FILE ---
            
            // 3a. Validate merged data
            $season_number = $_POST['merged_season'] ?? null;
            $ep_title = $_POST['merged_title'] ?? null;
            $ep_video_url = $_POST['merged_video_url'] ?? null;
            $ep_language = $_POST['merged_language'] ?? null;
            $duration_minutes = $_POST['merged_duration'] ?? null;

            if (empty($season_number) || empty($ep_title) || empty($ep_video_url) || empty($duration_minutes)) {
                throw new Exception("All merged season fields (season, title, URL, duration) are required.");
            }
            $duration_seconds = $duration_minutes * 60;

            // 3b. Create the season
            $stmt = $pdo->prepare("INSERT INTO seasons (movie_id, season_number) VALUES (?, ?)");
            $stmt->execute([$movie_id, $season_number]);
            $season_id = $pdo->lastInsertId();

            // 3c. Add the single merged episode
            $stmt = $pdo->prepare("
                INSERT INTO episodes (movie_id, season_id, episode_number, title, video_url, duration, language)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$movie_id, $season_id, 1, $ep_title, $ep_video_url, $duration_seconds, $ep_language]);

        } else {
            // --- ADDING EPISODIC FILES ---
            
            // 3a. Validate episodic data
            $season_number = $_POST['season_number'] ?? null;
            $ep_titles = $_POST['ep_title'] ?? null;
            $ep_numbers = $_POST['ep_number'] ?? null;
            $ep_video_urls = $_POST['ep_video_url'] ?? null;
            $ep_languages = $_POST['ep_language'] ?? null;
            $ep_durations = $_POST['ep_duration'] ?? null;

            if (empty($season_number) || empty($ep_titles) || !is_array($ep_titles) || empty($ep_titles[0])) {
                throw new Exception("At least one episode is required for the season.");
            }

            // 3b. Create the season
            $stmt = $pdo->prepare("INSERT INTO seasons (movie_id, season_number) VALUES (?, ?)");
            $stmt->execute([$movie_id, $season_number]);
            $season_id = $pdo->lastInsertId();

            // 3c. Loop through each episode and add it
            $stmt = $pdo->prepare("
                INSERT INTO episodes (movie_id, season_id, episode_number, title, video_url, duration, language)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            for ($i = 0; $i < count($ep_titles); $i++) {
                // Validate each episode's fields
                if (empty($ep_titles[$i]) || empty($ep_numbers[$i]) || empty($ep_video_urls[$i]) || empty($ep_durations[$i])) {
                    throw new Exception("All fields for Episode " . ($i+1) . " are required.");
                }
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

        // 4. If everything worked, commit the changes
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

