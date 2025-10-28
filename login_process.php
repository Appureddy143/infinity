<?php
    session_start();
    require_once 'db_connect.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            header("Location: login.php?error=1"); // Invalid fields
            exit;
        }

        try {
            // Find the user by email
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // Verify user exists and password is correct
            if ($user && password_verify($password, $user['password'])) {
                // Password is correct! Start the session.
                session_regenerate_id(true); // Security measure
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_admin'] = (bool)$user['is_admin'];

                // Redirect to the homepage (logged in)
                header("Location: index.php");
                exit;

            } else {
                // Invalid email or password
                header("Location: login.php?error=1");
                exit;
            }

        } catch (PDOException $e) {
            error_log($e->getMessage());
            // Database error
            header("Location: login.php?error=2");
            exit;
        }
    } else {
        // Not a POST request, redirect home
        header("Location: index.php");
        exit;
    }
?>