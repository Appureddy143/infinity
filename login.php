<?php
session_start();
// If already logged in, redirect to profile
if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit;
}
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - YourStream</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #111827; /* Dark background */
        }
        .bottom-nav {
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.5);
        }
        .search-bar {
            transition: max-height 0.3s ease-out, opacity 0.3s ease-out;
        }
    </style>
</head>
<body class="text-white">

    <div class="container mx-auto max-w-lg p-4 min-h-screen flex flex-col justify-center">

        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-white mb-2">Login to YourStream</h1>
            <p class="text-gray-400">Welcome back!</p>
        </div>

        <form action="login_process.php" method="POST" class="bg-gray-800 p-6 rounded-lg shadow-lg">
            
            <?php if ($error): ?>
                <div class="bg-red-500 text-white p-3 rounded-md mb-4 text-center">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                <input type="email" id="email" name="email" class="w-full p-3 bg-gray-700 rounded-md border border-gray-600 focus:outline-none focus:ring-2 focus:ring-red-500 text-white" required>
            </div>
            
            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-300 mb-2">Password</label>
                <!-- 
                  FIX: Added name="password" here. 
                  This was the cause of the "Undefined array key" error.
                -->
                <input type="password" id="password" name="password" class="w-full p-3 bg-gray-700 rounded-md border border-gray-600 focus:outline-none focus:ring-2 focus:ring-red-500 text-white" required>
            </div>
            
            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-md transition duration-300">Login</button>
        </form>

        <p class="text-center text-gray-400 mt-6">
            Don't have an account? 
            <a href="register.php" class="text-red-500 hover:underline font-medium">Sign up here</a>
        </p>

    </div>

    <!-- Bottom Navigation Bar (Consistent with other pages) -->
    <nav class="bottom-nav fixed bottom-0 left-0 right-0 h-16 bg-gray-900 border-t border-gray-700 flex justify-around items-center">
        <!-- Home -->
        <a href="index.php" class="flex flex-col items-center text-gray-400 hover:text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1V9a1 1 0 011-1h2a1 1 0 011 1v10a1 1 0 001 1m-6 0h6"></path></svg>
            <span class="text-xs">Home</span>
        </a>
        
        <!-- Profile/Login -->
        <a href="login.php" class="flex flex-col items-center text-red-500 font-medium"> <!-- Active Link -->
             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            <span class="text-xs">Login</span>
        </a>
    </nav>
</body>
</html>


