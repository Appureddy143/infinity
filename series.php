<?php
    session_start();

    // Helper function to format duration
    function format_duration($seconds) {
        if ($seconds < 3600) {
            $m = floor($seconds / 60);
            return "{$m}m";
        } else {
            $h = floor($seconds / 3600);
            $m = floor(($seconds % 3600) / 60);
            return "{$h}h {$m}m";
        }
    }

    // --- DATABASE CONNECTION (CONCEPT) ---
    // Make sure to replace this with your actual database connection
    // $dsn = "pgsql:host=...;port...;dbname=...;user=...;password=...";
    // $pdo = new PDO($dsn, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo = null; // Placeholder

    // --- Data Initialization ---
    $series = null;
    $seasons = [];
    $episodesBySeason = [];
    $error = null;

    // --- 1. Get Series ID from URL ---
    $series_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($series_id > 0) {
        try {
            // --- 2. Fetch Series Details ---
            // $stmt_series = $pdo->prepare("SELECT * FROM movies WHERE movie_id = ? AND type = 'series'");
            // $stmt_series->execute([$series_id]);
            // $series = $stmt_series->fetch(PDO::FETCH_ASSOC);

            // Mock Data (replace with DB call when ready)
            if ($series_id == 124) { // Mocking the series from admin panel
                 $series = [
                    'movie_id' => 124,
                    'title' => 'Epic Series Title',
                    'description' => 'A thrilling adventure through space and time.',
                    'poster_url' => 'https://placehold.co/400x600/1a1a1a/ffffff?text=Series+Poster',
                    'genre' => 'Sci-Fi, Adventure',
                    'release_date' => '2024-01-15'
                ];
            } else {
                $series = false;
            }


            if ($series) {
                // --- 3. Fetch Seasons for this Series ---
                // $stmt_seasons = $pdo->prepare("SELECT * FROM seasons WHERE movie_id = ? ORDER BY season_number");
                // $stmt_seasons->execute([$series_id]);
                // $seasons = $stmt_seasons->fetchAll(PDO::FETCH_ASSOC);
                
                // Mock Data
                $seasons = [
                    ['season_id' => 457, 'movie_id' => 124, 'season_number' => 1, 'title' => 'Season 1: The Beginning']
                ];

                if ($seasons) {
                    // --- 4. Fetch All Episodes for All Seasons ---
                    // This is more efficient than N+1 queries inside the loop
                    // $season_ids = array_map(fn($s) => $s['season_id'], $seasons);
                    // $placeholders = implode(',', array_fill(0, count($season_ids), '?'));
                    
                    // $sql_episodes = "SELECT * FROM episodes WHERE season_id IN ($placeholders) ORDER BY episode_number";
                    // $stmt_episodes = $pdo->prepare($sql_episodes);
                    // $stmt_episodes->execute($season_ids);
                    
                    // $all_episodes = $stmt_episodes->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Mock Data
                    $all_episodes = [
                        ['episode_id' => 1, 'season_id' => 457, 'episode_number' => 1, 'title' => 'The Pilot', 'description' => 'Our heroes meet.', 'thumbnail_url' => 'https://placehold.co/320x180/2a2a2a/ffffff?text=Ep+1', 'language' => 'English', 'duration_seconds' => 2700],
                        ['episode_id' => 2, 'season_id' => 457, 'episode_number' => 2, 'title' => 'The Adventure', 'description' => 'The journey begins.', 'thumbnail_url' => 'https://placehold.co/320x180/2a2a2a/ffffff?text=Ep+2', 'language' => 'Kannada', 'duration_seconds' => 2850]
                    ];

                    // Group episodes by their season_id for easy lookup
                    foreach ($all_episodes as $episode) {
                        $episodesBySeason[$episode['season_id']][] = $episode;
                    }
                }

            } else {
                $error = "Series not found.";
            }
        } catch (PDOException $e) {
            // Never show detailed database errors in production
            // error_log($e->getMessage()); // Log to server logs
            $error = "An error occurred while loading series details.";
        }
    } else {
        $error = "Invalid Series ID.";
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Use the title from the database, or a default if error -->
    <title><?php echo $series ? htmlspecialchars($series['title']) : 'Series Details'; ?> - YourStream</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0f0f0f;
            color: #ffffff;
        }
        /* Custom scrollbar for episode list */
        .episode-list::-webkit-scrollbar {
            width: 6px;
        }
        .episode-list::-webkit-scrollbar-track {
            background: #1a1a1a;
            border-radius: 3px;
        }
        .episode-list::-webkit-scrollbar-thumb {
            background: #4a4a4a;
            border-radius: 3px;
        }
        .episode-list::-webkit-scrollbar-thumb:hover {
            background: #5a5a5a;
        }
    </style>
