<?php
    session_start();
    
    // --- DATABASE CONNECTION (CONCEPT) ---
    // This is where you put your Neon connection string
    // $dsn = "pgsql:host=...;port=...;dbname=...;user=...;password=...";
    // try {
    //     $pdo = new PDO($dsn);
    // } catch (PDOException $e) {
    //     die("DB Error: " . $e->getMessage());
    // }

    $isLoggedIn = isset($_SESSION['user_id']);
    $userId = $_SESSION['user_id'] ?? null;

    // --- DATA FETCHING (CONCEPT) ---
    // In a real app, you would run these queries using $pdo

    // 1. Fetch Continue Watching (for logged-in user)
    // $continueWatching = [];
    // if ($isLoggedIn) {
    //     $sql = "SELECT m.id, m.title, m.poster_url, m.type, h.watch_time, 
    //                   CASE WHEN m.type = 'movie' THEN m.duration 
    //                        ELSE (SELECT e.duration_seconds FROM episodes e WHERE e.id = h.content_id) 
    //                   END as total_duration,
    //                   CASE WHEN m.type = 'series' THEN (SELECT e.id FROM episodes e WHERE e.id = h.content_id) 
    //                        ELSE m.id 
    //                   END as content_play_id
    //            FROM watch_history h
    //            JOIN movies m ON m.id = (
    //                 CASE WHEN (SELECT 1 FROM movies mov WHERE mov.id = h.content_id) THEN h.content_id 
    //                      ELSE (SELECT s.movie_id FROM episodes e JOIN seasons s ON e.season_id = s.id WHERE e.id = h.content_id) 
    //                 END
    //            )
    //            WHERE h.user_id = ? AND h.watch_time > 0 
    //            ORDER BY h.last_watched DESC LIMIT 5";
    //     // $stmt = $pdo->prepare($sql);
    //     // $stmt->execute([$userId]);
    //     // $continueWatching = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // }
    
    // Placeholder data
    $continueWatching = [
        ['id' => 1, 'title' => 'Continue Movie', 'poster_url' => 'https://placehold.co/300x450/111/fff?text=Continue', 'type' => 'movie', 'watch_time' => 300, 'total_duration' => '2h 15m', 'content_play_id' => 1],
        ['id' => 2, 'title' => 'Continue Series', 'poster_url' => 'https://placehold.co/300x450/222/fff?text=Continue', 'type' => 'series', 'watch_time' => 120, 'total_duration' => 1800, 'content_play_id' => 1] // content_play_id would be an episode ID
    ];


    // 2. Fetch Featured
    // $featured = $pdo->query("SELECT * FROM movies WHERE featured = true LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $featured = ['id' => 10, 'title' => 'Featured Movie', 'description' => 'This is the most popular movie right now. Watch it!', 'poster_url' => 'https://placehold.co/600x400/f00/fff?text=FEATURED'];

    // 3. Fetch All Time Hits
    // $allTimeHits = $pdo->query("SELECT * FROM movies WHERE type = 'movie' ORDER BY popularity DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    $allTimeHits = [
        ['id' => 1, 'title' => 'Hit Movie 1', 'poster_url' => 'https://placehold.co/300x450/333/fff?text=Hit+1', 'language' => 'English', 'type' => 'movie'],
        ['id' => 2, 'title' => 'Hit Movie 2', 'poster_url' => 'https://placehold.co/300x450/444/fff?text=Hit+2', 'language' => 'Hindi', 'type' => 'movie']
    ];

    // 4. Fetch by Language
    // $kannadaMovies = $pdo->query("SELECT * FROM movies WHERE language = 'Kannada' LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    $kannadaMovies = [
        ['id' => 3, 'title' => 'Kannada Movie 1', 'poster_url' => 'https://placehold.co/300x450/555/fff?text=KGF', 'language' => 'Kannada', 'type' => 'movie'],
        ['id' => 4, 'title' => 'Kannada Series 1', 'poster_url' => 'https://placehold.co/300x450/666/fff?text=Series', 'language' => 'Kannada', 'type' => 'series']
    ];
    // $teluguMovies = $pdo->query("SELECT * FROM movies WHERE language = 'Telugu' LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    $teluguMovies = [
        ['id' => 5, 'title' => 'Telugu Movie 1', 'poster_url' => 'https://placehold.co/300x450/777/fff?text=RRR', 'language' => 'Telugu', 'type' => 'movie']
    ];
    // $multiLanguage = $pdo->query("SELECT * FROM movies WHERE language = 'Multi' LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    $multiLanguage = [
         ['id' => 6, 'title' => 'Multi Movie 1', 'poster_url' => 'https://placehold.co/300x450/888/fff?text=Multi', 'language' => 'Multi', 'type' => 'movie']
    ];

?>

<!DOCTYPE html>
<html lang="en" class="bg-gray-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyStream - Home</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .snap-x { scroll-snap-type: x mandatory; }
        .snap-center { scroll-snap-align: center; }
        ::-webkit-scrollbar { display: none; }
    </style>
