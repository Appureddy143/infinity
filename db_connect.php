<?php
// FILE: db_connect.php
// This file connects to your Neon database.
// It uses Environment Variables set in Render for security.

// 1. Get database credentials from Render Environment Variables
$host = getenv('DB_HOST');
$port = getenv('DB_PORT');
$db = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');

// 2. Create the DSN (Data Source Name) string
//
// **UPDATED**
// We are adding "sslmode=require" to ensure a secure
// connection, just as your Neon string requires.
//
$dsn = "pgsql:host=$host;port=$port;dbname=$db;user=$user;password=$pass;sslmode=require";

// 3. Set PDO options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch as associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Use real prepared statements
];

// 4. Try to connect
try {
     $pdo = new PDO($dsn, null, null, $options);
} catch (PDOException $e) {
     // If connection fails, stop the script and show an error.
     // In a real production app, you'd log this error instead.
     die("Could not connect to the database: " . $e->getMessage());
}

// If we are here, the $pdo variable is now ready and
// can be used by any file that 'includes' this one.
?>

