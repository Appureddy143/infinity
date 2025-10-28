<?php
    session_start();
    require_once 'db_connect.php';

    $user_id = $_SESSION['user_id'] ?? null;
    $username = $_SESSION['username'] ?? 'Guest';

    $new_releases = [];
    $all_time_hits = [];
    $kannada_movies = [];
    $telugu_movies = [];
    $multi_lang_movies = [];
    $continue_watching = [];

    try {
        // --- Get Continue Watching (for logged in user) ---
        if ($user_id) {
            $stmt_cw = $pdo->prepare("
                SELECT 
                    m.movie_id, m.title, m.poster_url, m.type,
                    wh.progress_seconds, wh.total_duration_seconds, wh.episode_id,
                    e.title AS episode_title
                FROM watch_history wh
                JOIN movies m ON wh.movie_id = m.movie_id
                LEFT JOIN episodes e ON wh.episode_id = e.episode_id
                WHERE wh.user_id = ? 
                AND (wh.progress_seconds / wh.total_duration_seconds) < 0.95 -- Not completed
                ORDER BY wh.last_watched_at DESC
                LIMIT 5
            ");
            $stmt_cw->execute([$user_id]);
            $continue_watching = $stmt_cw->fetchAll(PDO::FETCH_ASSOC);
        }

        // --- Get New Releases (Latest 10) ---
        $stmt_new = $pdo->query("
            SELECT movie_id, title, poster_url, type, language 
            FROM movies 
            ORDER BY release_date DESC, created_at DESC 
            LIMIT 10
        ");
        $new_releases = $stmt_new->fetchAll(PDO::FETCH_ASSOC);
        
        // --- Get All Time Hits (Most Watched) ---
        $stmt_hits = $pdo->query("
            SELECT 
                m.movie_id, m.title, m.poster_url, m.type, m.language,
                COUNT(wh.watch_history_id) AS watch_count
            FROM movies m
            JOIN watch_history wh ON m.movie_id = wh.movie_id
            GROUP BY m.movie_id
            ORDER BY watch_count DESC
            LIMIT 10
        ");
        $all_time_hits = $stmt_hits->fetchAll(PDO::FETCH_ASSOC);
        if (empty($all_time_hits)) {
            // Fallback if no watch history exists
            $stmt_hits = $pdo->query("SELECT movie_id, title, poster_url, type, language FROM movies ORDER BY RANDOM() LIMIT 10");
            $all_time_hits = $stmt_hits->fetchAll(PDO::FETCH_ASSOC);
        }

        // --- Get By Language ---
        $stmt_kan = $pdo->prepare("SELECT movie_id, title, poster_url, type, language FROM movies WHERE language = ? LIMIT 10");
        $stmt_kan->execute(['Kannada']);
        $kannada_movies = $stmt_kan->fetchAll(PDO::FETCH_ASSOC);

        $stmt_tel = $pdo->prepare("SELECT movie_id, title, poster_url, type, language FROM movies WHERE language = ? LIMIT 10");
        $stmt_tel->execute(['Telugu']);
        $telugu_movies = $stmt_tel->fetchAll(PDO::FETCH_ASSOC);

        $stmt_multi = $pdo->prepare("SELECT movie_id, title, poster_url, type, language FROM movies WHERE language = ? LIMIT 10");
        $stmt_multi->execute(['Multi-language']);
        $multi_lang_movies = $stmt_multi->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log($e->getMessage());
        // You could set a user-facing error here
    }

    // Helper function to create the link
    function get_movie_link($item) {
        if ($item['type'] == 'series') {
            return "series.php?movie_id=" . htmlspecialchars($item['movie_id']);
        }
        return "details.php?movie_id=" . htmlspecialchars($item['movie_id']);
    }

    // Helper function for continue watching progress
    function get_progress_percent($current, $total) {
        if (!$total || $total == 0) return 0;
        return round(($current / $total) * 100);
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YourStream - Mobile Streaming</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0f0f0f; /* Darker background */
            color: #ffffff;
        }
        .movie-card {
            flex: 0 0 auto;
            width: 150px; /* 9.375rem */
            border-radius: 0.5rem; /* 8px */
            overflow: hidden;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            position: relative;
        }
        .movie-card:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.15);
        }
        .movie-poster {
            width: 150px;
            height: 225px; /* 14.0625rem */
            object-fit: cover;
            background-color: #1f2937; /* gray-800 */
        }
        .movie-title {
            margin-top: 0.5rem;
            font-size: 0.875rem; /* 14px */
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .lang-badge {
            position: absolute;
            top: 0.5rem;
            left: 0.5rem;
            background-color: rgba(239, 68, 68, 0.8); /* red-500 with opacity */
            backdrop-filter: blur(4px);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.75rem; /* 12px */
            font-weight: 600;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .scroll-container {
            display: flex;
            overflow-x: auto;
            padding: 1rem 0;
            gap: 1rem;
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
        .scroll-container::-webkit-scrollbar {
            display: none; /* Chrome, Safari, Opera */
        }
        .section-title {
            font-size: 1.25rem; /* 20px */
            font-weight: 600;
            margin-bottom: 0.5rem;
            padding-left: 1rem; /* Align with page padding */
        }
        .progress-bar-bg { background-color: #404040; } /* neutral-700 */
        .progress-bar-fg { background-color: #ef4444; } /* red-500 */
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
    <main class="container mx-auto max-w-lg space-y-6">
        
        <!-- Continue Watching -->
        <?php if ($user_id && !empty($continue_watching)): ?>
        <section class="mt-4">
            <h2 class="section-title">Continue Watching</h2>
            <div class="scroll-container px-4">
                <?php foreach ($continue_watching as $item): ?>
                    <?php 
                        $progress = get_progress_percent($item['progress_seconds'], $item['total_duration_seconds']);
                        $link = $item['episode_id'] 
                                ? "player.php?episode_id=" . htmlspecialchars($item['episode_id'])
                                : "player.php?movie_id=" . htmlspecialchars($item['movie_id']);
                    ?>
                    <a href="<?php echo $link; ?>" class="movie-card">
                        <div class="relative">
                            <img src="<?php echo htmlspecialchars($item['poster_url']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="movie-poster" onerror="this.src='httpsRead.co/150x225/000000/ffffff?text=Error'">
                            <div class="absolute bottom-0 left-0 right-0 w-full p-2 bg-gradient-to-t from-black/80 to-transparent">
                                <div class="w-full progress-bar-bg rounded-full h-1.5">
                                    <div class="progress-bar-fg h-1.5 rounded-full" style="width: <?php echo $progress; ?>%"></div>
                                </div>
                            </div>
                        </div>
                        <h3 class="movie-title px-1"><?php echo htmlspecialchars($item['title']); ?></h3>
                        <?php if ($item['episode_title']): ?>
                            <p class="text-xs text-gray-400 px-1 truncate"><?php echo htmlspecialchars($item['episode_title']); ?></p>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- New Releases -->
        <section>
            <h2 class="section-title">New Releases</h2>
            <div class="scroll-container px-4">
                <?php foreach ($new_releases as $movie): ?>
                    <a href="<?php echo get_movie_link($movie); ?>" class="movie-card">
                        <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="movie-poster" onerror="this.src='httpsRead.co/150x225/000000/ffffff?text=Error'">
                        <div class="lang-badge"><?php echo htmlspecialchars($movie['language']); ?></div>
                        <h3 class="movie-title px-1"><?php echo htmlspecialchars($movie['title']); ?></h3>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- All Time Hits -->
        <section>
            <h2 class="section-title">All Time Hits</h2>
            <div class="scroll-container px-4">
                <?php foreach ($all_time_hits as $movie): ?>
                    <a href="<?php echo get_movie_link($movie); ?>" class="movie-card">
                        <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="movie-poster" onerror="this.src='httpsRead.co/150x225/000000/ffffff?text=Error'">
                        <div class="lang-badge"><?php echo htmlspecialchars($movie['language']); ?></div>
                        <h3 class="movie-title px-1"><?php echo htmlspecialchars($movie['title']); ?></h3>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Kannada -->
        <section>
            <h2 class="section-title">Kannada</h2>
            <div class="scroll-container px-4">
                 <?php foreach ($kannada_movies as $movie): ?>
                    <a href="<?php echo get_movie_link($movie); ?>" class="movie-card">
                        <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="movie-poster" onerror="this.src='httpsRead.co/150x225/000000/ffffff?text=Error'">
                        <div class="lang-badge"><?php echo htmlspecialchars($movie['language']); ?></div>
                        <h3 class="movie-title px-1"><?php echo htmlspecialchars($movie['title']); ?></h3>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
        
        <!-- Telugu -->
        <section>
            <h2 class="section-title">Telugu</h2>
            <div class="scroll-container px-4">
                 <?php foreach ($telugu_movies as $movie): ?>
                    <a href="<?php echo get_movie_link($movie); ?>" class="movie-card">
                        <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="movie-poster" onerror="this.src='httpsRead.co/150x225/000000/ffffff?text=Error'">
                        <div class="lang-badge"><?php echo htmlspecialchars($movie['language']); ?></div>
                        <h3 class="movie-title px-1"><?php echo htmlspecialchars($movie['title']); ?></h3>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Multi-language -->
        <section>
            <h2 class="section-title">Multi-language</h2>
            <div class="scroll-container px-4">
                 <?php foreach ($multi_lang_movies as $movie): ?>
                    <a href="<?php echo get_movie_link($movie); ?>" class="movie-card">
                        <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="movie-poster" onerror="this.src='httpsRead.co/150x225/000000/ffffff?text=Error'">
                        <div class="lang-badge"><?php echo htmlspecialchars($movie['language']); ?></div>
                        <h3 class="movie-title px-1"><?php echo htmlspecialchars($movie['title']); ?></h3>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

    </main>

    <!-- Bottom Navigation -->
    <nav class="fixed bottom-0 left-0 right-0 bg-black border-t border-zinc-800 shadow-lg z-50">
        <div class="container mx-auto max-w-lg flex justify-around py-3">
            <!-- Home -->
            <a href="index.php" class="flex flex-col items-center justify-center text-red-500"> <!-- Active Link -->
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


