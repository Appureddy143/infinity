<?php
// We need to connect to the DB to get session/user info for the nav bar
require 'db_connect.php';

// Get error message from URL (if any)
$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : null;
?>
<!DOCTYPE html>
<html lang="en" class="bg-gray-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - YourStream</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-900 text-white antialiased">

    <!-- HEADER -->
    <header class="bg-gray-900 p-4 flex justify-between items-center sticky top-0 z-50">
        <h1 class="text-2xl font-bold text-red-600">YourStream</h1>
        <button id="search-icon">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </button>
    </header>

    <!-- Pop-out Search Bar -->
    <div id="search-bar" class="hidden bg-gray-800 p-4 sticky top-[68px] z-50">
        <form action="search.php" method="GET">
            <input type="search" name="q" placeholder="Search for movies or series..." class="w-full bg-gray-700 text-white rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-red-500">
        </form>
    </div>

    <!-- MAIN LOGIN FORM -->
    <main class="pt-10 pb-20 px-4">
        <div class="max-w-md mx-auto bg-gray-800 rounded-lg shadow-lg p-8">
            <h2 class="text-3xl font-bold text-center text-white mb-6">Login</h2>

            <!-- THIS IS THE FIX: Display the error message -->
            <?php if ($error): ?>
                <div class="bg-red-500 text-white p-3 rounded-md mb-6 text-center">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <!-- THIS IS THE FIX: The form 'action' points to the correct file -->
            <form action="login_process.php" method="POST">
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                    <input type="email" id="email" name="email" class="w-full bg-gray-700 text-white rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-red-500" required>
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-2">Password</label>
                    <input type="password" id="password" name="password" class="w-full bg-gray-700 text-white rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-red-500" required>
                </div>
                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-lg transition duration-300">
                    Login
                </button>
            </form>

            <p class="text-center text-gray-400 mt-6">
                Don't have an account? 
                <a href="register.php" class="text-red-500 hover:text-red-400 font-medium">Sign up</a>
            </p>
        </div>
    </main>

    <!-- BOTTOM NAVIGATION -->
    <nav class="fixed bottom-0 left-0 right-0 bg-gray-900 border-t border-gray-700 flex justify-around p-3 z-50">
        <a href="index.php" class="flex flex-col items-center text-gray-400 hover:text-white">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6-4a1 1 0 001-1v-1a1 1 0 10-2 0v1a1 1 0 001 1z" />
            </svg>
            <span class="text-xs">Home</span>
        </a>
        <a href="profile.php" class="flex flex-col items-center text-white">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span class="text-xs"><?= isset($currentUser['email']) ? 'Profile' : 'Login' ?></span>
        </a>
    </nav>

    <script>
        // Toggle search bar
        document.getElementById('search-icon').addEventListener('click', function() {
            document.getElementById('search-bar').classList.toggle('hidden');
        });
    </script>
</body>
</html>


