<!DOCTYPE html>
<html lang="en" class="bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - MyStream</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="font-sans">

    <?php
        session_start();
        
        // --- SIMPLE PASSWORD PROTECTION ---
        // In a real app, you'd have a separate admin login.
        $ADMIN_PASSWORD = "your_secret_password"; // CHANGE THIS
        $isLoggedIn = (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true);
        $errorMessage = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin_login'])) {
            if (isset($_POST['password']) && $_POST['password'] === $ADMIN_PASSWORD) {
                $_SESSION['admin_logged_in'] = true;
                $isLoggedIn = true;
            } else {
                $errorMessage = "Invalid password.";
            }
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_movie'])) {
            // --- DATABASE LOGIC: ADD MOVIE ---
            // 1. Connect to Neon DB: $pdo = new PDO($dsn, ...);
            // 2. Get form data
            $title = $_POST['title'];
            $description = $_POST['description'];
            $poster_url = $_POST['poster_url']; // URL from Backblaze, etc.
            $video_url = $_POST['video_url']; // URL from Backblaze, etc.
            $language = $_POST['language'];
            $genre = $_POST['genre'];
            $duration = $_POST['duration']; // e.g., "2h 15m"
            // 3. Insert into `movies` table
            // $sql = "INSERT INTO movies (title, description, poster_url, video_url, language, genre, duration, type, release_date) 
            //         VALUES (?, ?, ?, ?, ?, ?, ?, 'movie', NOW())";
            // $stmt = $pdo->prepare($sql);
            // $stmt->execute([$title, $description, $poster_url, $video_url, $language, $genre, $duration]);
            
            // $successMessage = "Movie '$title' added successfully!";
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_series'])) {
            // --- DATABASE LOGIC: ADD SERIES ---
            // 1. Connect to Neon DB
            // 2. Get form data
            $title = $_POST['title'];
            $description = $_POST['description'];
            $poster_url = $_POST['poster_url'];
            $language = $_POST['language'];
            $genre = $_POST['genre'];
            // 3. Insert into `movies` table with type 'series'
            // $sql = "INSERT INTO movies (title, description, poster_url, language, genre, type, release_date) 
            //         VALUES (?, ?, ?, ?, ?, 'series', NOW())";
            // $stmt = $pdo->prepare($sql);
            // $stmt->execute([$title, $description, $poster_url, $language, $genre]);
            // $seriesId = $pdo->lastInsertId(); // Get the ID of the new series
            
            // 4. Loop through seasons/episodes and add them
            // This is complex, but as a concept:
            // $sql_season = "INSERT INTO seasons (movie_id, season_number, title) VALUES (?, ?, ?)";
            // $sql_episode = "INSERT INTO episodes (season_id, episode_number, title, video_url, duration) VALUES (?, ?, ?, ?, ?)";
            
            // $successMessage = "Series '$title' added successfully!";
        }
    ?>

    <?php if (!$isLoggedIn): ?>
        <!-- Admin Login Screen -->
        <div class="max-w-md mx-auto min-h-screen flex flex-col justify-center items-center p-6 bg-gray-900">
            <h1 class="text-3xl font-bold text-red-600 mb-6">Admin Login</h1>
            <form action="admin.php" method="POST" class="w-full">
                <?php if ($errorMessage): ?>
                    <p class="text-red-400 mb-4"><?php echo $errorMessage; ?></p>
                <?php endif; ?>
                <label for="password" class="block text-sm font-medium text-gray-300">Password</label>
                <input type="password" name="password" id="password" required
                       class="mt-1 block w-full bg-gray-800 border border-gray-700 rounded-lg shadow-sm py-3 px-4 text-white focus:outline-none focus:border-red-500">
                <button type="submit" name="admin_login"
                        class="w-full bg-red-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-red-700 transition mt-4">
                    Login
                </button>
            </form>
        </div>
    <?php else: ?>
        <!-- Main Admin Panel -->
        <div class="max-w-2xl mx-auto p-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-6">MyStream Admin Panel</h1>

            <?php if (isset($successMessage)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                    <?php echo $successMessage; ?>
                </div>
            <?php endif; ?>

            <!-- Add Movie Form -->
            <section class="bg-white p-6 rounded-lg shadow-lg mb-8">
                <h2 class="text-2xl font-semibold mb-4">Add New Movie</h2>
                <form action="admin.php" method="POST" class="space-y-4">
                    <div>
                        <label for="movie-title" class="block text-sm font-medium text-gray-700">Title</label>
                        <input type="text" name="title" id="movie-title" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>
                    <div>
                        <label for="movie-desc" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" id="movie-desc" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"></textarea>
                    </div>
                    <div>
                        <label for="movie-poster" class="block text-sm font-medium text-gray-700">Poster URL</label>
                        <input type="text" name="poster_url" id="movie-poster" placeholder="https://my-storage.com/poster.jpg" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>
                    <div>
                        <label for="movie-video" class="block text-sm font-medium text-gray-700">Video URL</label>
                        <input type="text" name="video_url" id="movie-video" placeholder="https://my-storage.com/movie.mp4" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label for="movie-lang" class="block text-sm font-medium text-gray-700">Language</label>
                            <select name="language" id="movie-lang" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                <option>Kannada</option>
                                <option>Telugu</option>
                                <option>Multi</option>
                                <option>English</option>
                                <option>Hindi</option>
                            </select>
                        </div>
                        <div>
                            <label for="movie-genre" class="block text-sm font-medium text-gray-700">Genre</label>
                            <input type="text" name="genre" id="movie-genre" placeholder="Action" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                        </div>
                         <div>
                            <label for="movie-duration" class="block text-sm font-medium text-gray-700">Duration</label>
                            <input type="text" name="duration" id="movie-duration" placeholder="2h 15m" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                        </div>
                    </div>
                    <button type="submit" name="add_movie" class="w-full bg-blue-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-blue-700 transition">
                        Add Movie
                    </button>
                </form>
            </section>

            <!-- Add Series Form -->
            <section class="bg-white p-6 rounded-lg shadow-lg">
                <h2 class="text-2xl font-semibold mb-4">Add New Series</h2>
                <form action="admin.php" method="POST" class="space-y-4">
                    <!-- Basic series info (same as movie, but no video_url or duration) -->
                     <div>
                        <label for="series-title" class="block text-sm font-medium text-gray-700">Title</label>
                        <input type="text" name="title" id="series-title" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>
                    <div>
                        <label for="series-desc" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" id="series-desc" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"></textarea>
                    </div>
                    <div>
                        <label for="series-poster" class="block text-sm font-medium text-gray-700">Poster URL</label>
                        <input type="text" name="poster_url" id="series-poster" placeholder="https://my-storage.com/series-poster.jpg" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>
                     <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="series-lang" class="block text-sm font-medium text-gray-700">Language</label>
                            <select name="language" id="series-lang" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                <option>Kannada</option>
                                <option>Telugu</option>
                                <option>Multi</option>
                                <option>English</option>
                                <option>Hindi</option>
                            </select>
                        </div>
                        <div>
                            <label for="series-genre" class="block text-sm font-medium text-gray-700">Genre</label>
                            <input type="text" name="genre" id="series-genre" placeholder="Drama" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                        </div>
                    </div>
                    
                    <p class="text-gray-600 text-sm">Note: Add seasons and episodes from the 'Edit Series' page after creating this entry.</p>

                    <button type="submit" name="add_series" class="w-full bg-green-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-green-700 transition">
                        Add Series
                    </button>
                </form>
            </section>
        </div>
    <?php endif; ?>
    
</body>
</html>
