<?php
// Start the session (required for session-based authentication)
session_start();

// Database connection details (replace with your actual Neon credentials)
$host = getenv('DB_HOST') ?: 'your_default_host';  // Use env vars for security
$port = getenv('DB_PORT') ?: 5432;
$dbname = getenv('DB_NAME') ?: 'your_default_db';
$username = getenv('DB_USER') ?: 'your_default_user';
$password = getenv('DB_PASS') ?: 'your_default_pass';

try {
    // Create PDO connection
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $username, $password);
    
    // Force PDO to throw exceptions on ALL database errors (this fixes the "transaction aborted" issue)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Optional: Set default fetch mode for consistency
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle connection errors gracefully
    die("Database connection failed: " . $e->getMessage());
}

// Optional: Fetch current user if logged in (for use in other files)
$currentUser = null;
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT id, email, is_admin FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $currentUser = $stmt->fetch();
    } catch (PDOException $e) {
        // Log error if needed, but don't die here
        error_log("Error fetching user: " . $e->getMessage());
    }
}
?>
