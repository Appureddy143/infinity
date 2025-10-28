<?php
session_start();
require 'db_connect.php'; // This connects to your Neon database

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Check if email and password are set
    if (!isset($_POST['email']) || !isset($_POST['password'])) {
        header('Location: login.php?error=Missing email or password.');
        exit;
    }

    $email = $_POST['email'];
    $password = $_POST['password'];

    // 1. Find the user by email
    try {
        $stmt = $pdo->prepare("SELECT user_id, email, password_hash, is_admin FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. Verify the user and password
        // FIX: Check if $user was found AND if password_hash is not null
        // This prevents the "Passing null to parameter #2" error.
        if ($user && isset($user['password_hash']) && password_verify($password, $user['password_hash'])) {
            
            // Password is correct!
            // 3. Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['is_admin'] = (bool)$user['is_admin'];

            // 4. Redirect to the appropriate page
            if ($_SESSION['is_admin']) {
                header('Location: admin.php');
            } else {
                header('Location: profile.php');
            }
            exit;

        } else {
            // Invalid email or password
            header('Location: login.php?error=Invalid email or password.');
            exit;
        }

    } catch (PDOException $e) {
        // Database error
        // In production, you'd log this error instead of showing it
        header('Location: login.php?error=A database error occurred.');
        // error_log($e->getMessage()); // For logging
        exit;
    }

} else {
    // If someone tries to access this page directly
    header('Location: login.php');
    exit;
}
?>