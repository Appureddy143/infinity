<?php
session_start();
require_once 'db_connect.php'; // Use our new connection file

// Get form data
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    // Redirect back with an error
    header('Location: login.php?error=empty');
    exit;
}

try {
    // Find the user by email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Check if user exists and password is correct
    if ($user && password_verify($password, $user['password_hash'])) {
        
        // --- LOGIN SUCCESSFUL ---
        
        // Store user info in session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        
        // NEW: Check for admin status
        if ($user['is_admin'] === true) {
            $_SESSION['is_admin'] = true;
        } else {
            $_SESSION['is_admin'] = false;
        }

        // Redirect to homepage
        header('Location: index.php');
        exit;

    } else {
        // Invalid credentials
        header('Location: login.php?error=invalid');
        exit;
    }

} catch (PDOException $e) {
    // Database error
    error_log($e->getMessage());
    header('Location: login.php?error=db');
    exit;
}
?>