<?php
    session_start();
    require_once 'db_connect.php';

    $user_id = $_SESSION['user_id'] ?? null;
    $username = $_SESSION['username'] ?? 'Guest';
    $results = [];
    $search_query = '';
    $error = '';

    if (isset($_GET['q'])) {
        $search_query = trim($_GET['q']);

        if (empty($search_query)) {
            $error = "Please enter a search term.";
        } else {
            try {
                // Use ILIKE for case-insensitive search (PostgreSQL specific)
                // Use LIKE for MySQL
                $stmt = $pdo->prepare("
                    SELECT movie_id, title, poster_url, type, language, description
                    FROM movies 
                    WHERE title ILIKE ? OR description ILIKE ? OR genre ILIKE ?
                    ORDER BY release_date DESC
                ");
                $stmt->execute(['%' . $search_query . '%', '%' . $search_query . '%', '%' . $search_query . '%']);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            } catch (PDOException $e) {
                error_log($e->getMessage());
                $error = "A database error occurred during the search.";
            }
        }
    }

    // Helper function to create the link
    function get_movie_link($item) {
        if ($item['type'] == 'series') {
            return "series.php?movie_id=" . htmlspecialchars($item['movie_id']);
        }
        return "details.php?movie_id=" . htmlspecialchars($item['movie_id']);
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - YourStream</title>
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
            <button id="search-btn" class="text-red-500"> <!-- Active -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </button>
        </div>
    </header>
    
    <!-- Search Bar (Visible by default on this page) -->
    <div id="search-bar" class="bg-zinc-900 p-4 sticky top-[64px] z-40">
        <form action="search.php" method="GET" class="container mx-auto max-w-lg">
            <input type="search" name="q" class="w-full bg-zinc-800 text-white placeholder-gray-400 rounded-lg py-3 px-4 focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Search for movies or series..." value="<?php echo htmlspecialchars($search_query); ?>">
        </form>
    </div>

    <!-- Main Content -->
    <main class="container mx-auto max-w-lg p-4">
        
        <h2 class="text-xl font-semibold mb-4">
            <?php if (!empty($search_query)): ?>
                Search results for "<?php echo htmlspecialchars($search_query); ?>"
            <?php else: ?>
                Search
            <?php endif; ?>
        </h2>

        <?php if ($error): ?>
            <p class="text-red-400"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <?php if (empty($results) && !empty($search_query) && !$error): ?>
            <p class="text-gray-400">No results found for "<?php echo htmlspecialchars($search_query); ?>".</p>
        <?php endif; ?>

        <!-- Results Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            <?php foreach ($results as $item): ?>
                <a href="<?php echo get_movie_link($item); ?>" class="bg-zinc-900 rounded-lg overflow-hidden shadow-md hover:bg-zinc-800 transition-colors duration-200">
                    <div class="relative">
                        <img src="<?php echo htmlspecialchars($item['poster_url']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="w-full h-48 object-cover" onerror="this.src='httpsRead.co/200x288/000000/ffffff?text=Error'">
                        <span class="absolute top-2 left-2 bg-red-600/80 backdrop-blur-sm text-white text-xs font-semibold px-2 py-0.5 rounded-md"><?php echo htmlspecialchars($item['language']); ?></span>
                    </div>
                    <div class="p-3">
                        <h3 class="font-semibold text-sm truncate"><?php echo htmlspecialchars($item['title']); ?></h3>
                        <p class="text-xs text-gray-400 capitalize"><?php echo htmlspecialchars($item['type']); ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
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
            
            // Toggle search bar
            searchBtn.addEventListener('click', () => {
                searchBar.classList.toggle('hidden');
            });

            // Focus the search input on this page
            const searchInput = searchBar.querySelector('input[type="search"]');
            if (searchInput) {
                searchInput.focus();
            }
        });
    </script>
</body>
</html>


