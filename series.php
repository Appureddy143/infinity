<!DOCTYPE html>
<html lang="en" class="bg-gray-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Series Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Styles for active tab */
        .tab-button.active {
            border-bottom-color: #E53E3E; /* red-600 */
            color: #FFF;
        }
    </style>
</head>
<body class="font-sans text-white">

    <?php
        // --- DATABASE LOGIC (CONCEPT) ---
        
        $seriesId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$seriesId) {
            echo "Invalid Series ID.";
            exit;
        }

        // --- Fetch Series Details ---
        // $stmt = $db->prepare("SELECT * FROM movies WHERE id = ? AND type = 'series'");
        // $stmt->execute([$seriesId]);
        // $series = $stmt->fetch(PDO::FETCH_ASSOC);
        // if (!$series) { echo "Series not found."; exit; }

        // --- Fetch Seasons ---
        // $stmt = $db->prepare("SELECT * FROM seasons WHERE series_id = ? ORDER BY season_number");
        // $stmt->execute([$seriesId]);
        // $seasons = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // --- Fetch Episodes (e.g., for Season 1 by default) ---
        // $defaultSeasonId = $seasons[0]['id'] ?? 0;
        // $stmt = $db->prepare("SELECT * FROM episodes WHERE season_id = ? ORDER BY episode_number");
        // $stmt->execute([$defaultSeasonId]);
        // $episodes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Placeholder data
        $series = [
            'id' => $seriesId,
            'title' => 'Placeholder Series Title',
            'description' => 'This is a placeholder description for the series. It would be loaded from your database.',
            'poster_url' => 'https://placehold.co/600x800/222222/ffffff?text=Series+Poster',
            'genre' => 'Drama • Thriller',
            'release_year' => 2023,
        ];
        
        $seasons = [
            ['id' => 1, 'season_number' => 1],
            ['id' => 2, 'season_number' => 2],
        ];

        // Placeholder episodes for Season 1
        $episodes = [
            ['id' => 101, 'episode_number' => 1, 'title' => 'The Beginning', 'thumbnail_url' => 'https://placehold.co/400x225/333/fff?text=Ep+1'],
            ['id' => 102, 'episode_number' => 2, 'title' => 'The Middle', 'thumbnail_url' => 'https://placehold.co/400x225/444/fff?text=Ep+2'],
            ['id' => 103, 'episode_number' => 3, 'title' => 'The End', 'thumbnail_url' => 'https://placehold.co/400x225/555/fff?text=Ep+3'],
        ];

    ?>

    <div class="max-w-md mx-auto min-h-screen">
        
        <!-- Header with Back Button -->
        <header class="p-4 flex items-center">
            <a href="index.php" class="mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h1 class="text-xl font-bold"><?php echo htmlspecialchars($series['title']); ?></h1>
        </header>

        <!-- Main Content -->
        <main>
            <!-- Poster -->
            <section class="relative h-64">
                <img src="<?php echo htmlspecialchars($series['poster_url']); ?>" 
                     alt="<?php echo htmlspecialchars($series['title']); ?>" 
                     class="w-full h-full object-cover">
                <div class="absolute bottom-0 left-0 w-full h-2/3 bg-gradient-to-t from-gray-900 to-transparent"></div>
            </section>

            <!-- Details Section -->
            <section class="p-6 -mt-16 relative z-10">
                <h2 class="text-3xl font-bold"><?php echo htmlspecialchars($series['title']); ?></h2>
                <div class="flex space-x-2 text-sm text-gray-400 mt-2">
                    <span><?php echo $series['release_year']; ?></span>
                    <span>•</span>
                    <span><?php echo htmlspecialchars($series['genre']); ?></span>
                </div>
                <p class="text-gray-300 mt-4">
                    <?php echo htmlspecialchars($series['description']); ?>
                </p>
            </section>

            <!-- Seasons & Episodes Section -->
            <section class="mt-4">
                <!-- Season Tabs -->
                <div class="flex border-b border-gray-700 px-6">
                    <?php foreach ($seasons as $index => $season): ?>
                        <button class="tab-button py-2 px-4 border-b-2 border-transparent text-gray-400 <?php echo $index == 0 ? 'active' : ''; ?>" data-season="<?php echo $season['season_number']; ?>">
                            Season <?php echo $season['season_number']; ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- Episode List -->
                <!-- TODO: You will need JavaScript to click these tabs and use
                     AJAX/fetch to load the episodes for the selected season. -->
                <div id="episode-list" class="space-y-4 p-6">
                    <?php foreach ($episodes as $episode): ?>
                        <a href="player.php?episode=<?php echo $episode['id']; ?>" class="flex items-center space-x-4">
                            <img src="<?php echo htmlspecialchars($episode['thumbnail_url']); ?>" alt="<?php echo htmlspecialchars($episode['title']); ?>" class="w-28 h-16 rounded-lg object-cover">
                            <div class="flex-grow">
                                <h4 class="font-semibold">Ep <?php echo $episode['episode_number']; ?>: <?php echo htmlspecialchars($episode['title']); ?></h4>
                                <!-- You would also put episode duration here -->
                            </div>
                            <!-- Play Icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        </main>
    </div>

</body>
</html>
