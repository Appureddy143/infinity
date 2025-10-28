<?php
// Turn on error reporting
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Start the session on every page that includes this file
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get database credentials from Render Environment Variables
$host = getenv('DB_HOST');
$port = getenv('DB_PORT');
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');

// Create the DSN (Data Source Name) for PostgreSQL
// We also include sslmode=require as Neon requires SSL.
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$pass;sslmode=require";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, null, null, $options);
} catch (\PDOException $e) {
     // If connection fails, stop the script and show an error.
     // In a real production app, you might show a friendlier error page.
     // The (int) cast is a good security practice.
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Helper function to get logged-in user (can be null)
function getLoggedInUser($pdo) {
    if (isset($_SESSION['user_id'])) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Handle error, maybe log it
            return null;
        }
    }
    return null;
}

// Get the current user for all pages
$currentUser = getLoggedInUser($pdo);
// FIX: Removed all whitespace, blank lines, and comments after this closing tag.
?>