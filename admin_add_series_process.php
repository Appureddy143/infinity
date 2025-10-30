<?php
// This must be at the very top, before any HTML.
// We must start the session *before* db_connect.php is required.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// We will catch all errors, including connection errors
try {
    require 'db_connect.php';

    // Security Check: Make sure user is an admin
    if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
        header('Location: login.php?error=Access denied. Admins only.');
        exit;
    }

    // --- NEW, STRICTER VALIDATION ---

    // 1. Check top-level series details
    $series_title = trim($_POST['series_title'] ?? '');
    $series_description = trim($_POST['series_description'] ?? '');
    $series_poster_url = trim($_POST['series_poster_url'] ?? '');
    $series_genre = trim($_POST['series_genre'] ?? '');
    $episode_type = $_POST['episode_type'] ?? '';

    if (empty($series_title)) throw new Exception("Series Title is required.");
    if (empty($series_description)) throw new Exception("Series Description is required.");
    if (empty($series_poster_url)) throw new Exception("Series Poster URL is required.");
    if (empty($series_genre)) throw new Exception("Series Genre is required.");
    if (empty($episode_type)) throw new Exception("Episode Type is required.");

    // This is the new, lowercase value for the 'type' column
    $type = 'series';

    // Start database transaction
    $pdo->beginTransaction();

    // 2. Insert the main series data into 'movies' table
    $sql = "INSERT INTO movies (title, description, poster_url, genre, type, is_series, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $pdo->prepare($sql);
    
    // Check if statement was prepared successfully
    if (!$stmt) {
        throw new Exception("Failed to prepare the movie insertion query.");
    }
    
    $stmt->execute([
        $series_title,
        $series_description,
        $series_poster_url,
        $series_genre,
        $type,
        true
    ]);

    $movieId = $pdo->lastInsertId();
    if (!$movieId) {
        throw new Exception("Failed to get new movie ID after insertion.");
    }

    // 3. Handle different episode types
    if ($episode_type === 'merged') {
        // --- Validation for Merged File ---
        $season_number = filter_var($_POST['merged_season'] ?? null, FILTER_VALIDATE_INT);
        $title = trim($_POST['merged_title'] ?? '');
        $video_url = trim($_POST['merged_video_url'] ?? '');
        $language = trim($_POST['merged_language'] ?? '');
        $duration = filter_var($_POST['merged_duration'] ?? null, FILTER_VALIDATE_INT);

        if ($season_number === false || $season_number < 0) throw new Exception("Invalid Merged Season Number.");
        if (empty($title)) throw new Exception("Merged Season Title is required.");
        if (empty($video_url)) throw new Exception("Merged Video URL is required.");
        if (empty($language)) throw new Exception("Merged Language is required.");
        if ($duration === false || $duration <= 0) throw new Exception("Invalid Merged Duration.");

        // Insert the single season
        $sql_season = "INSERT INTO seasons (movie_id, season_number, title) VALUES (?, ?, ?)";
        $stmt_season = $pdo->prepare($sql_season);
        if (!$stmt_season) throw new Exception("Failed to prepare season query.");
        $stmt_season->execute([$movieId, $season_number, $title]);
        $seasonId = $pdo->lastInsertId();
        if (!$seasonId) throw new Exception("Failed to get new season ID.");

        // Insert the single "episode"
        $sql_ep = "INSERT INTO episodes (season_id, episode_number, title, video_url, language, duration_seconds) 
                   VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_ep = $pdo->prepare($sql_ep);
        if (!$stmt_ep) throw new Exception("Failed to prepare merged episode query.");
        $stmt_ep->execute([$seasonId, 1, $title, $video_url, $language, $duration * 60]);

    } elseif ($episode_type === 'episodic') {
        // --- Validation for Episodic Files ---
        $season_number = filter_var($_POST['season_number'] ?? null, FILTER_VALIDATE_INT);
        if ($season_number === false || $season_number < 0) throw new Exception("Invalid Season Number.");
        
        // Insert the season
        $sql_season = "INSERT INTO seasons (movie_id, season_number, title) VALUES (?, ?, ?)";
        $stmt_season = $pdo->prepare($sql_season);
        if (!$stmt_season) throw new Exception("Failed to prepare season query.");
        $stmt_season->execute([$movieId, $season_number, "Season " . $season_number]);
        $seasonId = $pdo->lastInsertId();
        if (!$seasonId) throw new Exception("Failed to get new season ID.");

        // Check for episode arrays
        if (!isset($_POST['ep_title']) || !is_array($_POST['ep_title'])) {
            throw new Exception("No episode data was submitted.");
        }

        $ep_titles = $_POST['ep_title'];
        $ep_numbers = $_POST['ep_number'];
        $ep_video_urls = $_POST['ep_video_url'];
        $ep_languages = $_POST['ep_language'];
        $ep_durations = $_POST['ep_duration'];

        $sql_ep = "INSERT INTO episodes (season_id, episode_number, title, video_url, language, duration_seconds) 
                   VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_ep = $pdo->prepare($sql_ep);
        if (!$stmt_ep) throw new Exception("Failed to prepare episodic episodes query.");

        // Loop and validate *each episode*
        for ($i = 0; $i < count($ep_titles); $i++) {
            $title = trim($ep_titles[$i] ?? '');
            $number = filter_var($ep_numbers[$i] ?? null, FILTER_VALIDATE_INT);
            $video_url = trim($ep_video_urls[$i] ?? '');
            $language = trim($ep_languages[$i] ?? '');
            $duration = filter_var($ep_durations[$i] ?? null, FILTER_VALIDATE_INT);

            // Stricter checks
            if (empty($title)) throw new Exception("Episode " . ($i + 1) . " is missing a title.");
            if ($number === false || $number <= 0) throw new Exception("Episode " . ($i + 1) . " has an invalid number.");
            if (empty($video_url)) throw new Exception("Episode " . ($i + 1) . " is missing a video URL.");
            if (empty($language)) throw new Exception("Episode " . ($i + 1) . " is missing a language.");
            if ($duration === false || $duration <= 0) throw new Exception("Episode " . ($i + 1) . " has an invalid duration.");

            // Execute the insertion for this episode
            $stmt_ep->execute([$seasonId, $number, $title, $video_url, $language, $duration * 60]);
        }
    } else {
        throw new Exception("Invalid episode type submitted.");
    }

    // If all checks passed, commit the transaction
    $pdo->commit();
    header('Location: admin.php?success=Series added successfully!');
    exit;

} catch (Exception $e) {
    // Catch *any* exception (PDO or our custom ones)
    // Roll back if a transaction was started
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Redirect back to the add series page with the *real* error message
    header('Location: admin_add_series.php?error=' . urlencode($e->getMessage()));
    exit;
}
// The closing "?>" tag is removed to prevent whitespace errors.

