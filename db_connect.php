<?php
// --- THIS IS THE FIX ---
// Only start a new session if one isn't already active.
// This should be the *only* place in your entire app that calls session_start().
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database connection details from Render Environment Variables
$host = getenv('DB_HOST');
$port = getenv('DB_PORT');
$db = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');

// Create the connection string (DSN) for PostgreSQL
$dsn = "pgsql:host=$host;port=$port;dbname=$db;user=$user;password=$pass";

try {
    // Create the PDO database connection
    $pdo = new PDO($dsn);
    
    // Set the PDO error mode to exception
    // This will make your try/catch blocks work correctly
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Store the current user in a global variable, if logged in
    $currentUser = null;
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT user_id, email, is_admin FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    // This will stop the script and show a user-friendly error
    die("Could not connect to the database: " . $e->getMessage());
}
?>

