<?php
    session_start();
    require_once 'db_connect.php';

    // 1. Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        http_response_code(403); // Forbidden
        echo json_encode(['error' => 'User not authenticated']);
        exit;
    }

    // 2. Get the POST data sent from the player
    // We use file_get_contents('php://input') because the player sends JSON
    $input = file_get_contents('php://input');
    if (!$input) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'No data received']);
        exit;
    }
    
    $data = json_decode($input, true);

    // 3. Validate the data
    $user_id = $_SESSION['user_id'];
    $movie_id = $data['movie_id'] ?? null;
    $episode_id = $data['episode_id'] ?? null;
    $currentTime = $data['currentTime'] ?? 0;
    $totalDuration = $data['totalDuration'] ?? 0;

    // We must have at least a movie_id
    if (empty($movie_id)) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'Invalid content ID']);
        exit;
    }

    // Sanitize data
    $movie_id = filter_var($movie_id, FILTER_VALIDATE_INT);
    $episode_id = $episode_id ? filter_var($episode_id, FILTER_VALIDATE_INT) : null;
    $currentTime = filter_var($currentTime, FILTER_VALIDATE_FLOAT);
    $totalDuration = filter_var($totalDuration, FILTER_VALIDATE_FLOAT);

    if ($currentTime <= 0 || $totalDuration <= 0) {
        http_response_code(200); // OK, but nothing to save
        echo json_encode(['message' => 'Nothing to update']);
        exit;
    }

    // 4. Use "UPSERT" to save the history
    // This will INSERT a new row, or UPDATE the existing one if the (user_id, movie_id, episode_id) combination already exists.
    // Note: The (episode_id IS NULL) logic is for handling potential NULL values in the unique constraint.
    
    $sql = "";
    if ($episode_id) {
        // --- This is a SERIES ---
        $sql = "
            INSERT INTO watch_history (user_id, movie_id, episode_id, progress_seconds, total_duration_seconds, last_watched_at)
            VALUES (:user_id, :movie_id, :episode_id, :progress, :duration, NOW())
            ON CONFLICT (user_id, movie_id, episode_id) 
            DO UPDATE SET
                progress_seconds = EXCLUDED.progress_seconds,
                total_duration_seconds = EXCLUDED.total_duration_seconds,
                last_watched_at = NOW();
        ";
    } else {
        // --- This is a MOVIE ---
        // We need to handle the unique constraint where episode_id IS NULL
        $sql = "
            INSERT INTO watch_history (user_id, movie_id, episode_id, progress_seconds, total_duration_seconds, last_watched_at)
            VALUES (:user_id, :movie_id, NULL, :progress, :duration, NOW())
            ON CONFLICT (user_id, movie_id) 
            WHERE episode_id IS NULL
            DO UPDATE SET
                progress_seconds = EXCLUDED.progress_seconds,
                total_duration_seconds = EXCLUDED.total_duration_seconds,
                last_watched_at = NOW();
        ";
    }

    try {
        $stmt = $pdo->prepare($sql);
        
        $params = [
            ':user_id' => $user_id,
            ':movie_id' => $movie_id,
            ':progress' => $currentTime,
            ':duration' => $totalDuration
        ];
        
        if ($episode_id) {
            $params[':episode_id'] = $episode_id;
        }

        $stmt->execute($params);

        http_response_code(200); // OK
        echo json_encode(['status' => 'success', 'message' => 'History updated.']);

    } catch (PDOException $e) {
        error_log($e->getMessage());
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'Database error while saving history.']);
    }

?>