</head>
<body class="font-sans text-white">

    <!-- Header -->
    <header class="p-4 flex justify-between items-center sticky top-0 bg-gray-900 z-10">
        <h1 class="text-3xl font-bold text-red-600">MyStream</h1>
        <div class="flex items-center space-x-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <?php if ($isLoggedIn): ?>
                <a href="logout.php">
                    <img src="https://placehold.co/40x40/f0f/fff?text=U" alt="User Profile" class="h-8 w-8 rounded-full">
                </a>
            <?php else: ?>
                <a href="login.php" class="text-sm bg-red-600 px-3 py-1.5 rounded-md font-semibold">Login</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Main Content -->
    <main class="pb-20">

        <!-- Featured Movie -->
        <?php if ($featured): ?>
        <section class="w-full h-64 md:h-80 relative mb-6">
            <img src="<?php echo htmlspecialchars($featured['poster_url']); ?>" alt="<?php echo htmlspecialchars($featured['title']); ?>" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-gray-900 to-transparent"></div>
            <div class="absolute bottom-0 left-0 p-6">
                <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($featured['title']); ?></h2>
                <p class="text-sm text-gray-300 mb-4"><?php echo htmlspecialchars($featured['description']); ?></p>
                <a href="details.php?id=<?php echo $featured['id']; ?>" class="bg-white text-black font-bold py-2 px-6 rounded-lg hover:bg-gray-200 transition">
                    Play
                </a>
            </div>
        </section>
        <?php endif; ?>
        
        <!-- Continue Watching -->
        <?php if (!empty($continueWatching)): ?>
        <section class="mb-8">
            <h2 class="text-2xl font-semibold px-4 mb-3">Continue Watching</h2>
            <div class="flex overflow-x-auto snap-x gap-4 px-4">
                <?php foreach ($continueWatching as $item): ?>
                    <?php
                        // Calculate progress
                        $progress = 0;
                        if (is_numeric($item['total_duration'])) { // Series (duration in seconds)
                            $progress = ($item['watch_time'] / $item['total_duration']) * 100;
                        } else { // Movie (duration as string "2h 15m")
                            // Simple placeholder logic
                            $progress = 50; 
                        }
                    ?>
                    <div class="flex-shrink-0 w-48 snap-center">
                        <div class="relative rounded-lg overflow-hidden group">
                            <!-- Link to player with correct ID (movie or episode) -->
                            <a href="player.php?<?php echo $item['type'] === 'movie' ? 'id=' : 'episode='; ?><?php echo $item['content_play_id']; ?>">
                                <img src="<?php echo htmlspecialchars($item['poster_url']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="w-full h-28 object-cover">
                                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <!-- Progress Bar -->
                                <div class="absolute bottom-0 left-0 w-full h-1 bg-gray-700">
                                    <div class="h-1 bg-red-600" style="width: <?php echo $progress; ?>%;"></div>
                                </div>
                            </a>
                        </div>
                        <h3 class="text-sm font-semibold mt-2 truncate"><?php echo htmlspecialchars($item['title']); ?></h3>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Reusable Movie Card Function (Conceptual) -->
        <?php
        function renderMovieCard($movie) {
            $link = ($movie['type'] === 'movie' ? 'details.php' : 'series.php') . '?id=' . $movie['id'];
            echo '<div class="flex-shrink-0 w-36 sm:w-40 snap-center">';
            echo '  <a href="' . $link . '" class="block group relative rounded-lg overflow-hidden">';
            echo '    <img src="' . htmlspecialchars($movie['poster_url']) . '" alt="' . htmlspecialchars($movie['title']) . '" class="w-full h-52 sm:h-60 object-cover">';
            echo '    <div class="absolute top-1 left-1 bg-red-600 text-white text-xs font-bold px-2 py-0.5 rounded-sm">' . htmlspecialchars($movie['language']) . '</div>';
            echo '    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center transition">';
            echo '      <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" /></svg>';
            echo '    </div>';
            echo '  </a>';
            echo '  <h3 class="text-sm font-semibold mt-2 truncate">' . htmlspecialchars($movie['title']) . '</h3>';
            echo '</div>';
        }
        ?>

        <!-- All Time Hits -->
        <section class="mb-8">
            <h2 class="text-2xl font-semibold px-4 mb-3">All Time Hits</h2>
            <div class="flex overflow-x-auto snap-x gap-4 px-4">
                <?php foreach ($allTimeHits as $movie) { renderMovieCard($movie); } ?>
            </div>
        </section>
        
        <!-- Kannada -->
        <section class="mb-8">
            <h2 class="text-2xl font-semibold px-4 mb-3">Kannada</h2>
            <div class="flex overflow-x-auto snap-x gap-4 px-4">
                <?php foreach ($kannadaMovies as $movie) { renderMovieCard($movie); } ?>
            </div>
        </section>

        <!-- Telugu -->
        <section class="mb-8">
            <h2 class="text-2xl font-semibold px-4 mb-3">Telugu</h2>
            <div class="flex overflow-x-auto snap-x gap-4 px-4">
                 <?php foreach ($teluguMovies as $movie) { renderMovieCard($movie); } ?>
            </div>
        </section>

        <!-- Multi Language -->
        <section class="mb-8">
            <h2 class="text-2xl font-semibold px-4 mb-3">Multi-Language</h2>
            <div class="flex overflow-x-auto snap-x gap-4 px-4">
                <?php foreach ($multiLanguage as $movie) { renderMovieCard($movie); } ?>
            </div>
        </section>

    </main>

    <!-- Bottom Navigation -->
    <nav class="fixed bottom-0 left-0 w-full bg-gray-900/80 backdrop-blur-sm border-t border-gray-700 p-4">
        <div class="flex justify-around">
            <a href="index.php" class="text-red-600 flex flex-col items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                </svg>
                <span class="text-xs">Home</span>
            </a>
            <!-- Other nav items would go here -->
        </div>
    </nav>

</body>
</html>

