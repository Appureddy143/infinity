<?php
    session_start();

    // --- DATABASE CONNECTION (CONCEPT) ---
    // $dsn = "pgsql:host=...;port...;dbname=...;user...;password=...";
    // $pdo = new PDO($dsn, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo = null; // Placeholder

    // --- Data Initialization ---
    $continueWatching = [];
    $allTimeHits = [];
    $kannadaMovies = [];
    $teluguMovies = [];
    $multiLanguageMovies = [];
    $error = null;

    try {
        // --- 1. Fetch Continue Watching (Logged-in users only) ---
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            
            // $stmt_continue = $pdo->prepare("
            //     SELECT 
            //         wh.progress_seconds, wh.total_duration_seconds,
            //         m.movie_id, m.title, m.poster_url, m.type,
            //         e.episode_id, e.title AS episode_title, e.episode_number,
            //         s.season_number
            //     FROM 
            //         watch_history wh
            //     JOIN 
            //         movies m ON wh.movie_id = m.movie_id
            //     LEFT JOIN 
            //         episodes e ON wh.episode_id = e.episode_id
            //     LEFT JOIN 
            //         seasons s ON e.season_id = s.season_id
            //     WHERE 
            //         wh.user_id = ? 
            //         AND wh.progress_seconds > 30 -- Not just started
            //         AND (wh.total_duration_seconds - wh.progress_seconds) > 60 -- Not finished
            //     ORDER BY 
            //         wh.last_watched_at DESC
            //     LIMIT 5
            // ");
            // $stmt_continue->execute([$user_id]);
            // $continueWatching = $stmt_continue->fetchAll(PDO::FETCH_ASSOC);

            // Mock Data (replace with DB call)
            $continueWatching = [
                [
                    'movie_id' => 124,
                    'episode_id' => 1,
                    'title' => 'Epic Series Title',
                    'poster_url' => 'https://placehold.co/400x600/1a1a1a/ffffff?text=Series+Poster',
                    'type' => 'series',
                    'episode_title' => 'The Pilot',
                    'episode_number' => 1,
                    'season_number' => 1,
                    'progress_seconds' => 1200,
                    'total_duration_seconds' => 2700
                ],
                [
                    'movie_id' => 123,
                    'episode_id' => null,
                    'title' => 'Action Movie',
                    'poster_url' => 'https://placehold.co/400x600/1a1a1a/ffffff?text=Action+Movie',
                    'type' => 'movie',
                    'episode_title' => null,
                    'episode_number' => null,
                    'season_number' => null,
                    'progress_seconds' => 3600,
                    'total_duration_seconds' => 7200
                ]
            ];
        }

        // --- 2. Fetch All Time Hits (NOW DYNAMIC) ---
        // This query counts views from watch_history to find the most popular movies.
        // $stmt_hits = $pdo->query("
        //     SELECT 
        //         m.*, COUNT(wh.watch_history_id) AS watch_count
        //     FROM 
        //         movies m
        //     JOIN 
        //         watch_history wh ON m.movie_id = wh.movie_id
        //     WHERE 
        //         m.type = 'movie'
        //     GROUP BY 
        //         m.movie_id
        //     ORDER BY 
        //         watch_count DESC
        //     LIMIT 10
        // ");
        // $allTimeHits = $stmt_hits->fetchAll(PDO::FETCH_ASSOC);
        
        // Mock Data (if db query fails or is empty)
        if (empty($allTimeHits)) {
            $allTimeHits = [
                ['movie_id' => 101, 'title' => 'All Time Hit 1', 'poster_url' => 'https://placehold.co/400x600/1a1a1a/ffffff?text=Hit+Movie+1', 'language' => 'Kannada', 'type' => 'movie'],
                ['movie_id' => 102, 'title' => 'All Time Hit 2', 'poster_url' => 'https://placehold.co/400x600/1a1a1a/ffffff?text=Hit+Movie+2', 'language' => 'Telugu', 'type' => 'movie'],
            ];
        }

        // --- 3. Fetch Kannada Movies ---
        // $stmt_kannada = $pdo->query("SELECT * FROM movies WHERE type = 'movie' AND language = 'Kannada' ORDER BY release_date DESC LIMIT 10");
        // $kannadaMovies = $stmt_kannada->fetchAll(PDO::FETCH_ASSOC);
        $kannadaMovies = [
            ['movie_id' => 103, 'title' => 'New Kannada Film', 'poster_url' => 'https://placehold.co/400x600/1a1a1a/ffffff?text=Kannada+1', 'language' => 'Kannada', 'type' => 'movie'],
        ];

        // --- 4. Fetch Telugu Movies ---
        // $stmt_telugu = $pdo->query("SELECT * FROM movies WHERE type = 'movie' AND language = 'Telugu' ORDER BY release_date DESC LIMIT 10");
        // $teluguMovies = $stmt_telugu->fetchAll(PDO::FETCH_ASSOC);
        $teluguMovies = [
            ['movie_id' => 104, 'title' => 'New Telugu Film', 'poster_url' => 'https://placehold.co/400x600/1a1a1a/ffffff?text=Telugu+1', 'language' => 'Telugu', 'type' => 'movie'],
        ];

        // --- 5. Fetch Multi-language Movies ---
        // $stmt_multi = $pdo->query("SELECT * FROM movies WHERE type = 'movie' AND language = 'Multi-language' ORDER BY release_date DESC LIMIT 10");
        // $multiLanguageMovies = $stmt_multi->fetchAll(PDO::FETCH_ASSOC);
        $multiLanguageMovies = [
            ['movie_id' => 105, 'title' => 'Pan-India Film', 'poster_url' => 'https://placehold.co/400x600/1a1a1a/ffffff?text=Multi+1', 'language' => 'Multi-language', 'type' => 'movie'],
        ];

    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
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
            background-color: #0f0f0f;
            color: #ffffff;
        }
        /* Hide scrollbar */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
        .movie-card {
            flex: 0 0 140px; /* Do not grow, do not shrink, base width 140px */
        }
        .progress-bar-bg {
            background-color: rgba(90, 90, 90, 0.7);
        }
        .progress-bar-fg {
            background-color: #ef4444; /* red-500 */
        }
    </style>
