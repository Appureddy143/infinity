<?php
    session_start();
    require_once 'db_connect.php';

    $user_id = $_SESSION['user_id'] ?? null;
    $username = $_SESSION['username'] ?? 'Guest';
    $movie = null;
    $error = '';

    // 1. Validate Movie ID
    if (!isset($_GET['movie_id'])) {
        $error = "No movie selected.";
    } else {
        $movie_id = intval($_GET['movie_id']);
        
        // 2. Fetch movie data
        try {
            $stmt = $pdo->prepare("SELECT * FROM movies WHERE movie_id = ? AND type = 'movie'");
            $stmt->execute([$movie_id]);
            $movie = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$movie) {
                $error = "Movie not found.";
            }

        } catch (PDOException $e) {
            error_log($e->getMessage());
            $error = "A database error occurred.";
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $movie ? htmlspecialchars($movie['title']) : 'Movie Details'; ?> - YourStream</title>
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
    <main class="container mx-auto max-w-lg p-4">

        <?php if ($error): ?>
            <div class="text-center text-red-400 p-4">
                <p><?php echo htmlspecialchars($error); ?></p>
                <a href="index.php" class="text-red-500 hover:text-red-400 mt-2 block">&larr; Back to Home</a>
            </div>
        <?php elseif ($movie): ?>
            <!-- Movie Poster -->
            <div class="relative rounded-lg overflow-hidden shadow-lg">
                <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="w-full h-auto object-cover" onerror="this.src='httpsRead.co/400x600/000000/ffffff?text=Error'">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
                <h1 class="absolute bottom-4 left-4 text-3xl font-bold"><?php echo htmlspecialchars($movie['title']); ?></h1>
            </div>

            <!-- Play Button -->
            <a href="player.php?movie_id=<?php echo htmlspecialchars($movie['movie_id']); ?>" class="block w-full bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-4 rounded-lg mt-4 text-center text-lg transition-colors duration-200">
                Play
            </a>

            <!-- Details -->
            <div class="mt-6">
                <p class="text-gray-300"><?php echo htmlspecialchars($movie['description']); ?></p>

                <div class="flex flex-wrap gap-4 mt-4 text-sm text-gray-400">
                    <span class="font-semibold"><?php echo date('Y', strtotime($movie['release_date'])); ?></span>
                    <span>|</span>
                    <span class="font-semibold"><?php echo htmlspecialchars($movie['genre']); ?></span>
                    <span>|</span>
                    <span class="font-semibold"><?php echo round($movie['duration_seconds'] / 60); ?> minutes</span>
                    <span>|</span>
                    <span class="font-semibold"><?php echo htmlspecialchars($movie['language']); ?></span>
                </div>
            </div>
        <?php endif; ?>

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
            <a href="<?php echo $user_id ? 'profile.php' : 'login.php'; ?>" class="flex flex-col items-center justify-center text-gray-400 hover:text-red-500 transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="text-xs font-medium mt-1"><?php echo $user_id ? 'Profile' : 'Login'; ?></span>
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


