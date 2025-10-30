<?php
// Only start a new session if one isn't already active.
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

// We let the script *calling* this file (e.g., login_process.php)
// handle the connection error. This prevents the "die()" command
// from printing output and causing the "headers already sent" error.

// Create the PDO database connection
$pdo = new PDO($dsn);

// Set the PDO error mode to exception
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Store the current user in a global variable, if logged in
$currentUser = null;
if (isset($_SESSION['user_id'])) {
    // We wrap this in a try...catch in case the DB connection
    // was successful but the user table has an issue.
    try {
        $stmt = $pdo->prepare("SELECT user_id, email, is_admin FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Don't die, just fail gracefully.
        $currentUser = null;
    }
}
// --- THIS IS THE FIX ---
// The file now ends *exactly* here.
// There are no comments, blank lines, or closing tags after this point.
