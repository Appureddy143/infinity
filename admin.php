<?php
    session_start();

    // --- NEW: Admin Security Check ---
    // If the user is not logged in, or they are not an admin, redirect them.
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        // You can redirect to home, or to a login page with an error.
        header('Location: login.php?error=admin_required');
        exit;
    }

    // You are a logged-in admin. Welcome!
    // We can include the db connection if we need to (e.g., to show stats)
    require_once 'db_connect.php';
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
        body {
            font-family: 'Inter', sans-serif;
            background-color: #1a1a1a; /* Darker bg for admin */
            color: #ffffff;
        }
        input, select {
            background-color: #2a2a2a;
            border-color: #4a4a4a;
        }
    </style>
</head>
<body class="antialiased">

    <div class="container mx-auto max-w-lg min-h-screen bg-black p-4">
        <header class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-red-500">Admin Panel</h1>
            <a href="index.php" class="text-sm text-gray-300 hover:text-white">&larr; Back to Site</a>
        </header>

        <!-- Status Message Area -->
        <?php if (isset($_GET['status'])): ?>
            <div class="mb-4 p-3 rounded-lg <?php echo $_GET['status'] == 'success' ? 'bg-green-600' : 'bg-red-600'; ?> text-white text-center">
                <?php
                    if ($_GET['status'] == 'success' && $_GET['type'] == 'movie') echo "Movie added successfully!";
                    if ($_GET['status'] == 'success' && $_GET['type'] == 'series') echo "Series added successfully!";
                    if ($_GET['status'] == 'error') echo "Error: " . htmlspecialchars($_GET['message']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Add Movie Form -->
        <section class="mb-8 p-4 bg-zinc-900 rounded-lg">
            <h2 class="text-xl font-semibold mb-4">Add New Movie</h2>
            <form action="admin_add_movie.php" method="POST" class="space-y-4">
                <div>
                    <label for="movie_title" class="block text-sm font-medium text-gray-300">Title</label>
                    <input type="text" name="title" id="movie_title" class="w-full mt-1 p-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-red-500" required>
                </div>
                <div>
                    <label for="movie_desc" class="block text-sm font-medium text-gray-300">Description</label>
                    <textarea name="description" id="movie_desc" rows="3" class="w-full mt-1 p-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="movie_poster" class="block text-sm font-medium text-gray-300">Poster URL</label>
                        <input type="url" name="poster_url" id="movie_poster" placeholder="https://..." class="w-full mt-1 p-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-red-500" required>
                    </div>
                     <div>
                        <label for="movie_video" class="block text-sm font-medium text-gray-300">Video URL</label>
                        <input type="url" name="video_url" id="movie_video" placeholder="https://.../video.mp4" class="w-full mt-1 p-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-red-500" required>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label for="movie_release" class="block text-sm font-medium text-gray-300">Release Date</label>
                        <input type="date" name="release_date" id="movie_release" class="w-full mt-1 p-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label for="movie_genre" class="block text-sm font-medium text-gray-300">Genre</label>
                        <input type="text" name="genre" id="movie_genre" placeholder="Action, Drama" class="w-full mt-1 p-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label for="movie_lang" class="block text-sm font-medium text-gray-300">Language</label>
                        <select name="language" id="movie_lang" class="w-full mt-1 p-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="English">English</option>
                            <option value="Kannada">Kannada</option>
                            <option value="Telugu">Telugu</option>
                            <option value="Hindi">Hindi</option>
                            <option value="Multi-language">Multi-language</option>
                        </select>
                    </div>
                </div>
                 <div>
                    <label for="movie_duration" class="block text-sm font-medium text-gray-300">Duration (in minutes)</label>
                    <input type="number" name="duration_minutes" id="movie_duration" class="w-full mt-1 p-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-red-500" required>
                </div>
                <button type="submit" class="w-full py-2 px-4 bg-red-600 hover:bg-red-700 rounded-lg text-white font-semibold transition-colors duration-200">
                    Add Movie
                </button>
            </form>
        </section>

        <!-- Add Series Form -->
        <section class="mb-8 p-4 bg-zinc-900 rounded-lg">
            <h2 class="text-xl font-semibold mb-4">Add New Series</h2>
            <form action="admin_add_series.php" method="POST" class="space-y-4" id="series-form">
                <!-- Series Details -->
                <div class="space-y-4 p-3 border border-zinc-700 rounded-lg">
                    <h3 class="font-semibold text-lg">Series Details</h3>
                    <div>
                        <label for="series_title" class="block text-sm font-medium text-gray-300">Title</label>
                        <input type="text" name="title" id="series_title" class="w-full mt-1 p-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-red-500" required>
                    </div>
                    <div>
                        <label for="series_desc" class="block text-sm font-medium text-gray-300">Description</label>
                        <textarea name="description" id="series_desc" rows="3" class="w-full mt-1 p-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="series_poster" class="block text-sm font-medium text-gray-300">Poster URL</label>
                            <input type="url" name="poster_url" id="series_poster" placeholder="https://..." class="w-full mt-1 p-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-red-500" required>
                        </div>
                        <div>
                            <label for="series_genre" class="block text-sm font-medium text-gray-300">Genre</label>
                            <input type="text" name="genre" id="series_genre" placeholder="Sci-Fi, Adventure" class="w-full mt-1 p-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                    </div>
                    <div>
                        <label for="series_release" class="block text-sm font-medium text-gray-300">Release Date</label>
                        <input type="date" name="release_date" id="series_release" class="w-full mt-1 p-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <!-- Season Details -->
                <div class="space-y-4 p-3 border border-zinc-700 rounded-lg">
                    <h3 class="font-semibold text-lg">Season Details</h3>
                    <div class="grid grid-cols-2 gap-4">
                         <div>
                            <label for="season_number" class="block text-sm font-medium text-gray-300">Season Number</label>
                            <input type="number" name="season_number" id="season_number" value="1" class="w-full mt-1 p-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-red-500" required>
                        </div>
                        <div>
                            <label for="season_title" class="block text-sm font-medium text-gray-300">Season Title (Optional)</label>
                            <input type="text" name="season_title" id="season_title" placeholder="e.g., The First Chapter" class="w-full mt-1 p-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                    </div>
                    
                    <!-- Episode Type Toggle -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300">Episode Format</label>
                        <select name="episode_format" id="episode-format-select" class="w-full mt-1 p-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="episodic">Episodic (Add one by one)</option>
                            <option value="merged">Merged (One file for whole season)</option>
                        </select>
                    </div>
                </div>

                <!-- Merged File Container (Hidden by default) -->
                <div id="merged-file-container" class="space-y-4 p-3 border border-dashed border-zinc-600 rounded-lg hidden">
                    <h3 class="font-semibold text-lg">Merged Season File</h3>
                    <p class="text-sm text-gray-400">Use this if the entire season is one single video file.</p>
                     <div>
                        <label for="merged_video_url" class="block text-sm font-medium text-gray-300">Video URL</label>
                        <input type="url" name="merged_video_url" id="merged_video_url" placeholder="https://.../season1_merged.mp4" class="w-full mt-1 p-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="merged_duration" class="block text-sm font-medium text-gray-300">Total Duration (in minutes)</label>
                            <input type="number" name="merged_duration_minutes" id="merged_duration" class="w-full mt-1 p-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                         <div>
                            <label for="merged_lang" class="block text-sm font-medium text-gray-300">Language</label>
                            <select name="merged_language" id="merged_lang" class="w-full mt-1 p-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-red-500">
                                <option value="English">English</option>
                                <option value="Kannada">Kannada</option>
                                <option value="Telugu">Telugu</option>
                                <option value="Hindi">Hindi</option>
                                <option value="Multi-language">Multi-language</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Episodic Container (Visible by default) -->
                <div id="episodic-container" class="space-y-4">
                    <!-- Dynamic episode fields will be injected here -->
                </div>
                
                <button type="button" id="add-episode-btn" class="w-full py-2 px-4 bg-zinc-700 hover:bg-zinc-600 rounded-lg text-white font-semibold transition-colors duration-200">
                    Add Another Episode
                </button>

                <button type="submit" class="w-full py-2 px-4 bg-red-600 hover:bg-red-700 rounded-lg text-white font-semibold transition-colors duration-200">
                    Add Series
                </button>
            </form>
        </section>

    </div>

    <!-- Episode Template -->
    <template id="episode-template">
        <div class="episode-field-group space-y-4 p-3 border border-dashed border-zinc-600 rounded-lg">
            <div class="flex justify-between items-center">
                <h3 class="font-semibold text-lg">Episode <span class="episode-number">1</span></h3>
                <button type="button" class="remove-episode-btn text-xs text-red-400 hover:text-red-300">&times; Remove</button>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300">Episode Number</label>
                    <input type="number" name="episodes[0][number]" value="1" class="ep-number-input w-full mt-1 p-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-red-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300">Title</label>
                    <input type="text" name="episodes[0][title]" placeholder="e.g., The Pilot" class="w-full mt-1 p-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-red-500" required>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300">Video URL</label>
                <input type="url" name="episodes[0][video_url]" placeholder="https://.../s1e1.mp4" class="w-full mt-1 p-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-red-500" required>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300">Thumbnail URL</label>
                    <input type="url" name="episodes[0][thumbnail_url]" placeholder="https://.../s1e1.jpg" class="w-full mt-1 p-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300">Duration (in minutes)</label>
                    <input type="number" name="episodes[0][duration_minutes]" class="w-full mt-1 p-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-red-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300">Language</label>
                    <select name="episodes[0][language]" class="w-full mt-1 p-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="English">English</option>
                        <option value="Kannada">Kannada</option>
                        <option value="Telugu">Telugu</option>
                        <option value="Hindi">Hindi</option>
                        <option value="Multi-language">Multi-language</option>
                    </select>
                </div>
            </div>
        </div>
    </template>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const seriesForm = document.getElementById('series-form');
            const episodicContainer = document.getElementById('episodic-container');
            const mergedContainer = document.getElementById('merged-file-container');
            const episodeTemplate = document.getElementById('episode-template');
            const addEpisodeBtn = document.getElementById('add-episode-btn');
            const episodeFormatSelect = document.getElementById('episode-format-select');

            let episodeCount = 0;

            function addEpisodeField() {
                const newEpisode = episodeTemplate.content.cloneNode(true);
                const newIndex = episodeCount;
                
                // Update identifiers
                newEpisode.querySelector('.episode-number').textContent = newIndex + 1;
                newEpisode.querySelector('.ep-number-input').value = newIndex + 1;
                
                // Update 'name' attributes for PHP array
                newEpisode.querySelectorAll('[name]').forEach(el => {
                    let name = el.getAttribute('name');
                    el.setAttribute('name', name.replace('episodes[0]', `episodes[${newIndex}]`));
                });

                // Add remove functionality
                newEpisode.querySelector('.remove-episode-btn').addEventListener('click', function(e) {
                    e.target.closest('.episode-field-group').remove();
                    updateEpisodeNumbers();
                });
                
                episodicContainer.appendChild(newEpisode);
                episodeCount++;
                updateEpisodeNumbers();
            }

            function updateEpisodeNumbers() {
                const allEpisodeGroups = episodicContainer.querySelectorAll('.episode-field-group');
                allEpisodeGroups.forEach((group, index) => {
                    group.querySelector('.episode-number').textContent = index + 1;
                    group.querySelector('.ep-number-input').value = index + 1;
                    
                    // Update 'name' attributes to be sequential
                    group.querySelectorAll('[name]').forEach(el => {
                        let name = el.getAttribute('name');
                        el.setAttribute('name', name.replace(/episodes\[\d+\]/, `episodes[${index}]`));
                    });
                });
                episodeCount = allEpisodeGroups.length;
            }

            // Toggle between episode formats
            episodeFormatSelect.addEventListener('change', function() {
                if (this.value === 'merged') {
                    mergedContainer.classList.remove('hidden');
                    episodicContainer.classList.add('hidden');
                    addEpisodeBtn.classList.add('hidden');
                } else { // 'episodic'
                    mergedContainer.classList.add('hidden');
                    episodicContainer.classList.remove('hidden');
                    addEpisodeBtn.classList.remove('hidden');
                }
            });

            // Add episode button
            addEpisodeBtn.addEventListener('click', addEpisodeField);

            // Add one episode field by default
            addEpisodeField();
        });
    </script>
</body>
</html>


