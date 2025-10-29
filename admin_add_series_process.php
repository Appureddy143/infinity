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
        // 1. Validate Common Series Details
        $title = trim($_POST['series_title'] ?? '');
        $description = trim($_POST['series_description'] ?? '');
        $poster_url = trim($_POST['series_poster_url'] ?? '');
        $genre = trim($_POST['series_genre'] ?? '');
        $episode_type = $_POST['episode_type'] ?? '';
        
        if ($title === '' || $description === '' || $poster_url === '' || $genre === '' || $episode_type === '') {
            throw new Exception("All series fields (title, description, poster, genre, episode type) are required.");
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
            $season_number = trim($_POST['merged_season'] ?? '');
            $ep_title = trim($_POST['merged_title'] ?? '');
            $ep_video_url = trim($_POST['merged_video_url'] ?? '');
            $ep_language = trim($_POST['merged_language'] ?? '');
            $duration_minutes = trim($_POST['merged_duration'] ?? '');
            
            if ($season_number === '' || $ep_title === '' || $ep_video_url === '' || $ep_language === '' || $duration_minutes === '') {
                throw new Exception("All merged season fields (season, title, URL, language, duration) are required.");
            }
            if (!is_numeric($season_number) || !is_numeric($duration_minutes)) {
                throw new Exception("Season number and duration must be numeric.");
            }
            
            $duration_seconds = $duration_minutes * 60;
            
            // 3b. Create the season
            $stmt_season = $pdo->prepare("INSERT INTO seasons (movie_id, season_number) VALUES (?, ?)");
            $stmt_season->execute([$movie_id, $season_number]);
            $season_id = $pdo->lastInsertId();
            
            // 3c. Add the single merged episode
            $stmt_episode = $pdo->prepare("
                INSERT INTO episodes (movie_id, season_id, episode_number, title, video_url, duration, language)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt_episode->execute([$movie_id, $season_id, 1, $ep_title, $ep_video_url, $duration_seconds, $ep_language]);
        } else {
            // --- ADDING EPISODIC FILES ---
            // 3a. Validate episodic data
            $season_number = trim($_POST['season_number'] ?? '');
            $ep_titles = $_POST['ep_title'] ?? [];
            $ep_numbers = $_POST['ep_number'] ?? [];
            $ep_video_urls = $_POST['ep_video_url'] ?? [];
            $ep_languages = $_POST['ep_language'] ?? [];
            $ep_durations = $_POST['ep_duration'] ?? [];
            
            if ($season_number === '' || empty($ep_titles) || !is_array($ep_titles)) {
                throw new Exception("At least one episode (and a season number) is required for the season.");
            }
            if (!is_numeric($season_number)) {
                throw new Exception("Season number must be numeric.");
            }
            
            // 3b. Create the season
            $stmt_season = $pdo->prepare("INSERT INTO seasons (movie_id, season_number) VALUES (?, ?)");
            $stmt_season->execute([$movie_id, $season_number]);
            $season_id = $pdo->lastInsertId();
            
            // 3c. Loop through each episode and add it
            $stmt_episode = $pdo->prepare("
                INSERT INTO episodes (movie_id, season_id, episode_number, title, video_url, duration, language)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            for ($i = 0; $i < count($ep_titles); $i++) {
                // Validate each episode's fields
                $ep_title = trim($ep_titles[$i] ?? '');
                $ep_number = trim($ep_numbers[$i] ?? '');
                $ep_video_url = trim($ep_video_urls[$i] ?? '');
                $ep_language = trim($ep_languages[$i] ?? '');
                $ep_duration = trim($ep_durations[$i] ?? '');
                
                if ($ep_title === '' || $ep_number === '' || $ep_video_url === '' || $ep_language === '' || $ep_duration === '') {
                    throw new Exception("All fields (title, #, URL, language, duration) for Episode " . ($i + 1) . " are required.");
                }
                if (!is_numeric($ep_number) || !is_numeric($ep_duration)) {
                    throw new Exception("Episode number and duration must be numeric for Episode " . ($i + 1) . ".");
                }
                
                $duration_seconds = $ep_duration * 60;
                $stmt_episode->execute([
                    $movie_id, $season_id, $ep_number, $ep_title, $ep_video_url, $duration_seconds, $ep_language
                ]);
            }
        }
        
        // 4. If everything worked, commit the changes
        $pdo->commit();
        header('Location: admin.php?success=Series added successfully!');
        exit;
    } catch (Exception $e) {
        // If anything failed, roll back all changes
        // Check if the transaction is still active before rolling back
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        // Now $e->getMessage() will contain the *real* database error
        // (e.g., "Not null violation") instead of "transaction aborted"
        header('Location: admin.php?error=Failed to add series: ' . urlencode($e->getMessage()));
        exit;
    }
} else {
    header('Location: admin.php');
    exit;
}
?>
