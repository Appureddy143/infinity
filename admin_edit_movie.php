<?php
    session_start();
    require_once 'db_connect.php';

    // 1. Check if user is an Admin
    if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
        header("Location: login.php");
        exit;
    }

    $error = '';
    $movie = null;

    // 2. Check for movie_id in URL
    if (!isset($_GET['movie_id'])) {
        $error = "No movie ID provided.";
    } else {
        $movie_id = intval($_GET['movie_id']);
        
        // 3. Fetch movie data from database
        try {
            $stmt = $pdo->prepare("SELECT * FROM movies WHERE movie_id = ? AND type = 'movie'");
            $stmt->execute([$movie_id]);
            $movie = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$movie) {
                $error = "Movie not found or it is a series.";
            } else {
                // Convert duration back to minutes
                $movie['duration_minutes'] = round($movie['duration_seconds'] / 60);
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
    <title>Edit Movie - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f0f0f; color: #ffffff; }
        input, select, textarea {
            background-color: #1f2937;
            border: 1px solid #374151;
            color: #ffffff;
        }
        input:focus, select:focus, textarea:focus {
            border-color: #ef4444;
            ring: 1px;
            ring-color: #ef4444;
        }
    </style>
</head>
<body class="antialiased">

    <!-- Admin Header -->
    <header class="bg-black shadow-lg shadow-zinc-900/50">
        <div class="container mx-auto max-w-2xl p-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-red-500">Edit Movie</h1>
            <div>
                <a href="admin.php" class="text-sm text-gray-300 hover:text-red-500 mr-4">&larr; Back to Admin</a>
                <a href="logout.php" class="text-sm text-gray-300 hover:text-red-500">Log Out</a>
            </div>
        </div>
    </header>

    <main class="container mx-auto max-w-2xl p-4">
        
        <?php if ($error): ?>
            <div class="bg-red-900 border border-red-700 text-red-100 px-4 py-3 rounded-lg text-center">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php elseif ($movie): ?>
            <!-- Edit Movie Form -->
            <section class="bg-black p-6 rounded-lg shadow-2xl">
                <form action="admin_update_movie.php" method="POST">
                    <!-- Hidden field to pass the movie ID -->
                    <input type="hidden" name="movie_id" value="<?php echo htmlspecialchars($movie['movie_id']); ?>">
                    
                    <div class="space-y-4">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-300">Title</label>
                            <input type="text" name="title" id="title" class="mt-1 block w-full rounded-md p-2" value="<?php echo htmlspecialchars($movie['title']); ?>" required>
                        </div>
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-300">Description</label>
                            <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md p-2"><?php echo htmlspecialchars($movie['description']); ?></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="release_date" class="block text-sm font-medium text-gray-300">Release Date</label>
                                <input type="date" name="release_date" id="release_date" class="mt-1 block w-full rounded-md p-2" value="<?php echo htmlspecialchars($movie['release_date']); ?>">
                            </div>
                            <div>
                                <label for="genre" class="block text-sm font-medium text-gray-300">Genre</label>
                                <input type="text" name="genre" id="genre" class="mt-1 block w-full rounded-md p-2" value="<?php echo htmlspecialchars($movie['genre']); ?>" placeholder="e.g., Action, Comedy">
                            </div>
                        </div>
                         <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="duration_minutes" class="block text-sm font-medium text-gray-300">Duration (minutes)</label>
                                <input type="number" name="duration_minutes" id="duration_minutes" class="mt-1 block w-full rounded-md p-2" value="<?php echo htmlspecialchars($movie['duration_minutes']); ?>" required>
                            </div>
                            <div>
                                <label for="language" class="block text-sm font-medium text-gray-300">Language</label>
                                <select name="language" id="language" class="mt-1 block w-full rounded-md p-2">
                                    <option <?php echo $movie['language'] == 'English' ? 'selected' : ''; ?>>English</option>
                                    <option <?php echo $movie['language'] == 'Kannada' ? 'selected' : ''; ?>>Kannada</option>
                                    <option <?php echo $movie['language'] == 'Telugu' ? 'selected' : ''; ?>>Telugu</option>
                                    <option <?php echo $movie['language'] == 'Multi-language' ? 'selected' : ''; ?>>Multi-language</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label for="poster_url" class="block text-sm font-medium text-gray-300">Poster URL</label>
                            <input type="url" name="poster_url" id="poster_url" class="mt-1 block w-full rounded-md p-2" value="<?php echo htmlspecialchars($movie['poster_url']); ?>" placeholder="httpsRead.co/...">
                        </div>
                        <div>
                            <label for="video_url" class="block text-sm font-medium text-gray-300">Video URL</label>
                            <input type="url" name="video_url" id="video_url" class="mt-1 block w-full rounded-md p-2" value="<?php echo htmlspecialchars($movie['video_url']); ?>" placeholder="http://.../movie.mp4">
                        </div>
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition-colors duration-200">
                            Save Changes
                        </button>
                    </div>
                </form>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>