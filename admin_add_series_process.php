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

    // --- THIS IS THE FIX ---
    // Force the connection to throw exceptions on SQL errors.
    // This will stop the "transaction aborted" error and give the real error.
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    try {
        // --- NEW SERVER-SIDE VALIDATION ---
        // 1. Validate Common Series Details
        $title = $_POST['series_title'] ?? null;
        $description = $_POST['series_description'] ?? null;
        $poster_url = $_POST['series_poster_url'] ?? null;
        $genre = $_POST['series_genre'] ?? null;
        $episode_type = $_POST['episode_type'] ?? null;

        // Use more precise checks (not empty()) to allow '0' but not null or ''
        if (!isset($title) || $title === '' || !isset($description) || $description === '' || !isset($poster_url) || $poster_url === '' || !isset($genre) || $genre === '' || !isset($episode_type) || $episode_type === '') {
            throw new Exception("All series fields (title, description, poster, genre) are required.");
        }

        // 2. Insert the main series into the 'movies' table
        $stmt = $pdo->prepare("
            INSERT INTO movies (title, description, poster_url, genre, is_series, type)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        // We no longer need the manual 'if === false' checks because of the new line above.
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

            if (!isset($season_number) || $season_number === '' || !isset($ep_title) || $ep_title === '' || !isset($ep_video_url) || $ep_video_url === '' || !isset($duration_minutes) || $duration_minutes === '' || !isset($ep_language) || $ep_language === '') {
                throw new Exception("All merged season fields (season, title, URL, language, duration) are required.");
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

            if (!isset($season_number) || $season_number === '' || empty($ep_titles) || !is_array($ep_titles) || !isset($ep_titles[0]) || $ep_titles[0] === '') {
                throw new Exception("At least one episode (and a season number) is required for the season.");
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
                if (!isset($ep_titles[$i]) || $ep_titles[$i] === '' || 
                    !isset($ep_numbers[$i]) || $ep_numbers[$i] === '' || 
                    !isset($ep_video_urls[$i]) || $ep_video_urls[$i] === '' || 
                    !isset($ep_durations[$i]) || $ep_durations[$i] === '' ||
                    !isset($ep_languages[$i]) || $ep_languages[$i] === '') {
                    
                    throw new Exception("All fields (title, #, URL, language, duration) for Episode " . ($i+1) . " are required.");
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

