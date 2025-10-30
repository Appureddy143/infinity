<?php
// This must be at the very top, before any HTML.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$host = getenv('DB_HOST');
$port = getenv('DB_PORT');
$db = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');

// Validate that environment variables were loaded
if (empty($host) || empty($port) || empty($db) || empty($user) || empty($pass)) {
    die("Database configuration is incomplete. Please check your environment variables in Render.");
}

$dsn = "pgsql:host=$host;port=$port;dbname=$db;user=$user;password=$pass";

try {
    // Create the PDO object
    $pdo = new PDO($dsn);
    
    // --- THIS IS THE PERMANENT FIX ---
    // Set the error mode to throw exceptions immediately.
    // This MUST be done in this file, right after connection.
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // --- END OF FIX ---
    
} catch (PDOException $e) {
    // If the connection *itself* fails, stop everything.
    // We use htmlspecialchars to prevent XSS in the error message.
    die("Database connection failed: " . htmlspecialchars($e->getMessage()));
}

// The closing "?>" tag is removed to prevent whitespace errors.
