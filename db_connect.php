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
    // DO NOT DIE. Throw an exception that the main script can catch.
    throw new Exception("Database configuration is incomplete. Please check your environment variables in Render.");
}

$dsn = "pgsql:host=$host;port=$port;dbname=$db;user=$user;password=$pass";

// No try/catch block here.
// Let the script that *includes* this file handle connection errors.
// This ensures *this* file never prints output.
$pdo = new PDO($dsn);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// The file ends here. No comments, no closing tag.

