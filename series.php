<?php
    session_start();
    require_once 'db_connect.php';

    $user_id = $_SESSION['user_id'] ?? null;
    $username = $_SESSION['username'] ?? 'Guest';
    $series = null;
    $seasons = [];
    $episodesBySeason = [];
    $error = '';

    // Helper function
    function format_duration($seconds) {
        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        return $h > 0 ? "{$h}h {$m}m" : "{$m}m";
    }

    // 1. Validate Series ID
    if (!isset($_GET['movie_id'])) {
        $error = "No series selected.";
    } else {
        $series_id = intval($_GET['movie_id']);
        
        try {
            // 2. Fetch series data
            $stmt_series = $pdo->prepare("SELECT * FROM movies WHERE movie_id = ? AND type = 'series'");
            $stmt_series->execute([$series_id]);
            $series = $stmt_series->fetch(PDO::FETCH_ASSOC);
            
            if (!$series) {
                $error = "Series not found.";
            } else {
                // 3. Fetch all seasons for this series
                $stmt_seasons = $pdo->prepare("SELECT * FROM seasons WHERE movie_id = ? ORDER BY season_number");
                $stmt_seasons->execute([$series_id]);
                $seasons = $stmt_seasons->fetchAll(PDO::FETCH_ASSOC);
                
                // 4. Fetch all episodes and group them by season_id
                if ($seasons) {
                    $season_ids = array_map(fn($s) => $s['season_id'], $seasons);
                    $in_query = implode(',', array_fill(0, count($season_ids), '?'));
                    
                    $stmt_episodes = $pdo->prepare("
                        SELECT * FROM episodes WHERE season_id IN ($in_query) 
                        ORDER BY episode_number
                    ");
                    $stmt_episodes->execute($season_ids);
                    
                    while ($episode = $stmt_episodes->fetch(PDO::FETCH_ASSOC)) {
                        $episodesBySeason[$episode['season_id']][] = $episode;
                    }
                }
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
    <title><?php echo $series ? htmlspecialchars($series['title']) : 'Series Details'; ?> - YourStream</title>
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
        <?php elseif ($series): ?>
            <!-- Series Poster & Info -->
            <div class="flex flex-col sm:flex-row gap-4">
                <img src="<?php echo htmlspecialchars($series['poster_url']); ?>" alt="<?php echo htmlspecialchars($series['title']); ?>" class="w-full sm:w-1/3 h-auto object-cover rounded-lg shadow-lg" onerror="this.src='httpsRead.co/200x300/000000/ffffff?text=Error'">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold"><?php echo htmlspecialchars($series['title']); ?></h1>
                    <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-sm text-gray-400">
                        <span class="font-semibold"><?php echo date('Y', strtotime($series['release_date'])); ?></span>
                        <span>|</span>
                        <span class="font-semibold"><?php echo count($seasons); ?> Season(s)</span>
                        <span>|</span>
                        <span class="font-semibold"><?php echo htmlspecialchars($series['genre']); ?></span>
                    </div>
                    <p class="text-gray-300 mt-4 text-sm"><?php echo htmlspecialchars($series['description']); ?></p>
                </div>
            </div>
            
            <!-- Season & Episode List -->
            <div class="mt-8 space-y-6">
                <?php if (empty($seasons)): ?>
                    <p class="text-gray-400">No seasons or episodes have been added for this series yet.</p>
                <?php endif; ?>

                <?php foreach ($seasons as $season): ?>
                    <section>
                        <h2 class="text-xl font-semibold mb-3"><?php echo htmlspecialchars($season['title']); ?></h2>
                        <div class="space-y-3">
                            <?php 
                                $episodes = $episodesBySeason[$season['season_id']] ?? []; 
                            ?>
                            <?php if (empty($episodes)): ?>
                                <p class="text-gray-500 text-sm">No episodes found for this season.</p>
                            <?php endif; ?>

                            <?php foreach ($episodes as $episode): ?>
                                <a href="player.php?episode_id=<?php echo htmlspecialchars($episode['episode_id']); ?>" class="flex items-center bg-zinc-900 rounded-lg overflow-hidden shadow-md hover:bg-zinc-800 transition-colors duration-200">
                                    <div class="flex-shrink-0">
                                        <img src="<?php echo htmlspecialchars($episode['thumbnail_url'] ?: $series['poster_url']); ?>" alt="<?php echo htmlspecialchars($episode['title']); ?>" class="w-32 h-20 object-cover" onerror="this.src='httpsRead.co/128x80/000000/ffffff?text=Error'">
                                    </div>
                                    <div class="flex-1 p-3">
                                        <h3 class="font-semibold text-sm">
                                            E<?php echo htmlspecialchars($episode['episode_number']); ?>. <?php echo htmlspecialchars($episode['title']); ?>
                                        </h3>
                                        <div class="flex items-center text-xs text-gray-400 mt-1 space-x-2">
                                            <span><?php echo format_duration($episode['duration_seconds']); ?></span>
                                            <span>&bull;</span>
                                            <span><?php echo htmlspecialchars($episode['language']); ?></span>
                                        </div>
                                    </div>
                                    <div class="p-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
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


