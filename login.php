<?php
    session_start();
    // If user is already logged in, redirect to profile
    if (isset($_SESSION['user_id'])) {
        header("Location: profile.php");
        exit;
    }
    $error = $_SESSION['login_error'] ?? null;
    unset($_SESSION['login_error']); // Clear the error after displaying it
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
            <h2 class="text-3xl font-bold text-center text-white mb-8">Login</h2>

            <?php if ($error): ?>
                <div class="bg-red-900 border border-red-700 text-red-100 px-4 py-3 rounded-lg relative mb-6" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form action="login_process.php" method="POST">
                <div class="mb-5">
                    <label for="email" class="block mb-2 text-sm font-medium text-gray-300">Email</label>
                    <input type="email" id="email" name="email" class="bg-zinc-800 border border-zinc-700 text-white text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-3" placeholder="name@company.com" required>
                </div>
                <div class="mb-6">
                    <label for="password" class="block mb-2 text-sm font-medium text-gray-300">Password</label>
                    <input type="password" id="password" name="password" class="bg-zinc-800 border border-zinc-700 text-white text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-3" required>
                </div>
                <button type="submit" class="w-full text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-800 font-medium rounded-lg text-sm px-5 py-3 text-center transition-colors duration-200">
                    Login to your account
                </button>
            </form>

            <p class="text-sm text-center text-gray-400 mt-6">
                Don't have an account? <a href="register.php" class="font-medium text-red-500 hover:underline">Register here</a>
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


