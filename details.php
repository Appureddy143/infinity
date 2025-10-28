<!DOCTYPE html>
<html lang="en" class="bg-gray-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="font-sans text-white">

    <?php
        // --- DATABASE LOGIC (CONCEPT) ---
        
        // 1. Get the movie ID from the URL
        // Use filter_input for security
        $movieId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$movieId) {
            echo "Invalid Movie ID.";
            exit; // Stop the script
        }

        // 2. Connect to your Neon (PostgreSQL) database.
        // 3. Query for the movie details
        //    $stmt = $db->prepare("SELECT * FROM movies WHERE id = ? AND type = 'movie'");
        //    $stmt->execute([$movieId]);
        //    $movie = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // 4. If no movie is found, show an error
        //    if (!$movie) {
        //        echo "Movie not found.";
        //        exit;
        //    }

        // Placeholder data
        $movie = [
            'id' => $movieId,
            'title' => 'Placeholder Movie Title',
            'description' => 'This is a placeholder description for the movie. Here you would show the plot summary, cast, and director, all pulled from your database.',
            'poster_url' => 'https://placehold.co/600x800/1a1a1a/ffffff?text=Movie+Poster',
            'genre' => 'Action • Sci-Fi',
            'duration' => '2h 15m',
            'release_year' => 2024,
            'video_url' => 'https://test-videos.co.uk/vids/bigbuckbunny/mp4/h264/360/Big_Buck_Bunny_360_10s_1MB.mp4' // This now comes from your DB
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
            <h1 class="text-xl font-bold"><?php echo htmlspecialchars($movie['title']); ?></h1>
        </header>

        <!-- Main Content -->
        <main>
            <!-- Poster -->
            <section class="relative h-96">
                <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" 
                     alt="<?php echo htmlspecialchars($movie['title']); ?>" 
                     class="w-full h-full object-cover">
                <!-- Faded overlay at the bottom -->
                <div class="absolute bottom-0 left-0 w-full h-2/3 bg-gradient-to-t from-gray-900 to-transparent"></div>
            </section>

            <!-- Details Section -->
            <section class="p-6 -mt-20 relative z-10">
                
                <!-- Play Button -->
                <!-- 
                    This button would now open your video player.
                    You could either:
                    1. Link to a new `player.php?id=...` page
                    2. Re-implement the player modal from your original file,
                       but on THIS page instead of the index.
                -->
                <a href="player.php?id=<?php echo $movie['id']; ?>" class="w-full bg-red-600 text-white font-bold py-3 px-6 rounded-lg flex items-center justify-center space-x-2 hover:bg-red-700 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                    </svg>
                    <span>Play Movie</span>
                </a>
                
                <!-- Movie Info -->
                <h2 class="text-3xl font-bold mt-6"><?php echo htmlspecialchars($movie['title']); ?></h2>
                <div class="flex space-x-2 text-sm text-gray-400 mt-2">
                    <span><?php echo $movie['release_year']; ?></span>
                    <span>•</span>
                    <span><?php echo htmlspecialchars($movie['genre']); ?></span>
                    <span>•</span>
                    <span><?php echo htmlspecialchars($movie['duration']); ?></span>
                </div>

                <p class="text-gray-300 mt-4">
                    <?php echo htmlspecialchars($movie['description']); ?>
                </p>

                <!-- TODO: Add Cast, Director, etc. here -->

            </section>
        </main>
    </div>

</body>
</html>
