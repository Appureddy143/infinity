<?php
    session_start();
    require_once 'db_connect.php'; // Use our new connection file

    // --- Data Initialization ---
    $search_query = "";
    $results = [];
    $error = null;

    if (isset($_GET['q'])) {
        $search_query = trim($_GET['q']);

        if (empty($search_query)) {
            $error = "Please enter a search term.";
        } else {
            try {
                // --- Perform REAL Search Query ---
                // We use ILIKE for case-insensitive search in PostgreSQL
                $like_query = "%" . $search_query . "%";
                
                $stmt = $pdo->prepare("
                    SELECT * FROM movies 
                    WHERE 
                        title ILIKE ? OR 
                        genre ILIKE ? OR 
                        description ILIKE ?
                    ORDER BY 
                        release_date DESC
                    LIMIT 50
                ");
                
                $stmt->execute([$like_query, $like_query, $like_query]);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            } catch (PDOException $e) {
                error_log($e->getMessage()); // Log error
                $error = "An error occurred during the search. Please try again.";
            }
        }
    } else {
        $error = "No search term provided.";
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - YourStream</title>
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

    <div class="container mx-auto max-w-lg min-h-screen bg-black p-4">

        <!-- Back Button & Header -->
        <header class="mb-4 flex items-center justify-between">
            <a href="index.php" class="text-gray-300 hover:text-white transition-colors duration-200 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                Back
            </a>
            <h1 class="text-xl font-bold text-red-500">Search Results</h1>
        </header>

        <!-- Search Form (optional, for re-searching) -->
        <div class="mb-6">
            <form action="search.php" method="GET" class="relative">
                <input type="search" name="q" value="<?php echo htmlspecialchars($search_query); ?>" class="w-full bg-zinc-800 text-white placeholder-gray-400 rounded-lg py-2.5 px-4 focus:outline-none focus:ring-2 focus:ring-red-500" required>
                <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                </button>
            </form>
        </div>

        <!-- Results Grid -->
        <main>
            <?php if ($error): ?>
                <div class="text-center text-gray-400 p-8">
                    <p><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php elseif (empty($results) && !empty($search_query)): ?>
                <div class="text-center text-gray-400 p-8">
                    <p>No results found for "<strong><?php echo htmlspecialchars($search_query); ?></strong>".</p>
                    <p class="text-sm mt-2">Try searching for a different movie or series.</p>
                </div>
            <?php elseif (!empty($results)): ?>
                <p class="text-gray-300 mb-4">Showing results for "<strong><?php echo htmlspecialchars($search_query); ?></strong>"</p>
                
                <div class="grid grid-cols-3 gap-3">
                    <?php foreach ($results as $item): ?>
                        <?php
                            // Determine link based on type
                            $details_link = "details.php?id=" . $item['movie_id'];
                            if (isset($item['type']) && $item['type'] == 'series') {
                                $details_link = "series.php?id=" . $item['movie_id'];
                            }
                        ?>
                        <a href="<?php echo htmlspecialchars($details_link); ?>" class="block relative rounded-lg overflow-hidden group">
                            <img src="<?php echo htmlspecialchars($item['poster_url']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="w-full h-auto object-cover transition-transform duration-300 group-hover:scale-105" onerror="this.src='https://placehold.co/400x600/1a1a1a/ffffff?text=Poster+Error'">
                            <span class="absolute top-1 left-1 bg-black bg-opacity-70 text-white text-xs px-2 py-0.5 rounded"><?php echo htmlspecialchars($item['language']); ?></span>
                            <div class="absolute bottom-0 left-0 right-0 p-2 bg-gradient-to-t from-black via-black/70 to-transparent">
                                <h3 class="text-sm font-medium text-white truncate"><?php echo htmlspecialchars($item['title']); ?></h3>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>

    </div>

</body>
</html>


