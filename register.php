<?php
    session_start();
    require_once 'db_connect.php';

    // If user is already logged in, redirect to home
    if (isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit;
    }

    $error = '';
    $success = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // --- Validation ---
        if (empty($username) || empty($email) || empty($password)) {
            $error = 'Please fill in all fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email format.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } else {
            try {
                // Check if email already exists
                $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = 'This email address is already registered.';
                } else {
                    // Hash the password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Insert new user (default is_admin to false)
                    $stmt_insert = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                    $stmt_insert->execute([$username, $email, $hashed_password]);

                    // Get the new user ID
                    $user_id = $pdo->lastInsertId();

                    // Automatically log the user in
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['username'] = $username;
                    $_SESSION['is_admin'] = false; // Default for new registration

                    // Redirect to home page
                    header("Location: index.php");
                    exit;
                }
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $error = 'An error occurred during registration. Please try again.';
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - YourStream</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0f0f0f;
            color: #ffffff;
        }
    </style>
</head>
<body class="antialiased">

    <div class="container mx-auto max-w-lg min-h-screen bg-black flex flex-col items-center justify-center p-6">
        
        <h1 class="text-3xl font-bold text-red-500 mb-8">Create Account</h1>
        
        <form action="register.php" method="POST" class="w-full max-w-sm">
            
            <?php if ($error): ?>
                <div class="bg-red-900 border border-red-700 text-red-100 px-4 py-3 rounded-lg relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <div class="mb-4">
                <label for="username" class="block text-sm font-medium text-gray-300 mb-2">Username</label>
                <input type="text" id="username" name="username" class="w-full bg-zinc-800 text-white placeholder-gray-400 rounded-lg py-3 px-4 focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Choose a username" required>
            </div>
            
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                <input type="email" id="email" name="email" class="w-full bg-zinc-800 text-white placeholder-gray-400 rounded-lg py-3 px-4 focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="you@example.com" required>
            </div>
            
            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-300 mb-2">Password</label>
                <input type="password" id="password" name="password" class="w-full bg-zinc-800 text-white placeholder-gray-400 rounded-lg py-3 px-4 focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="••••••••" required>
            </div>
            
            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 rounded-lg text-lg transition-colors duration-200">
                Sign Up
            </button>
        </form>

        <p class="mt-8 text-sm text-gray-400">
            Already have an account? 
            <a href="login.php" class="font-medium text-red-500 hover:text-red-400">Log In</a>
        </p>
    </div>

</body>
</html>


