<?php
    session_start();
    require_once 'db_connect.php';

    // --- Helper Function ---
    // Converts seconds to a "1h 30m" or "45m" format
    function format_duration($seconds) {
        if ($seconds < 1) {
            return "0m";
        }
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        if ($hours > 0) {
            return $hours . "h " . $minutes . "m";
        } else {
            return $minutes . "m";
        }
    }

    // --- Data Initialization ---
    $series = null;
    $seasons = [];
    $episodesBySeason = [];
    $error = null;

    // 1. Get Series ID from URL
    $series_id = $_GET['id'] ?? null;
    if (!$series_id || !filter_var($series_id, FILTER_VALIDATE_INT)) {
        header("Location: index.php"); // Redirect home
        exit;
    }

    try {
        // 2. Fetch Series Details
        $stmt_series = $pdo->prepare("SELECT * FROM movies WHERE movie_id = ? AND type = 'series'");
        $stmt_series->execute([$series_id]);
        $series = $stmt_series->fetch();

        if (!$series) {
            // Check if it's a movie and redirect
            $stmt_check_movie = $pdo->prepare("SELECT movie_id FROM movies WHERE movie_id = ? AND type = 'movie'");
            $stmt_check_movie->execute([$series_id]);
            if ($stmt_check_movie->fetch()) {
                header("Location: details.php?id=" . $series_id);
                exit;
            } else {
                $error = "Series not found.";
            }
        } else {
            // 3. Fetch Seasons for this Series
            $stmt_seasons = $pdo->prepare("SELECT * FROM seasons WHERE movie_id = ? ORDER BY season_number");
            $stmt_seasons->execute([$series_id]);
            $seasons = $stmt_seasons->fetchAll(PDO::FETCH_ASSOC);

            // 4. Fetch all Episodes and group them by season
            $stmt_episodes = $pdo->prepare("
                SELECT e.* FROM episodes e
                JOIN seasons s ON e.season_id = s.season_id
                WHERE s.movie_id = ?
                ORDER BY e.season_id, e.episode_number
            ");
            $stmt_episodes->execute([$series_id]);
            
            while ($episode = $stmt_episodes->fetch(PDO::FETCH_ASSOC)) {
                $episodesBySeason[$episode['season_id']][] = $episode;
            }
        }

    } catch (PDOException $e) {
        error_log($e->getMessage());
        $error = "An error occurred while loading the series details.";
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $series ? htmlspecialchars($series['title']) : 'Details'; ?> - YourStream</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0f0f0f;
            color: #ffffff;
        }
    </style>
</head>
<body class="antialiased">

    <div class="container mx-auto max-w-lg min-h-screen bg-black">
        
        <?php if ($error): ?>
            <!-- Error Message -->
            <div class="p-4 text-center text-red-400">
                <p><?php echo htmlspecialchars($error); ?></p>
                <a href="index.php" class="text-red-500 hover:text-red-400 mt-2 block">&larr; Back to Home</a>
            </div>

        <?php elseif ($series): ?>
            <!-- Series Content -->
            <div>
                <!-- Header / Back Button -->
                <header class="p-4 absolute top-0 left-0 z-10">
                    <a href="index.php" class="text-white bg-black bg-opacity-50 rounded-full p-2 hover:bg-opacity-75 transition-colors duration-200 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                </header>

                <!-- Poster Image -->
                <div class="relative h-64 w-full">
                    <img src="<?php echo htmlspecialchars($series['poster_url']); ?>" alt="<?php echo htmlspecialchars($series['title']); ?> Poster" class="w-full h-full object-cover" onerror="this.src='https://placehold.co/600x400/1a1a1a/ffffff?text=Image+Missing'">
                    <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent"></div>
                </div>

                <!-- Series Info -->
                <main class="p-4 -mt-12 relative z-10">
                    <h1 class="text-3xl font-bold text-white mb-2"><?php echo htmlspecialchars($series['title']); ?></h1>
                    
                    <!-- Meta Info -->
                    <div class="flex items-center space-x-3 text-gray-400 text-sm mb-4">
                        <?php if ($series['release_date']): ?>
                            <span><?php echo date('Y', strtotime($series['release_date'])); ?></span>
                            <span class="text-gray-600">&bull;</span>
                        <?php endif; ?>
                        
                        <span><?php echo count($seasons); ?> Season(s)</span>
                        <span class="text-gray-600">&bull;</span>

                        <span class="border border-gray-500 px-1.5 py-0.5 rounded text-xs"><?php echo htmlspecialchars($series['genre']); ?></span>
                    </div>

                    <!-- Description -->
                    <div class="mt-6 mb-6">
                        <h2 class="text-lg font-semibold text-white mb-2">Description</h2>
                        <p class="text-gray-300 text-sm leading-relaxed">
                            <?php echo nl2br(htmlspecialchars($series['description'])); ?>
                        </p>
                    </div>

                    <!-- Season & Episode List -->
                    <div class="space-y-6">
                        <?php if (empty($seasons)): ?>
                            <p class="text-gray-400">No seasons or episodes are available for this series yet.</p>
                        <?php else: ?>
                            <?php foreach ($seasons as $season): ?>
                                <section>
                                    <h3 class="text-xl font-semibold mb-3">
                                        <?php echo htmlspecialchars($season['title'] ?? 'Season ' . $season['season_number']); ?>
                                    </h3>
                                    
                                    <div class="space-y-4">
                                        <?php if (!isset($episodesBySeason[$season['season_id']]) || empty($episodesBySeason[$season['season_id']])): ?>
                                            <p class="text-gray-500 text-sm">No episodes listed for this season.</p>
                                        <?php else: ?>
                                            <?php foreach ($episodesBySeason[$season['season_id']] as $episode): ?>
                                                <a href="player.php?episode_id=<?php echo $episode['episode_id']; ?>" class="block bg-zinc-900 rounded-lg overflow-hidden flex space-x-3 hover:bg-zinc-800 transition-colors duration-200">
                                                    <!-- Episode Thumbnail -->
                                                    <div class="flex-shrink-0 w-28">
                                                        <img src="<?php echo htmlspecialchars($episode['thumbnail_url'] ?? $series['poster_url']); ?>" alt="Episode <?php echo $episode['episode_number']; ?>" class="w-full h-20 object-cover" onerror="this.src='https://placehold.co/400x300/1a1a1a/ffffff?text=No+Thumb'">
                                                    </div>
                                                    
                                                    <!-- Episode Details -->
                                                    <div class="py-3 pr-3 flex-1 overflow-hidden">
                                                        <h4 class="text-sm font-semibold text-white truncate">
                                                            E<?php echo $episode['episode_number']; ?>: <?php echo htmlspecialchars($episode['title']); ?>
                                                        </h4>
                                                        <p class="text-xs text-gray-400 mt-1">
                                                            <?php echo format_duration($episode['duration_seconds']); ?>
                                                            <span class="text-gray-500 mx-1">&bull;</span>
                                                            <?php echo htmlspecialchars($episode['language']); ?>
                                                        </p>
                                                    </div>
                                                </a>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </section>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                </main>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>


