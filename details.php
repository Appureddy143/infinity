<?php
    session_start();
    require_once 'db_connect.php'; // Use our new connection file

    // --- Data Initialization ---
    $movie = null;
    $first_episode = null; // Used to get the 'Play' button for a movie
    $error = null;

    // 1. Get Movie ID from URL
    $movie_id = $_GET['id'] ?? null;
    if (!$movie_id || !filter_var($movie_id, FILTER_VALIDATE_INT)) {
        // No ID, or invalid ID
        header("Location: index.php"); // Redirect home
        exit;
    }

    try {
        // 2. Fetch Movie Details from Database
        $stmt_movie = $pdo->prepare("SELECT * FROM movies WHERE movie_id = ? AND type = 'movie'");
        $stmt_movie->execute([$movie_id]);
        $movie = $stmt_movie->fetch();

        if (!$movie) {
            // Movie not found, check if it's a series
            $stmt_check_series = $pdo->prepare("SELECT movie_id FROM movies WHERE movie_id = ? AND type = 'series'");
            $stmt_check_series->execute([$movie_id]);
            if ($stmt_check_series->fetch()) {
                // It's a series, redirect to series.php
                header("Location: series.php?id=" . $movie_id);
                exit;
            } else {
                // Not found at all
                $error = "Movie not found.";
            }
        } else {
            // 3. Find the first "episode" associated with this movie
            // (We stored the movie's video URL in the episodes table)
            $stmt_episode = $pdo->prepare("
                SELECT e.*
                FROM episodes e
                JOIN seasons s ON e.season_id = s.season_id
                WHERE s.movie_id = ?
                ORDER BY s.season_number, e.episode_number
                LIMIT 1
            ");
            $stmt_episode->execute([$movie_id]);
            $first_episode = $stmt_episode->fetch();
        }

    } catch (PDOException $e) {
        error_log($e->getMessage());
        $error = "An error occurred while loading the movie details.";
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $movie ? htmlspecialchars($movie['title']) : 'Details'; ?> - YourStream</title>
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

        <?php elseif ($movie): ?>
            <!-- Movie Content -->
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
                    <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?> Poster" class="w-full h-full object-cover" onerror="this.src='https://placehold.co/600x400/1a1a1a/ffffff?text=Image+Missing'">
                    <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent"></div>
                </div>

                <!-- Movie Info -->
                <main class="p-4 -mt-12 relative z-10">
                    <h1 class="text-3xl font-bold text-white mb-2"><?php echo htmlspecialchars($movie['title']); ?></h1>
                    
                    <!-- Meta Info -->
                    <div class="flex items-center space-x-3 text-gray-400 text-sm mb-4">
                        <?php if ($movie['release_date']): ?>
                            <span><?php echo date('Y', strtotime($movie['release_date'])); ?></span>
                            <span class="text-gray-600">&bull;</span>
                        <?php endif; ?>
                        
                        <?php if ($first_episode): ?>
                            <span>
                                <?php 
                                    $minutes = floor($first_episode['duration_seconds'] / 60);
                                    $hours = floor($minutes / 60);
                                    $remaining_minutes = $minutes % 60;
                                    if ($hours > 0) {
                                        echo "{$hours}h {$remaining_minutes}m";
                                    } else {
                                        echo "{$minutes}m";
                                    }
                                ?>
                            </span>
                            <span class="text-gray-600">&bull;</span>
                        <?php endif; ?>

                        <span class="border border-gray-500 px-1.5 py-0.5 rounded text-xs"><?php echo htmlspecialchars($movie['language']); ?></span>
                    </div>

                    <!-- Play Button -->
                    <?php if ($first_episode): ?>
                        <a href="player.php?episode_id=<?php echo $first_episode['episode_id']; ?>" class="w-full flex items-center justify-center bg-red-600 hover:bg-red-700 text-white font-semibold py-3 rounded-lg text-lg transition-colors duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                            </svg>
                            Play
                        </a>
                    <?php else: ?>
                         <div class="w-full text-center bg-gray-700 text-gray-400 font-semibold py-3 rounded-lg text-lg">
                            Video Not Available
                        </div>
                    <?php endif; ?>
                    
                    <!-- Description -->
                    <div class="mt-6">
                        <h2 class="text-lg font-semibold text-white mb-2">Description</h2>
                        <p class="text-gray-300 text-sm leading-relaxed">
                            <?php echo nl2br(htmlspecialchars($movie['description'])); ?>
                        </p>
                    </div>

                    <!-- Genre -->
                    <?php if ($movie['genre']): ?>
                        <div class="mt-4">
                            <span class="text-gray-400 text-sm">Genre: </span>
                            <span class="text-gray-200 text-sm"><?php echo htmlspecialchars($movie['genre']); ?></span>
                        </div>
                    <?php endif; ?>
                </main>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>


