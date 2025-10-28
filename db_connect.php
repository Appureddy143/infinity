<?php
/*
 * YourStream Central Database Connection
 *
 * This file connects to your Neon (PostgreSQL) database.
 * Enter your credentials from the Neon dashboard here.
 * All other PHP files will include this one file.
 */

// --- Database Credentials from Neon ---
$host = 'your-neon-host.db.elephantsql.com'; // Get this from Neon
$db = 'your-database-name';             // Get this from Neon
$user = 'your-username';                 // Get this from Neon
$pass = 'your-password';                 // Get this from Neon
$port = '5432';                        // Default for PostgreSQL

// This is the "DSN" or connection string
$dsn = "pgsql:host=$host;port=$port;dbname=$db;user=$user;password=$pass";

// --- PDO Connection Options ---
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch as associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Use real prepared statements
];

try {
    // Create the PDO database connection
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Stop the script and show a generic error.
    // In a real production app, you would log this error and show a user-friendly page.
    error_log("Database Connection Error: " . $e->getMessage());
    die("Could not connect to the database. Please try again later.");
}
?>
