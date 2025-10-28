<?php
// --- DATABASE LOGIC: UPDATE WATCH HISTORY ---
session_start();

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'User not logged in.']);
    exit;
}

// 2. Check if data was sent via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'POST method required.']);
    exit;
}

// 3. Get the data from the player.js
$userId = $_SESSION['user_id'];
$contentId = filter_input(INPUT_POST, 'content_id', FILTER_VALIDATE_INT);
$watchTime = filter_input(INPUT_POST, 'watch_time', FILTER_VALIDATE_INT);

if (!$contentId || $watchTime === false) { // 0 is a valid watch time
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid content_id or watch_time.']);
    exit;
}

// 4. Connect to your Neon (PostgreSQL) database
//    $pdo = new PDO($dsn, ...);

/*
// 5. Use UPSERT logic: UPDATE the row if it exists, or INSERT if it doesn't
//    This is more efficient than checking with SELECT first.
//    PostgreSQL UPSERT (INSERT ... ON CONFLICT) is perfect here.

$sql = "INSERT INTO watch_history (user_id, content_id, watch_time, last_watched)
        VALUES (?, ?, ?, NOW())
        ON CONFLICT (user_id, content_id) 
        DO UPDATE SET
            watch_time = EXCLUDED.watch_time,
            last_watched = NOW()";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId, $contentId, $watchTime]);
    
    // Send a success response
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => "History updated to ${watchTime}s for content ${contentId}"]);

} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

*/

// Placeholder success response (remove this when you add DB logic)
http_response_code(200);
echo json_encode(['success' => true, 'message' => "History updated to ${watchTime}s for content ${contentId}"]);
?>
