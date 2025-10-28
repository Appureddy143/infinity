<?php
    session_start();
    require_once 'db_connect.php';

    // 1. Check if user is an Admin
    if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
        header("Location: login.php");
        exit;
    }

    // 2. Fetch existing content to manage
    $content = [];
    try {
        $stmt = $pdo->query("SELECT movie_id, title, type, release_date FROM movies ORDER BY created_at DESC");
        $content = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $manage_error = "Could not fetch content list.";
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - YourStream</title>
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
        /* Style for the episode template */
        #episode-template { display: none; }
    </style>
</head>
<body class="antialiased">

    <!-- Admin Header -->
    <header class="bg-black shadow-lg shadow-zinc-900/50">
        <div class="container mx-auto max-w-4xl p-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-red-500">Admin Panel</h1>
            <div>
                <a href="index.php" class="text-sm text-gray-300 hover:text-red-500 mr-4">&larr; Back to Site</a>
                <a href="logout.php" class="text-sm text-gray-300 hover:text-red-500">Log Out</a>
            </div>
        </div>
    </header>

    <main class="container mx-auto max-w-4xl p-4 grid grid-cols-1 md:grid-cols-2 gap-8">
        
        <!-- Column 1: Add Content Forms -->
        <div class="space-y-8">
            <!-- Add Movie Form -->
            <section class="bg-black p-6 rounded-lg shadow-2xl">
                <h2 class="text-xl font-semibold mb-4 border-b border-zinc-700 pb-2">Add New Movie</h2>
                <form action="admin_add_movie.php" method="POST">
                    <div class="space-y-4">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-300">Title</label>
                            <input type="text" name="title" id="title" class="mt-1 block w-full rounded-md p-2" required>
                        </div>
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-300">Description</label>
                            <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md p-2"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="release_date" class="block text-sm font-medium text-gray-300">Release Date</label>
                                <input type="date" name="release_date" id="release_date" class="mt-1 block w-full rounded-md p-2">
                            </div>
                            <div>
                                <label for="genre" class="block text-sm font-medium text-gray-300">Genre</label>
                                <input type="text" name="genre" id="genre" class="mt-1 block w-full rounded-md p-2" placeholder="e.g., Action, Comedy">
                            </div>
                        </div>
                         <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="duration_minutes" class="block text-sm font-medium text-gray-300">Duration (minutes)</label>
                                <input type="number" name="duration_minutes" id="duration_minutes" class="mt-1 block w-full rounded-md p-2" required>
                            </div>
                            <div>
                                <label for="language" class="block text-sm font-medium text-gray-300">Language</label>
                                <select name="language" id="language" class="mt-1 block w-full rounded-md p-2">
                                    <option>English</option>
                                    <option>Kannada</option>
                                    <option>Telugu</option>
                                    <option>Multi-language</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label for="poster_url" class="block text-sm font-medium text-gray-300">Poster URL</label>
                            <input type="url" name="poster_url" id="poster_url" class="mt-1 block w-full rounded-md p-2" placeholder="httpsRead.co/...">
                        </div>
                        <div>
                            <label for="video_url" class="block text-sm font-medium text-gray-300">Video URL</label>
                            <input type="url" name="video_url" id="video_url" class="mt-1 block w-full rounded-md p-2" placeholder="http://.../movie.mp4">
                        </div>
                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg transition-colors duration-200">
                            Add Movie
                        </button>
                    </div>
                </form>
            </section>

            <!-- Add Series Form -->
            <section class="bg-black p-6 rounded-lg shadow-2xl">
                <h2 class="text-xl font-semibold mb-4 border-b border-zinc-700 pb-2">Add New Series</h2>
                <form action="admin_add_series.php" method="POST">
                    <!-- Series Details -->
                    <div class="space-y-4">
                        <div>
                            <label for="series_title" class="block text-sm font-medium text-gray-300">Series Title</label>
                            <input type="text" name="title" id="series_title" class="mt-1 block w-full rounded-md p-2" required>
                        </div>
                        <div>
                            <label for="series_description" class="block text-sm font-medium text-gray-300">Series Description</label>
                            <textarea name="description" id="series_description" rows="3" class="mt-1 block w-full rounded-md p-2"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                             <div>
                                <label for="series_release_date" class="block text-sm font-medium text-gray-300">Release Date</label>
                                <input type="date" name="release_date" id="series_release_date" class="mt-1 block w-full rounded-md p-2">
                            </div>
                            <div>
                                <label for="series_genre" class="block text-sm font-medium text-gray-300">Genre</label>
                                <input type="text" name="genre" id="series_genre" class="mt-1 block w-full rounded-md p-2" placeholder="e.g., Drama, Sci-Fi">
                            </div>
                        </div>
                        <div>
                            <label for="series_poster_url" class="block text-sm font-medium text-gray-300">Series Poster URL</label>
                            <input type="url" name="poster_url" id="series_poster_url" class="mt-1 block w-full rounded-md p-2" placeholder="httpsRead.co/...">
                        </div>
                        <div>
                            <label for="season_title" class="block text-sm font-medium text-gray-300">Season Title</label>
                            <input type="text" name="season_title" id="season_title" class="mt-1 block w-full rounded-md p-2" value="Season 1">
                        </div>
                        
                        <!-- Upload Type -->
                        <div class="pt-2">
                             <label class="block text-sm font-medium text-gray-300">Upload Type</label>
                             <div class="mt-2 flex gap-4">
                                <label class="flex items-center">
                                    <input type="radio" name="upload_type" value="episodic" class="form-radio text-red-500" checked onchange="toggleUploadType(this.value)">
                                    <span class="ml-2 text-sm">Episodic</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="upload_type" value="merged" class="form-radio text-red-500" onchange="toggleUploadType(this.value)">
                                    <span class="ml-2 text-sm">Merged Season File</span>
                                </label>
                             </div>
                        </div>

                        <!-- Merged Section (Hidden by default) -->
                        <div id="merged-section" class="hidden space-y-4 pt-4 border-t border-zinc-700">
                            <h3 class="text-md font-semibold">Merged Season File Details</h3>
                             <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-300">Duration (minutes)</label>
                                    <input type="number" name="merged_duration_minutes" class="mt-1 block w-full rounded-md p-2">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-300">Language</label>
                                    <select name="merged_language" class="mt-1 block w-full rounded-md p-2">
                                        <option>English</option>
                                        <option>Kannada</option>
                                        <option>Telugu</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300">Video URL</label>
                                <input type="url" name="merged_video_url" class="mt-1 block w-full rounded-md p-2" placeholder="http://.../season1_merged.mp4">
                            </div>
                        </div>

                        <!-- Episodic Section (Visible by default) -->
                        <div id="episodic-section" class="space-y-4 pt-4 border-t border-zinc-700">
                            <h3 class="text-md font-semibold">Episodes</h3>
                            <div id="episodes-container">
                                <!-- Episode 1 (Mandatory) -->
                                <div class="episode-item space-y-3 p-3 border border-zinc-700 rounded-lg">
                                    <div class="flex justify-between items-center">
                                        <label class="block text-sm font-medium text-gray-300">Episode 1</label>
                                    </div>
                                    <input type="text" name="ep_title[]" class="block w-full rounded-md p-2 text-sm" placeholder="Episode 1 Title" required>
                                    <input type="url" name="ep_video_url[]" class="block w-full rounded-md p-2 text-sm" placeholder="Video URL" required>
                                    <input type="url" name="ep_thumbnail_url[]" class="block w-full rounded-md p-2 text-sm" placeholder="Thumbnail URL (optional)">
                                    <div class="grid grid-cols-2 gap-4">
                                        <input type="number" name="ep_duration_minutes[]" class="block w-full rounded-md p-2 text-sm" placeholder="Duration (min)" required>
                                        <select name="ep_language[]" class="block w-full rounded-md p-2 text-sm">
                                            <option>English</option>
                                            <option>Kannada</option>
                                            <option>Telugu</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <button type="button" id="add-episode-btn" class="mt-2 text-sm text-red-500 hover:text-red-400 font-medium">
                                + Add Another Episode
                            </button>
                        </div>
                        
                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg transition-colors duration-200">
                            Add Series
                        </button>
                    </div>
                </form>
            </section>
        </div>

        <!-- Column 2: Manage Content -->
        <section class="bg-black p-6 rounded-lg shadow-2xl">
            <h2 class="text-xl font-semibold mb-4 border-b border-zinc-700 pb-2">Manage Content</h2>
            <?php if (isset($manage_error)): ?>
                <p class="text-red-400"><?php echo htmlspecialchars($manage_error); ?></p>
            <?php endif; ?>
            <div class="space-y-3 max-h-[1000px] overflow-y-auto">
                <?php foreach ($content as $item): ?>
                    <div class="flex items-center justify-between bg-zinc-900 p-3 rounded-lg">
                        <div class="flex-1 overflow-hidden">
                            <h3 class="font-semibold truncate"><?php echo htmlspecialchars($item['title']); ?></h3>
                            <p class="text-sm text-gray-400 capitalize">
                                <?php echo htmlspecialchars($item['type']); ?> &bull; <?php echo date('Y', strtotime($item['release_date'])); ?>
                            </p>
                        </div>
                        <div class="flex-shrink-0 flex gap-3 ml-4">
                            <?php if ($item['type'] == 'movie'): ?>
                                <a href="admin_edit_movie.php?movie_id=<?php echo $item['movie_id']; ?>" class="text-sm text-blue-400 hover:underline">Edit</a>
                            <?php else: ?>
                                <span class="text-sm text-gray-500" title="Series edit coming soon">Edit</span>
                            <?php endif; ?>
                            <a href="admin_delete.php?movie_id=<?php echo $item['movie_id']; ?>" class="text-sm text-red-500 hover:underline" onclick="return confirmDelete()">Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($content)): ?>
                    <p class="text-gray-500">No content added yet.</p>
                <?php endif; ?>
            </div>
        </section>

    </main>

    <!-- JS for dynamic forms -->
    <script>
        // Toggle between Episodic and Merged
        function toggleUploadType(type) {
            if (type === 'merged') {
                document.getElementById('merged-section').style.display = 'block';
                document.getElementById('episodic-section').style.display = 'none';
                // Remove 'required' from episodic inputs
                document.querySelectorAll('#episodic-section input[required]').forEach(el => el.required = false);
                // Add 'required' to merged inputs
                document.querySelector('input[name="merged_video_url"]').required = true;
                document.querySelector('input[name="merged_duration_minutes"]').required = true;
            } else {
                document.getElementById('merged-section').style.display = 'none';
                document.getElementById('episodic-section').style.display = 'block';
                // Add 'required' back to episodic inputs
                document.querySelectorAll('#episodic-section input[placeholder*="Title"]').forEach(el => el.required = true);
                document.querySelectorAll('#episodic-section input[placeholder*="Video URL"]').forEach(el => el.required = true);
                document.querySelectorAll('#episodic-section input[placeholder*="Duration"]').forEach(el => el.required = true);
                // Remove 'required' from merged inputs
                document.querySelector('input[name="merged_video_url"]').required = false;
                document.querySelector('input[name="merged_duration_minutes"]').required = false;
            }
        }
        
        // Add new episode fields
        let episodeCount = 1;
        document.getElementById('add-episode-btn').addEventListener('click', () => {
            episodeCount++;
            const container = document.getElementById('episodes-container');
            const newItem = document.createElement('div');
            newItem.className = 'episode-item space-y-3 p-3 border border-zinc-700 rounded-lg mt-3';
            newItem.innerHTML = `
                <div class="flex justify-between items-center">
                    <label class="block text-sm font-medium text-gray-300">Episode ${episodeCount}</label>
                    <button type="button" class="text-xs text-red-500 hover:underline" onclick="removeEpisode(this)">Remove</button>
                </div>
                <input type="text" name="ep_title[]" class="block w-full rounded-md p-2 text-sm" placeholder="Episode ${episodeCount} Title" required>
                <input type="url" name="ep_video_url[]" class="block w-full rounded-md p-2 text-sm" placeholder="Video URL" required>
                <input type="url" name="ep_thumbnail_url[]" class="block w-full rounded-md p-2 text-sm" placeholder="Thumbnail URL (optional)">
                <div class="grid grid-cols-2 gap-4">
                    <input type="number" name="ep_duration_minutes[]" class="block w-full rounded-md p-2 text-sm" placeholder="Duration (min)" required>
                    <select name="ep_language[]" class="block w-full rounded-md p-2 text-sm">
                        <option>English</option>
                        <option>Kannada</option>
                        <option>Telugu</option>
                    </select>
                </div>
            `;
            container.appendChild(newItem);
        });

        // Remove an episode
        function removeEpisode(button) {
            button.closest('.episode-item').remove();
            // We don't need to re-number, the array index in PHP will handle it.
        }

        // Custom confirm
        function confirmDelete() {
            // A simple modal could be built here, but for now, we'll use the browser confirm.
            // In a real iFrame, this might be blocked. A custom modal is the long-term solution.
            return confirm("Are you sure you want to delete this item? This action is permanent and will delete all associated seasons and episodes.");
        }

        // Initialize form
        toggleUploadType('episodic');
    </script>

</body>
</html>


