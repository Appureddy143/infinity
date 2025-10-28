<?php
    session_start();
    // If user is already logged in, redirect to profile
    if (isset($_SESSION['user_id'])) {
        header("Location: profile.php");
        exit;
    }
    
    require_once 'db_connect.php';
    $error = '';
    $success = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $password_confirm = $_POST['password_confirm'];

        if (empty($username) || empty($email) || empty($password)) {
            $error = "Please fill in all fields.";
        } elseif ($password !== $password_confirm) {
            $error = "Passwords do not match.";
        } elseif (strlen($password) < 8) {
            $error = "Password must be at least 8 characters long.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format.";
        } else {
            try {
                // Check if email already exists
                $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = "An account with this email already exists.";
                } else {
                    // Hash the password
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert new user
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
                    $stmt->execute([$username, $email, $password_hash]);
                    
                    $success = "Registration successful! You can now log in.";
                    // Optionally, log the user in directly
                    // $_SESSION['user_id'] = $pdo->lastInsertId();
                    // $_SESSION['username'] = $username;
                    // $_SESSION['is_admin'] = false; // Default
                    // header("Location: index.php");
                    // exit;
                }
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $error = "A database error occurred. Please try again.";
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
        body { font-family: 'Inter', sans-serif; background-color: #0f0f0f; color: #ffffff; }
    </style>
</head>
<body class="antialiased pb-24">

    <!-- Header -->
    <header class="bg-black sticky top-0 z-50 py-4 px-4 shadow-lg shadow-zinc-900/50">
        <div class="container mx-auto max-w-lg flex justify-between items-center">
            <h1 class="text-2xl font-bold text-red-500">YourStream</h1>
            <!-- Search Icon -->
            <button id="search-btn" class="text-white hover:text-red-500 transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </button>
        </div>
    </header>
    
    <!-- Search Bar (Hidden by default) -->
    <div id="search-bar" class="hidden bg-zinc-900 p-4 sticky top-[64px] z-40">
        <form action="search.php" method="GET" class="container mx-auto max-w-lg">
            <input type="search" name="q" class="w-full bg-zinc-800 text-white placeholder-gray-400 rounded-lg py-3 px-4 focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Search for movies or series...">
        </form>
    </div>

    <!-- Main Content -->
    <main class="container mx-auto max-w-lg p-4 pt-12">
        <div class="bg-black p-8 rounded-lg shadow-2xl max-w-sm mx-auto">
            <h2 class="text-3xl font-bold text-center text-white mb-8">Create Account</h2>

            <?php if ($error): ?>
                <div class="bg-red-900 border border-red-700 text-red-100 px-4 py-3 rounded-lg relative mb-6" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div classm="bg-green-900 border border-green-700 text-green-100 px-4 py-3 rounded-lg relative mb-6" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($success); ?></span>
                    <a href="login.php" class="block text-center mt-2 font-semibold text-green-200 hover:text-white">Go to Login &rarr;</a>
                </div>
            <?php else: ?>
                <form action="register.php" method="POST">
                    <div class="mb-5">
                        <label for="username" class="block mb-2 text-sm font-medium text-gray-300">Username</label>
                        <input type="text" id="username" name="username" class="bg-zinc-800 border border-zinc-700 text-white text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-3" placeholder="JohnDoe" required>
                    </div>
                    <div class="mb-5">
                        <label for="email" class="block mb-2 text-sm font-medium text-gray-300">Email</lebel>
                        <input type="email" id="email" name="email" class="bg-zinc-800 border border-zinc-700 text-white text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-3" placeholder="name@company.com" required>
                    </div>
                    <div class="mb-5">
                        <label for="password" class="block mb-2 text-sm font-medium text-gray-300">Password</lebel>
                        <input type="password" id="password" name="password" class="bg-zinc-800 border border-zinc-700 text-white text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-3" placeholder="••••••••" required>
                    </div>
                    <div class="mb-6">
                        <label for="password_confirm" class="block mb-2 text-sm font-medium text-gray-300">Confirm Password</lebel>
                        <input type="password" id="password_confirm" name="password_confirm" class="bg-zinc-800 border border-zinc-700 text-white text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-3" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="w-full text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-800 font-medium rounded-lg text-sm px-5 py-3 text-center transition-colors duration-200">
                        Create account
                    </button>
                </form>
            <?php endif; ?>

            <p class="text-sm text-center text-gray-400 mt-6">
                Already have an account? <a href="login.php" class="font-medium text-red-500 hover:underline">Login here</a>
            </p>
        </div>
    </main>

    <!-- Bottom Navigation -->
    <nav class="fixed bottom-0 left-0 right-0 bg-black border-t border-zinc-800 shadow-lg z-50">
        <div class="container mx-auto max-w-lg flex justify-around py-3">
            <!-- Home -->
            <a href="index.php" class="flex flex-col items-center justify-center text-gray-400 hover:text-red-500 transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1V9a1 1 0 011-1h2a1 1 0 011 1v10a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="text-xs font-medium mt-1">Home</span>
            </a>
            
            <!-- Profile -->
            <a href="login.php" class="flex flex-col items-center justify-center text-red-500"> <!-- Active Link -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="text-xs font-medium mt-1">Login</span>
            </a>
        </div>
    </nav>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchBtn = document.getElementById('search-btn');
            const searchBar = document.getElementById('search-bar');
            searchBtn.addEventListener('click', () => {
                searchBar.classList.toggle('hidden');
            });
        });
    </script>
</body>
</html>


