<?php
// --- THIS IS THE FIX ---
// We must start the session *before* db_connect.php is required.
// We also make sure one isn't already active.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- THIS IS THE FIX ---
// We wrap the *entire* script, including the 'require', in a try...catch block.
// This will catch any connection errors from db_connect.php
// AND any query errors from this script.
try {
    require 'db_connect.php';

    $email = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;

    if (!$email || !$password) {
        header('Location: login.php?error=Email and password are required.');
        exit;
    }

    $stmt = $pdo->prepare("SELECT user_id, email, password_hash, is_admin FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if user exists AND has a password
    if ($user && $user['password_hash'] && password_verify($password, $user['password_hash'])) {
        // Password is correct!
        
        // Regenerate session ID for security
        session_regenerate_id(true); 
        
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['is_admin'] = $user['is_admin'];

        // Redirect admin to admin panel, others to profile
        if ($user['is_admin']) {
            header('Location: admin.php');
            exit;
        } else {
            header('Location: profile.php');
            exit;
        }
    } else {
        // Invalid email or password
        header('Location: login.php?error=Invalid email or password.');
        exit;
    }

} catch (PDOException $e) {
    // This one catch block will now handle *all* database errors,
    // including the connection error from db_connect.php.
    // It will always send a header, never a "die()" message.
    header('Location: login.php?error=' . urlencode($e->getMessage()));
    exit;
}

// The closing "?>" tag is removed to prevent whitespace errors.
