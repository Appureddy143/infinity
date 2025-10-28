<?php
    session_start();

    // --- DATABASE CONNECTION (CONCEPT) ---
    // $dsn = "pgsql:host=...;port=...;dbname=...;user=...;password=...";
    // $pdo = new PDO($dsn);

    $errorMessage = '';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = $_POST['email'];
        $password = $_POST['password'];

        if (empty($email) || empty($password)) {
            $errorMessage = 'Please fill in all fields.';
        } else {
            // 1. Find the user by email
            // $sql = "SELECT id, email, password_hash FROM users WHERE email = ?";
            // $stmt = $pdo->prepare($sql);
            // $stmt->execute([$email]);
            // $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // 2. Verify the password
            // if ($user && password_verify($password, $user['password_hash'])) {
                // Success! Password matches.
                
                // 3. Start the session
                // $_SESSION['user_id'] = $user['id'];
                // $_SESSION['user_email'] = $user['email'];
                
                // 4. Redirect to the homepage
                // header("Location: index.php");
                // exit;
                
            // } else {
                // Invalid email or password
                // $errorMessage = 'Invalid email or password.';
            // }
        }

        // --- Placeholder logic ---
        if ($email === 'user@example.com' && $password === 'password') {
             $_SESSION['user_id'] = 99; // Placeholder ID
             $_SESSION['user_email'] = $email;
             header("Location: index.php");
             exit;
        } else {
             $errorMessage = 'Invalid email or password.';
        }
        // --- End Placeholder ---
        
        // If login failed, redirect back to login.php with an error
        if ($errorMessage) {
            $_SESSION['login_error'] = $errorMessage;
            header("Location: login.php");
            exit;
        }
        
    } else {
        // Not a POST request, just redirect to login
        header("Location: login.php");
        exit;
    }
?>
