<?php
// Start session at the very top
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Wrap *everything* in try/catch blocks
try {
    
    // Try to connect to the database. 
    // This will now throw an exception if it fails (from db_connect.php)
    require 'db_connect.php';

    // This logic only runs if the connection was successful.
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    // Use name="email" from the form
    $email = trim($_POST['email'] ?? '');
    // Use name="password" from the form
    $password = $_POST['password'] ?? ''; 

    if (empty($email) || empty($password)) {
        throw new Exception('Email and password are required.');
    }

    // Find the user by email
    $stmt = $pdo->prepare("SELECT user_id, email, password_hash, is_admin FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if user exists AND if password is correct
    if (!$user || !isset($user['password_hash']) || !password_verify($password, $user['password_hash'])) {
        throw new Exception('Invalid email or password.');
    }

    // Password is correct! Regenerate session ID
    session_regenerate_id(true); 

    // Set session variables
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['is_admin'] = (bool) $user['is_admin'];

    // Redirect based on admin status
    if ($_SESSION['is_admin']) {
        header('Location: admin.php');
    } else {
        header('Location: profile.php');
    }
    exit; 

} catch (PDOException | Exception $e) {
    // Catch *any* error (from db_connect.php or from logic above)
    // and redirect cleanly without any output.
    $error_message = urlencode($e->getMessage());
    header("Location: login.php?error={$error_message}");
    exit;
}