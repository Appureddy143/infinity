<?php
    session_start();
    require_once 'db_connect.php';

    // 1. Check if user is an Admin
    if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
        header("Location: login.php");
        exit;
    }

    $error = '';
    $series = null;
    $seasons = [];
    $episodes = []; // Will be keyed by season_id

    // 2. Check for movie_id in URL
    if (!isset($_GET['movie_id'])) {
        $error = "No series ID provided.";
    } else {
        $movie_id = intval($_GET['movie_id']);
        
        // 3. Fetch series data
        try {
            $stmt = $pdo->prepare("SELECT * FROM movies WHERE movie_id = ? AND type = 'series'");
            $stmt->execute([$movie_id]);
            $series = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$series) {
                $error = "Series not found or it is a movie.";
            } else {
                // 4. Fetch all seasons for this series
                $stmt_seasons = $pdo->prepare("SELECT * FROM seasons WHERE movie_id = ? ORDER BY season_number");
                $stmt_seasons->execute([$movie_id]);
                $seasons = $stmt_seasons->fetchAll(PDO::FETCH_ASSOC);

                // 5. Fetch all episodes for these seasons
                if (!empty($seasons)) {
                    $season_ids = array_map(fn($s) => $s['season_id'], $seasons);
                    $in_query = implode(',', array_fill(0, count($season_ids), '?'));

                    $stmt_episodes = $pdo->prepare("SELECT * FROM episodes WHERE season_id IN ($in_query) ORDER BY episode_number");
                    $stmt_episodes->execute($season_ids);

                    while ($episode = $stmt_episodes->fetch(PDO::FETCH_ASSOC)) {
                        // Convert duration back to minutes for the form
                        $episode['duration_minutes'] = round($episode['duration_seconds'] / 60);
                        $episodes[$episode['season_id']][] = $episode;
                    }
                }
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
    <title>Edit Series - Admin Panel</title>
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
        .season-block {
            background-color: #111827; /* gray-900 */
            border: 1px solid #374151; /* gray-700 */
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-top: 1.5rem;
        }
        .episode-block {
            background-color: #1f2937; /* gray-800 */
            border: 1px solid #4b5563; /* gray-600 */
            border-radius: 0.5rem;
            padding: 1rem;
            margin-top: 1rem;
        }
    </style>
</head>
<body class="antialiased">

    <!-- Admin Header -->
    <header class="bg-black shadow-lg shadow-zinc-900/50 sticky top-0 z-50">
        <div class="container mx-auto max-w-4xl p-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-red-500">Edit Series</h1>
            <div>
                <a href="admin.php" class="text-sm text-gray-300 hover:text-red-500 mr-4">&larr; Back to Admin</a>
                <a href="logout.php" class="text-sm text-gray-300 hover:text-red-500">Log Out</a>
            </div>
        </div>
    </header>

    <main class="container mx-auto max-w-4xl p-4">
        
        <?php if ($error): ?>
            <div class="bg-red-900 border border-red-700 text-red-100 px-4 py-3 rounded-lg text-center">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php elseif ($series): ?>
            <form action="admin_update_series.php" method="POST">
                <input type="hidden" name="movie_id" value="<?php echo htmlspecialchars($series['movie_id']); ?>">
                
                <!-- Main Series Details -->
                <section class="bg-black p-6 rounded-lg shadow-2xl">
                    <h2 class="text-xl font-semibold mb-4">Series Details</h2>
                    <div class="space-y-4">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-300">Series Title</label>
                            <input type="text" name="title" id="title" class="mt-1 block w-full rounded-md p-2" value="<?php echo htmlspecialchars($series['title']); ?>" required>
                        </div>
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-300">Description</label>
                            <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md p-2"><?php echo htmlspecialchars($series['description']); ?></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="release_date" class="block text-sm font-medium text-gray-300">Release Date</label>
                                <input type="date" name="release_date" id="release_date" class="mt-1 block w-full rounded-md p-2" value="<?php echo htmlspecialchars($series['release_date']); ?>">
                            </div>
                            <div>
                                <label for="genre" class="block text-sm font-medium text-gray-300">Genre</label>
                                <input type="text" name="genre" id="genre" class="mt-1 block w-full rounded-md p-2" value="<?php echo htmlspecialchars($series['genre']); ?>">
                            </div>
                        </div>
                        <div>
                            <label for="poster_url" class="block text-sm font-medium text-gray-300">Poster URL</label>
                            <input type="url" name="poster_url" id="poster_url" class="mt-1 block w-full rounded-md p-2" value="<?php echo htmlspecialchars($series['poster_url']); ?>">
                        </div>
                    </div>
                </section>

                <!-- Seasons & Episodes -->
                <div id="seasons-container" class="mt-6">
                    <?php if (empty($seasons)): ?>
                        <p class="text-gray-400 text-center">No seasons found. Add one below.</p>
                    <?php endif; ?>
                    <?php foreach ($seasons as $season): ?>
                        <div class="season-block" id="season-<?php echo $season['season_id']; ?>">
                            <input type="hidden" name="season_id[]" value="<?php echo $season['season_id']; ?>">
                            <div class="flex justify-between items-center">
                                <h3 class="text-lg font-semibold">Edit Season</h3>
                                <button type="button" class="text-xs text-red-500 hover:underline" onclick="markForDeletion(this, 'season')">Delete Season</button>
                            </div>
                            <div class="grid grid-cols-2 gap-4 mt-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-300">Season Title</label>
                                    <input type="text" name="season_title[<?php echo $season['season_id']; ?>]" class="mt-1 block w-full rounded-md p-2" value="<?php echo htmlspecialchars($season['title']); ?>" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-300">Season Number</label>
                                    <input type="number" name="season_number[<?php echo $season['season_id']; ?>]" class="mt-1 block w-full rounded-md p-2" value="<?php echo htmlspecialchars($season['season_number']); ?>" required>
                                </div>
                            </div>
                            
                            <!-- Episodes for this season -->
                            <div class="episodes-container mt-4">
                                <?php if (!empty($episodes[$season['season_id']])): ?>
                                    <?php foreach ($episodes[$season['season_id']] as $episode): ?>
                                        <div class="episode-block" id="episode-<?php echo $episode['episode_id']; ?>">
                                            <input type="hidden" name="episode_id[<?php echo $season['season_id']; ?>][]" value="<?php echo $episode['episode_id']; ?>">
                                            <div class="flex justify-between items-center">
                                                <h4 class="text-md font-semibold">Edit Episode</h4>
                                                <button type="button" class="text-xs text-red-500 hover:underline" onclick="markForDeletion(this, 'episode')">Delete Episode</button>
                                            </div>
                                            <div class="space-y-3 mt-3">
                                                <input type="text" name="ep_title[<?php echo $season['season_id']; ?>][<?php echo $episode['episode_id']; ?>]" class="block w-full rounded-md p-2 text-sm" value="<?php echo htmlspecialchars($episode['title']); ?>" placeholder="Episode Title" required>
                                                <input type="number" name="ep_number[<?php echo $season['season_id']; ?>][<?php echo $episode['episode_id']; ?>]" class="block w-full rounded-md p-2 text-sm" value="<?php echo htmlspecialchars($episode['episode_number']); ?>" placeholder="Episode Number" required>
                                                <input type="url" name="ep_video_url[<?php echo $season['season_id']; ?>][<?php echo $episode['episode_id']; ?>]" class="block w-full rounded-md p-2 text-sm" value="<?php echo htmlspecialchars($episode['video_url']); ?>" placeholder="Video URL" required>
                                                <input type="url" name="ep_thumbnail_url[<?php echo $season['season_id']; ?>][<?php echo $episode['episode_id']; ?>]" class="block w-full rounded-md p-2 text-sm" value="<?php echo htmlspecialchars($episode['thumbnail_url']); ?>" placeholder="Thumbnail URL (optional)">
                                                <div class="grid grid-cols-2 gap-4">
                                                    <input type="number" name="ep_duration_minutes[<?php echo $season['season_id']; ?>][<?php echo $episode['episode_id']; ?>]" class="block w-full rounded-md p-2 text-sm" value="<?php echo htmlspecialchars($episode['duration_minutes']); ?>" placeholder="Duration (min)" required>
                                                    <select name="ep_language[<?php echo $season['season_id']; ?>][<?php echo $episode['episode_id']; ?>]" class="block w-full rounded-md p-2 text-sm">
                                                        <option <?php echo $episode['language'] == 'English' ? 'selected' : ''; ?>>English</option>
                                                        <option <?php echo $episode['language'] == 'Kannada' ? 'selected' : ''; ?>>Kannada</option>
                                                        <option <?php echo $episode['language'] == 'Telugu' ? 'selected' : ''; ?>>Telugu</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <!-- Add New Episode Button -->
                            <button type="button" class="mt-4 text-sm text-blue-400 hover:text-blue-300 font-medium" onclick="addEpisode(this, <?php echo $season['season_id']; ?>)">+ Add New Episode</button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Add New Season Button -->
                <button type="button" class="mt-6 text-md text-green-500 hover:text-green-400 font-semibold" onclick="addSeason()">+ Add New Season</button>
                
                <!-- Save Button -->
                <button type="submit" class="mt-8 w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-4 rounded-lg transition-colors duration-200 text-lg">
                    Save All Changes
                </button>
            </form>
        <?php endif; ?>
    </main>

    <!-- Templates for new seasons/episodes -->
    <template id="new-season-template">
        <div class="season-block" id="season-new_@COUNT@">
            <!-- Note: season_id[] is 'new_@COUNT@' to be handled by backend -->
            <input type="hidden" name="season_id[]" value="new_@COUNT@">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold">New Season</h3>
                <button type="button" class="text-xs text-red-500 hover:underline" onclick="this.closest('.season-block').remove()">Remove Season</button>
            </div>
            <div class="grid grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300">Season Title</label>
                    <input type="text" name="season_title[new_@COUNT@]" class="mt-1 block w-full rounded-md p-2" placeholder="e.g., Season 2" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300">Season Number</label>
                    <input type="number" name="season_number[new_@COUNT@]" class="mt-1 block w-full rounded-md p-2" placeholder="e.g., 2" required>
                </div>
            </div>
            <div class="episodes-container mt-4">
                <!-- New episodes will be added here -->
            </div>
            <button type="button" class="mt-4 text-sm text-blue-400 hover:text-blue-300 font-medium" onclick="addEpisode(this, 'new_@COUNT@')">+ Add New Episode</button>
        </div>
    </template>

    <template id="new-episode-template">
        <div class="episode-block" id="episode-new_@EP_COUNT@">
            <!-- Note: episode_id[season_id][] is 'new_@EP_COUNT@' -->
            <input type="hidden" name="episode_id[@SEASON_ID@][]" value="new_@EP_COUNT@">
            <div class="flex justify-between items-center">
                <h4 class="text-md font-semibold">New Episode</h4>
                <button type="button" class="text-xs text-red-500 hover:underline" onclick="this.closest('.episode-block').remove()">Remove Episode</button>
            </div>
            <div class="space-y-3 mt-3">
                <input type="text" name="ep_title[@SEASON_ID@][new_@EP_COUNT@]" class="block w-full rounded-md p-2 text-sm" placeholder="Episode Title" required>
                <input type="number" name="ep_number[@SEASON_ID@][new_@EP_COUNT@]" class="block w-full rounded-md p-2 text-sm" placeholder="Episode Number" required>
                <input type="url" name="ep_video_url[@SEASON_ID@][new_@EP_COUNT@]" classS="block w-full rounded-md p-2 text-sm" placeholder="Video URL" required>
                <input type="url" name="ep_thumbnail_url[@SEASON_ID@][new_@EP_COUNT@]" class="block w-full rounded-md p-2 text-sm" placeholder="Thumbnail URL (optional)">
                <div class="grid grid-cols-2 gap-4">
                    <input type="number" name="ep_duration_minutes[@SEASON_ID@][new_@EP_COUNT@]" class="block w-full rounded-md p-2 text-sm" placeholder="Duration (min)" required>
                    <select name="ep_language[@SEASON_ID@][new_@EP_COUNT@]" class="block w-full rounded-md p-2 text-sm">
                        <option>English</option>
                        <option>Kannada</option>
                        <option>Telugu</option>
                    </select>
                </div>
            </div>
        </div>
    </template>
    
    <script>
        let seasonCounter = 0;
        let episodeCounter = 0;

        function addSeason() {
            seasonCounter++;
            const template = document.getElementById('new-season-template').innerHTML;
            const newSeasonHtml = template.replace(/@COUNT@/g, seasonCounter);
            document.getElementById('seasons-container').insertAdjacentHTML('beforeend', newSeasonHtml);
        }

        function addEpisode(button, seasonId) {
            episodeCounter++;
            const template = document.getElementById('new-episode-template').innerHTML;
            const newEpisodeHtml = template.replace(/@SEASON_ID@/g, seasonId).replace(/@EP_COUNT@/g, episodeCounter);
            button.previousElementSibling.insertAdjacentHTML('beforeend', newEpisodeHtml);
        }

        function markForDeletion(button, type) {
            if (!confirm(`Are you sure you want to delete this ${type}? This will be permanent when you save.`)) {
                return;
            }
            const block = button.closest(type === 'season' ? '.season-block' : '.episode-block');
            block.style.display = 'none'; // Hide it
            
            // Add a hidden input to tell the backend to delete this item
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `delete_${type}[]`;
            input.value = block.id.split('-')[1]; // e.g., '123' or 'new_1'
            block.appendChild(input);
        }
    </script>
</body>
</html>