</head>
<body class="antialiased">

    <div class="container mx-auto max-w-lg min-h-screen bg-black p-4">

        <!-- Back Button -->
        <header class="mb-4">
            <a href="index.php" class="text-gray-300 hover:text-white transition-colors duration-200">
                &larr; Back to Home
            </a>
        </header>

        <?php if ($error): ?>
            <!-- Error State -->
            <div class="flex flex-col items-center justify-center h-[80vh] text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-red-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h2 class="text-2xl font-bold mb-2">Oops!</h2>
                <p class="text-gray-400"><?php echo htmlspecialchars($error); ?></p>
                <a href="index.php" class="mt-6 px-4 py-2 bg-red-600 hover:bg-red-700 rounded-lg text-white font-medium transition-colors duration-200">
                    Go Home
                </a>
            </div>

        <?php elseif ($series): ?>
            <!-- Content State -->
            <main>
                <!-- Series Poster and Info -->
                <div class="flex flex-col sm:flex-row gap-4 mb-6">
                    <img src="<?php echo htmlspecialchars($series['poster_url']); ?>" alt="<?php echo htmlspecialchars($series['title']); ?> Poster" class="w-full sm:w-1/3 rounded-lg shadow-lg object-cover" onerror="this.src='https://placehold.co/400x600/1a1a1a/ffffff?text=Poster+Error'">
                    <div class="w-full sm:w-2/3">
                        <h1 class="text-3xl font-bold mb-2"><?php echo htmlspecialchars($series['title']); ?></h1>
                        <p class="text-gray-400 text-sm mb-3">
                            <span><?php echo date('Y', strtotime($series['release_date'])); ?></span> &bull;
                            <span><?php echo htmlspecialchars($series['genre']); ?></span>
                        </p>
                        <p class="text-gray-300 mb-4">
                            <?php echo htmlspecialchars($series['description']); ?>
                        </p>
                        <a href="player.php?series_id=<?php echo $series['movie_id']; ?>&season=1&episode=1" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 bg-red-600 hover:bg-red-700 rounded-lg text-white font-bold transition-colors duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                            </svg>
                            Play S1 E1
                        </a>
                    </div>
                </div>

                <!-- Seasons and Episodes -->
                <div class="space-y-6">
                    <?php if (empty($seasons)): ?>
                        <p class="text-gray-400">No seasons are available for this series yet.</p>
                    <?php else: ?>
                        <?php foreach ($seasons as $season): ?>
                            <div class="bg-zinc-900 rounded-lg p-4">
                                <h2 class="text-xl font-semibold mb-3">
                                    <?php echo htmlspecialchars($season['title']); ?>
                                </h2>
                                
                                <div class="space-y-3 episode-list max-h-[400px] overflow-y-auto pr-2">
                                    <?php if (empty($episodesBySeason[$season['season_id']])): ?>
                                        <p class="text-gray-500 text-sm px-2">No episodes listed for this season.</p>
                                    <?php else: ?>
                                        <?php foreach ($episodesBySeason[$season['season_id']] as $episode): ?>
                                            <a href="player.php?episode_id=<?php echo $episode['episode_id']; ?>" class="block p-3 bg-zinc-800 hover:bg-zinc-700 rounded-lg transition-colors duration-200">
                                                <div class="flex items-center gap-4">
                                                    <img src="<?php echo htmlspecialchars($episode['thumbnail_url']); ?>" alt="<?php echo htmlspecialchars($episode['title']); ?>" class="w-24 h-14 rounded object-cover flex-shrink-0" onerror="this.src='https://placehold.co/320x180/2a2a2a/ffffff?text=No+Thumb'">
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex justify-between items-start">
                                                            <h3 class="text-md font-medium truncate"><?php echo $episode['episode_number']; ?>. <?php echo htmlspecialchars($episode['title']); ?></h3>
                                                            <span class="text-xs text-gray-400 whitespace-nowrap ml-2"><?php echo format_duration($episode['duration_seconds']); ?></span>
                                                        </div>
                                                        <p class="text-sm text-gray-400 mt-1 truncate"><?php echo htmlspecialchars($episode['description']); ?></p>
                                                        <span class_ ="text-xs text-gray-500 bg-zinc-700 px-2 py-0.5 rounded-full mt-2 inline-block"><?php echo htmlspecialchars($episode['language']); ?></span>
                                                    </div>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </main>
        <?php endif; ?>

    </div>

</body>
</html>