</head>
<body class="antialiased">

    <div class="container mx-auto max-w-lg min-h-screen bg-black">
        
        <!-- Header -->
        <header class="p-4 flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
            <h1 class="text-2xl font-bold text-red-500">YourStream</h1>
            <nav class="flex items-center space-x-4">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="text-gray-300 text-sm hidden sm:inline">Hi, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="logout.php" class="text-gray-300 hover:text-white text-sm">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="text-gray-300 hover:text-white text-sm">Login</a>
                <?php endif; ?>
            </nav>
        </header>

        <!-- Search Bar -->
        <div class="p-4 pt-0">
            <form action="search.php" method="GET" class="relative">
                <input type="search" name="q" placeholder="Search movies, series..." class="w-full bg-zinc-800 text-white placeholder-gray-400 rounded-lg py-2.5 px-4 focus:outline-none focus:ring-2 focus:ring-red-500" required>
                <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                </button>
            </form>
        </div>

        <!-- Main Content -->
        <main class="pb-6">
            
            <?php if ($error): ?>
                <div class="text-center text-red-400 p-4"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- Continue Watching Section -->
            <?php if (!empty($continueWatching)): ?>
                <section class="mb-6">
                    <h2 class="text-xl font-semibold px-4 mb-3">Continue Watching</h2>
                    <div class="flex overflow-x-auto no-scrollbar px-4 space-x-4">
                        
                        <?php foreach ($continueWatching as $item): ?>
                            <?php
                                // Calculate progress
                                $progressPercent = 0;
                                if ($item['total_duration_seconds'] > 0) {
                                    $progressPercent = ($item['progress_seconds'] / $item['total_duration_seconds']) * 100;
                                }
                                
                                // Determine the correct link
                                $player_link = "player.php?";
                                if ($item['type'] == 'series' && $item['episode_id']) {
                                    $player_link .= "episode_id=" . $item['episode_id'];
                                } else {
                                    $player_link .= "movie_id=" . $item['movie_id'];
                                }
                            ?>
                            <a href="<?php echo htmlspecialchars($player_link); ?>" class="block movie-card relative rounded-lg overflow-hidden group">
                                <img src="<?php echo htmlspecialchars($item['poster_url']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="w-full h-auto object-cover transition-transform duration-300 group-hover:scale-105" onerror="this.src='https://placehold.co/400x600/1a1a1a/ffffff?text=Poster+Error'">
                                
                                <!-- Play icon overlay -->
                                <div class="absolute inset-0 bg-black bg-opacity-30 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                                    </svg>
                                </div>

                                <!-- Progress Bar -->
                                <div class="absolute bottom-0 left-0 w-full h-1.5 progress-bar-bg">
                                    <div class="h-full progress-bar-fg" style="width: <?php echo $progressPercent; ?>%;"></div>
                                </div>
                                
                                <!-- Content Title -->
                                <div class="absolute bottom-2 left-2 right-2 text-white p-1 rounded">
                                    <p class="text-sm font-semibold truncate"><?php echo htmlspecialchars($item['title']); ?></p>
                                    <?php if ($item['type'] == 'series'): ?>
                                        <p class="text-xs text-gray-200 truncate">S<?php echo $item['season_number']; ?>:E<?php echo $item['episode_number']; ?> "<?php echo htmlspecialchars($item['episode_title']); ?>"</p>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; ?>

                    </div>
                </section>
            <?php endif; ?>


            <!-- All Time Hits Section -->
            <section class="mb-6">
                <h2 class="text-xl font-semibold px-4 mb-3">All Time Hits</h2>
                <div class="flex overflow-x-auto no-scrollbar px-4 space-x-4">
                    
                    <?php if (empty($allTimeHits)): ?>
                        <p class="text-gray-500 pl-4">No movies available in this category.</p>
                    <?php else: ?>
                        <?php foreach ($allTimeHits as $movie): ?>
                            <?php
                                $details_link = ($movie['type'] ?? 'movie') == 'series' ? "series.php?id=" . $movie['movie_id'] : "details.php?id=" . $movie['movie_id'];
                            ?>
                            <a href="<?php echo htmlspecialchars($details_link); ?>" class="block movie-card relative rounded-lg overflow-hidden group">
                                <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="w-full h-auto object-cover transition-transform duration-300 group-hover:scale-105" onerror="this.src='https://placehold.co/400x600/1a1a1a/ffffff?text=Poster+Error'">
                                <span class="absolute top-1 left-1 bg-black bg-opacity-70 text-white text-xs px-2 py-0.5 rounded"><?php echo htmlspecialchars($movie['language']); ?></span>
                                <div class="absolute bottom-0 left-0 right-0 p-2 bg-gradient-to-t from-black via-black/70 to-transparent">
                                    <h3 class="text-sm font-medium text-white truncate"><?php echo htmlspecialchars($movie['title']); ?></h3>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>

                </div>
            </section>
            
            <!-- Kannada Section -->
            <section class="mb-6">
                <h2 class="text-xl font-semibold px-4 mb-3">Kannada</h2>
                <div class="flex overflow-x-auto no-scrollbar px-4 space-x-4">
                    
                    <?php if (empty($kannadaMovies)): ?>
                        <p class="text-gray-500 pl-4">No movies available in this category.</p>
                    <?php else: ?>
                        <?php foreach ($kannadaMovies as $movie): ?>
                             <?php
                                $details_link = ($movie['type'] ?? 'movie') == 'series' ? "series.php?id=" . $movie['movie_id'] : "details.php?id=" . $movie['movie_id'];
                            ?>
                            <a href="<?php echo htmlspecialchars($details_link); ?>" class="block movie-card relative rounded-lg overflow-hidden group">
                                <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="w-full h-auto object-cover transition-transform duration-300 group-hover:scale-105" onerror="this.src='https://placehold.co/400x600/1a1a1a/ffffff?text=Poster+Error'">
                                <span class="absolute top-1 left-1 bg-black bg-opacity-70 text-white text-xs px-2 py-0.5 rounded"><?php echo htmlspecialchars($movie['language']); ?></span>
                                <div class="absolute bottom-0 left-0 right-0 p-2 bg-gradient-to-t from-black via-black/70 to-transparent">
                                    <h3 class="text-sm font-medium text-white truncate"><?php echo htmlspecialchars($movie['title']); ?></h3>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>

                </div>
            </section>
            
            <!-- Telugu Section -->
            <section class="mb-6">
                <h2 class="text-xl font-semibold px-4 mb-3">Telugu</h2>
                <div class="flex overflow-x-auto no-scrollbar px-4 space-x-4">
                    
                     <?php if (empty($teluguMovies)): ?>
                        <p class="text-gray-500 pl-4">No movies available in this category.</p>
                    <?php else: ?>
                        <?php foreach ($teluguMovies as $movie): ?>
                             <?php
                                $details_link = ($movie['type'] ?? 'movie') == 'series' ? "series.php?id=" . $movie['movie_id'] : "details.php?id=" . $movie['movie_id'];
                            ?>
                            <a href="<?php echo htmlspecialchars($details_link); ?>" class="block movie-card relative rounded-lg overflow-hidden group">
                                <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="w-full h-auto object-cover transition-transform duration-300 group-hover:scale-105" onerror="this.src='https://placehold.co/400x600/1a1a1a/ffffff?text=Poster+Error'">
                                <span class="absolute top-1 left-1 bg-black bg-opacity-70 text-white text-xs px-2 py-0.5 rounded"><?php echo htmlspecialchars($movie['language']); ?></span>
                                <div class="absolute bottom-0 left-0 right-0 p-2 bg-gradient-to-t from-black via-black/70 to-transparent">
                                    <h3 class="text-sm font-medium text-white truncate"><?php echo htmlspecialchars($movie['title']); ?></h3>
                                CSS
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>

                </div>
            </section>
            
            <!-- Multi-language Section -->
            <section class="mb-6">
                <h2 class="text-xl font-semibold px-4 mb-3">Multi-language</h2>
                <div class="flex overflow-x-auto no-scrollbar px-4 space-x-4">
                    
                     <?php if (empty($multiLanguageMovies)): ?>
                        <p class="text-gray-500 pl-4">No movies available in this category.</p>
                    <?php else: ?>
                        <?php foreach ($multiLanguageMovies as $movie): ?>
                             <?php
                                $details_link = ($movie['type'] ?? 'movie') == 'series' ? "series.php?id=" . $movie['movie_id'] : "details.php?id=" . $movie['movie_id'];
                            ?>
                            <a href="<?php echo htmlspecialchars($details_link); ?>" class="block movie-card relative rounded-lg overflow-hidden group">
                                <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="w-full h-auto object-cover transition-transform duration-300 group-hover:scale-105" onerror="this.src='https://placehold.co/400x600/1a1a1a/ffffff?text=Poster+Error'">
                                <span class="absolute top-1 left-1 bg-black bg-opacity-70 text-white text-xs px-2 py-0.5 rounded"><?php echo htmlspecialchars($movie['language']); ?></span>
                                <div class="absolute bottom-0 left-0 right-0 p-2 bg-gradient-to-t from-black via-black/70 to-transparent">
                                    <h3 class="text-sm font-medium text-white truncate"><?php echo htmlspecialchars($movie['title']); ?></h3>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>

                </div>
            </section>

        </main>
        
    </div>

</body>
</html>

