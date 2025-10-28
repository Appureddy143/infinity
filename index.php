<!DOCTYPE html>
<html lang="en" class="bg-gray-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyStream - Home</title>
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom scrollbar for horizontal lists */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none; /* IE and Edge */
            scrollbar-width: none; /* Firefox */
        }
        /* ... existing style code ... */
    </style>
</head>
<body class="font-sans">

    <!-- 
      Main mobile screen container.
    -->
    <div class="max-w-md mx-auto bg-gray-900 text-white min-h-screen">

        <!-- Header -->
        <header class="p-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-red-600">MyStream</h1>
            <div class="flex space-x-4">
                <!-- Search Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <!-- Profile/Login Icon -->
                <!-- TODO: Link this to your login.php page -->
                <a href="login.php" title="Login/Profile">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </a>
            </div>
        </header>

        <!-- Main Content -->
        <main class="pb-16">
            <!-- Featured Movie -->
            <?php
                // --- DATABASE LOGIC (CONCEPT) ---
                // 1. Connect to your Neon (PostgreSQL) database here.
                //    $pdo = new PDO($dsn, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                
                // 2. Query for the featured movie
                //    $result = $pdo->query("SELECT * FROM movies WHERE featured = true LIMIT 1");
                //    $featuredMovie = $result->fetch(PDO::FETCH_ASSOC);
                
                // For now, we use placeholder data.
                $featuredMovie = [
                    'id' => 1,
                    'title' => 'Featured Movie Title',
                    'poster_url' => 'https://placehold.co/600x800/1a1a1a/ffffff?text=Movie+Poster',
                    'genre' => 'Action',
                    'type' => 'movie', // 'movie' or 'series'
                    'duration' => '2h 15m',
                    'language' => 'Multi' // Added language
                ];
                
                // Determine the correct details page
                $detailsPage = $featuredMovie['type'] == 'series' ? 'series.php' : 'details.php';
            ?>
            <section class="relative h-96">
                <img src="<?php echo htmlspecialchars($featuredMovie['poster_url']); ?>" 
                     alt="<?php echo htmlspecialchars($featuredMovie['title']); ?>" 
                     class="w-full h-full object-cover opacity-50">
                <div class="absolute bottom-0 left-0 p-6">
                    <h2 class="text-3xl font-bold"><?php echo htmlspecialchars($featuredMovie['title']); ?></h2>
                    <p class="text-sm text-gray-300 mt-1"><?php echo htmlspecialchars($featuredMovie['genre']); ?> • <?php echo htmlspecialchars($featuredMovie['duration']); ?></p>
                    
                    <a href="<?php echo $detailsPage; ?>?id=<?php echo $featuredMovie['id']; ?>" class="mt-4 bg-red-600 text-white font-bold py-2 px-6 rounded-lg flex items-center space-x-2 hover:bg-red-700 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                        </svg>
                        <span>Play</span>
                    </a>
                </div>
            </section>

            <!-- Movie Lists -->
            <section class="mt-8 space-y-6">
                
                <!-- Category: Continue Watching (For Logged-in Users) -->
                <?php
                    // --- DATABASE LOGIC (CONCEPT) ---
                    // if (user_is_logged_in()) {
                    //     $userId = $_SESSION['user_id'];
                    //     $history = $pdo->query("SELECT * FROM watch_history 
                    //                           JOIN movies ON movies.id = watch_history.movie_id
                    //                           WHERE user_id = $userId 
                    //                           ORDER BY last_watched DESC LIMIT 5");
                    // }
                    // if (!empty($history)):
                ?>
                <!-- 
                <div>
                    <h3 class="text-xl font-semibold px-4 mb-3">Continue Watching</h3>
                    <div class="flex space-x-4 overflow-x-auto no-scrollbar px-4">
                        <?php // foreach ($history as $item): ?>
                            <?php // $itemDetailsPage = $item['type'] == 'series' ? 'series.php' : 'details.php'; ?>
                            <a href="<?php // echo $itemDetailsPage; ?>?id=<?php // echo $item['id']; ?>" class="flex-shrink-0 w-32 relative">
                                <img src="<?php // echo htmlspecialchars($item['poster_url']); ?>" alt="<?php // echo htmlspecialchars($item['title']); ?>" class="rounded-lg w-full h-48 object-cover">
                                <span class="absolute top-2 left-2 bg-red-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                                    <?php // echo htmlspecialchars($item['language']); ?>
                                </span>
                                <div class="relative w-full h-1 bg-gray-700 rounded-full mt-2">
                                    <?php // $progress = ($item['watch_time'] / $item['duration_seconds']) * 100; ?>
                                    <div class="absolute top-0 left-0 h-1 bg-red-600 rounded-full" style="width: <?php // echo $progress; ?>%;"></div>
                                </div>
                            </a>
                        <?php // endforeach; ?>
                    </div>
                </div>
                -->
                <?php // endif; ?>


                <!-- Category: New Releases -->
                <div>
                    <h3 class="text-xl font-semibold px-4 mb-3">New Releases</h3>
                    <div class="flex space-x-4 overflow-x-auto no-scrollbar px-4">
                        <?php
                            // --- DATABASE LOGIC (CONCEPT) ---
                            // $newReleases = $pdo->query("SELECT * FROM movies WHERE type = 'movie' ORDER BY release_date DESC LIMIT 5");
                            
                            // Placeholder loop
                            $newReleases = [
                                ['id' => 2, 'title' => 'Movie 1', 'poster_url' => 'https://placehold.co/300x450/2a2a2a/ffffff?text=Movie+1', 'language' => 'Kannada', 'type' => 'movie'],
                                ['id' => 3, 'title' => 'Movie 2', 'poster_url' => 'https://placehold.co/300x450/3a3a3a/ffffff?text=Movie+2', 'language' => 'Telugu', 'type' => 'movie'],
                                ['id' => 4, 'title' => 'Movie 3', 'poster_url' => 'https://placehold.co/300x450/4a4a4a/ffffff?text=Movie+3', 'language' => 'Multi', 'type' => 'movie'],
                                ['id' => 5, 'title' => 'Movie 4', 'poster_url' => 'https://placehold.co/300x450/5a5a5a/ffffff?text=Movie+4', 'language' => 'Kannada', 'type' => 'movie'],
                            ];

                            foreach ($newReleases as $movie):
                                $detailsPage = $movie['type'] == 'series' ? 'series.php' : 'details.php';
                        ?>
                            <!-- Movie Card with Language Badge -->
                            <a href="<?php echo $detailsPage; ?>?id=<?php echo $movie['id']; ?>" class="flex-shrink-0 w-32 relative">
                                <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="rounded-lg w-full h-48 object-cover">
                                <span class="absolute top-2 left-2 bg-red-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                                    <?php echo htmlspecialchars($movie['language']); ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Category: All Time Hits -->
                <div>
                    <h3 class="text-xl font-semibold px-4 mb-3">All Time Hits</h3>
                    <div class="flex space-x-4 overflow-x-auto no-scrollbar px-4">
                        <?php
                            // --- DATABASE LOGIC (CONCEPT) ---
                            // $allTimeHits = $pdo->query("SELECT * FROM movies ORDER BY popularity DESC LIMIT 5");
                            
                            // Placeholder loop
                            $allTimeHits = [
                                ['id' => 10, 'title' => 'Hit 1', 'poster_url' => 'https://placehold.co/300x450/880808/ffffff?text=Hit+1', 'language' => 'Multi', 'type' => 'movie'],
                                ['id' => 11, 'title' => 'Hit 2', 'poster_url' => 'https://placehold.co/300x450/8B0000/ffffff?text=Hit+2', 'language' => 'Kannada', 'type' => 'movie'],
                                ['id' => 12, 'title' => 'Hit 3', 'poster_url' => 'https://placehold.co/300x450/A52A2A/ffffff?text=Hit+3', 'language' => 'Telugu', 'type' => 'movie'],
                            ];

                            foreach ($allTimeHits as $movie):
                                $detailsPage = $movie['type'] == 'series' ? 'series.php' : 'details.php';
                        ?>
                            <!-- Movie Card with Language Badge -->
                            <a href="<?php echo $detailsPage; ?>?id=<?php echo $movie['id']; ?>" class="flex-shrink-0 w-32 relative">
                                <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="rounded-lg w-full h-48 object-cover">
                                <span class="absolute top-2 left-2 bg-red-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                                    <?php echo htmlspecialchars($movie['language']); ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Category: Popular Series -->
                <div>
                    <h3 class="text-xl font-semibold px-4 mb-3">Popular Series</h3>
                    <div class="flex space-x-4 overflow-x-auto no-scrollbar px-4">
                        <?php
                            // $popularSeries = $pdo->query("SELECT * FROM movies WHERE type = 'series' ORDER BY popularity DESC LIMIT 5");
                            
                            $popularSeries = [
                                ['id' => 6, 'title' => 'Series A', 'poster_url' => 'https://placehold.co/300x450/222222/ffffff?text=Series+A', 'language' => 'Multi', 'type' => 'series'],
                                ['id' => 7, 'title' => 'Series B', 'poster_url' => 'https://placehold.co/300x450/333333/ffffff?text=Series+B', 'language' => 'Kannada', 'type' => 'series'],
                                ['id' => 8, 'title' => 'Series C', 'poster_url' => 'https://placehold.co/300x450/444444/ffffff?text=Series+C', 'language' => 'Telugu', 'type' => 'series'],
                            ];

                            foreach ($popularSeries as $series):
                        ?>
                            <!-- Series Card with Language Badge -->
                            <a href="series.php?id=<?php echo $series['id']; ?>" class="flex-shrink-0 w-32 relative">
                                <img src="<?php echo htmlspecialchars($series['poster_url']); ?>" alt="<?php echo htmlspecialchars($series['title']); ?>" class="rounded-lg w-full h-48 object-cover">
                                <span class="absolute top-2 left-2 bg-red-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                                    <?php echo htmlspecialchars($series['language']); ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Category: Kannada -->
                <div>
                    <h3 class="text-xl font-semibold px-4 mb-3">Kannada</h3>
                    <div class="flex space-x-4 overflow-x-auto no-scrollbar px-4">
                        <?php
                            // $kannadaMovies = $pdo->query("SELECT * FROM movies WHERE language = 'Kannada' ORDER BY release_date DESC LIMIT 5");
                            $kannadaMovies = [
                                ['id' => 2, 'title' => 'Movie 1', 'poster_url' => 'https://placehold.co/300x450/2a2a2a/ffffff?text=Movie+1', 'language' => 'Kannada', 'type' => 'movie'],
                                ['id' => 5, 'title' => 'Movie 4', 'poster_url' => 'https://placehold.co/300x450/5a5a5a/ffffff?text=Movie+4', 'language' => 'Kannada', 'type' => 'movie'],
                                ['id' => 11, 'title' => 'Hit 2', 'poster_url' => 'https://placehold.co/300x450/8B0000/ffffff?text=Hit+2', 'language' => 'Kannada', 'type' => 'movie'],
                            ];

                            foreach ($kannadaMovies as $movie):
                                $detailsPage = $movie['type'] == 'series' ? 'series.php' : 'details.php';
                        ?>
                            <a href="<?php echo $detailsPage; ?>?id=<?php echo $movie['id']; ?>" class="flex-shrink-0 w-32 relative">
                                <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="rounded-lg w-full h-48 object-cover">
                                <span class="absolute top-2 left-2 bg-red-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                                    <?php echo htmlspecialchars($movie['language']); ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Category: Telugu -->
                <div>
                    <h3 class="text-xl font-semibold px-4 mb-3">Telugu</h3>
                    <div class="flex space-x-4 overflow-x-auto no-scrollbar px-4">
                        <?php
                            // $teluguMovies = $pdo->query("SELECT * FROM movies WHERE language = 'Telugu' ORDER BY release_date DESC LIMIT 5");
                            $teluguMovies = [
                                ['id' => 3, 'title' => 'Movie 2', 'poster_url' => 'https://placehold.co/300x450/3a3a3a/ffffff?text=Movie+2', 'language' => 'Telugu', 'type' => 'movie'],
                                ['id' => 12, 'title' => 'Hit 3', 'poster_url' => 'https://placehold.co/300x450/A52A2A/ffffff?text=Hit+3', 'language' => 'Telugu', 'type' => 'movie'],
                            ];

                            foreach ($teluguMovies as $movie):
                                $detailsPage = $movie['type'] == 'series' ? 'series.php' : 'details.php';
                        ?>
                            <a href="<?php echo $detailsPage; ?>?id=<?php echo $movie['id']; ?>" class="flex-shrink-0 w-32 relative">
                                <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="rounded-lg w-full h-48 object-cover">
                                <span class="absolute top-2 left-2 bg-red-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                                    <?php echo htmlspecialchars($movie['language']); ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Category: Multi-language -->
                <div>
                    <h3 class="text-xl font-semibold px-4 mb-3">Multi-language</h3>
                    <div class="flex space-x-4 overflow-x-auto no-scrollbar px-4">
                        <?php
                            // $multiLanguageMovies = $pdo->query("SELECT * FROM movies WHERE language = 'Multi' ORDER BY release_date DESC LIMIT 5");
                            $multiLanguageMovies = [
                                ['id' => 4, 'title' => 'Movie 3', 'poster_url' => 'https://placehold.co/300x450/4a4a4a/ffffff?text=Movie+3', 'language' => 'Multi', 'type' => 'movie'],
                                ['id' => 10, 'title' => 'Hit 1', 'poster_url' => 'https://placehold.co/300x450/880808/ffffff?text=Hit+1', 'language' => 'Multi', 'type' => 'movie'],
                            ];

                            foreach ($multiLanguageMovies as $movie):
                                $detailsPage = $movie['type'] == 'series' ? 'series.php' : 'details.php';
                        ?>
                            <a href="<?php echo $detailsPage; ?>?id=<?php echo $movie['id']; ?>" class="flex-shrink-0 w-32 relative">
                                <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="rounded-lg w-full h-48 object-cover">
                                <span class="absolute top-2 left-2 bg-red-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                                    <?php echo htmlspecialchars($movie['language']); ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>


            </section>
        </main>
        
        <!-- Bottom Navigation Bar (Fixed) -->
        <nav class="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-gray-800 border-t border-gray-700 p-3 flex justify-around">
            <!-- Home (Active) -->
            <a href="index.php" class="flex flex-col items-center text-red-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                </svg>
                <span class="text-xs">Home</span>
            </a>
            <!-- Coming Soon -->
            <button class="flex flex-col items-center text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-xs">Soon</span>
            </button>
            <!-- Downloads -->
            <button class="flex flex-col items-center text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span class="text-xs">Downloads</span>
            </button>
        </nav>
    </div>

    <script>
        // No client-side JS needed for this static PHP page.
        // You would add JS for the search or profile popups.
    </script>

</body>
</html>

