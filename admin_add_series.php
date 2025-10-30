<?php
// This must be at the very top, before any HTML.
// This file includes the new db_connect.php, which sets the error mode.
try {
    require 'db_connect.php';
} catch (Exception $e) {
    die("Failed to connect to database: " . $e->getMessage());
}

// Security Check: Make sure user is an admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: login.php?error=Access denied. Admins only.');
    exit;
}

$error = '';
$success = '';

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- FORM SUBMITTED, START PROCESSING LOGIC ---
    
    // Series Details
    $title = trim($_POST['series_title'] ?? '');
    $description = trim($_POST['series_description'] ?? '');
    $poster_url = trim($_POST['series_poster_url'] ?? '');
    $genre = trim($_POST['series_genre'] ?? '');
    $episode_type = $_POST['episode_type'] ?? 'episodic';

    // --- 1. VALIDATION ---
    // This is the strict validation that runs *before* the database query.
    
    try {
        if (empty($title) || empty($description) || empty($poster_url) || empty($genre)) {
            throw new Exception("Series Title, Description, Poster, and Genre are all required.");
        }

        if (!filter_var($poster_url, FILTER_VALIDATE_URL)) {
            throw new Exception("Invalid Poster URL format.");
        }

        if ($episode_type === 'merged') {
            // --- Merged File Validation ---
            $merged_season = filter_var($_POST['merged_season'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
            $merged_title = trim($_POST['merged_title'] ?? '');
            $merged_video_url = trim($_POST['merged_video_url'] ?? '');
            $merged_language = trim($_POST['merged_language'] ?? '');
            $merged_duration_min = filter_var($_POST['merged_duration'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

            if ($merged_season === false || $merged_season === null) throw new Exception("Merged Season Number must be a valid number (0 or more).");
            if (empty($merged_title)) throw new Exception("Merged Title is required.");
            if (empty($merged_video_url) || !filter_var($merged_video_url, FILTER_VALIDATE_URL)) throw new Exception("Merged Video URL is required and must be a valid URL.");
            if (empty($merged_language)) throw new Exception("Merged Language is required.");
            if ($merged_duration_min === false || $merged_duration_min === null) throw new Exception("Merged Duration must be a valid number (1 or more).");
        
        } else {
            // --- Episodic File Validation ---
            $season_number = filter_var($_POST['season_number'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
            $ep_titles = $_POST['ep_title'] ?? [];
            $ep_numbers = $_POST['ep_number'] ?? [];
            $ep_video_urls = $_POST['ep_video_url'] ?? [];
            $ep_languages = $_POST['ep_language'] ?? [];
            $ep_durations_min = $_POST['ep_duration'] ?? [];

            if ($season_number === false || $season_number === null) throw new Exception("Season Number must be a valid number (0 or more).");
            if (empty($ep_titles)) throw new Exception("At least one episode is required.");
            
            foreach ($ep_titles as $index => $ep_title) {
                $ep_num = $ep_numbers[$index] ?? null;
                $ep_url = $ep_video_urls[$index] ?? null;
                $ep_lang = $ep_languages[$index] ?? null;
                $ep_dur_min = $ep_durations_min[$index] ?? null;

                $display_num = $index + 1;

                if (empty(trim($ep_title))) throw new Exception("Episode {$display_num} is missing a Title.");
                
                $ep_num_validated = filter_var($ep_num, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
                if ($ep_num_validated === false || $ep_num_validated === null) throw new Exception("Episode {$display_num} has an invalid Episode Number.");

                if (empty(trim($ep_url)) || !filter_var(trim($ep_url), FILTER_VALIDATE_URL)) throw new Exception("Episode {$display_num} has an invalid Video URL.");
                
                if (empty(trim($ep_lang))) throw new Exception("Episode {$display_num} is missing a Language.");
                
                $ep_dur_validated = filter_var($ep_dur_min, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                if ($ep_dur_validated === false || $ep_dur_validated === null) throw new Exception("Episode {$display_num} has an invalid Duration (must be 1 or more).");
            }
        }

        // --- 2. DATABASE TRANSACTION ---
        // Validation passed! Now we can start the transaction.
        
        $pdo->beginTransaction();

        // 1. Insert into 'movies' table
        $sql_movie = "INSERT INTO movies (title, description, poster_url, genre, is_series, type) 
                      VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_movie = $pdo->prepare($sql_movie);
        
        // This query now includes 'type' and 'is_series'
        $stmt_movie->execute([$title, $description, $poster_url, $genre, true, 'series']);
        
        // Get the new movie_id
        $movie_id = $pdo->lastInsertId();

        if ($episode_type === 'merged') {
            // --- Save Merged File ---
            $merged_duration_sec = $merged_duration_min * 60;

            // 2. Insert into 'seasons' table
            $sql_season = "INSERT INTO seasons (movie_id, season_number, title) VALUES (?, ?, ?)";
            $stmt_season = $pdo->prepare($sql_season);
            $stmt_season->execute([$movie_id, $merged_season, $merged_title]);
            $season_id = $pdo->lastInsertId();

            // 3. Insert into 'episodes' table
            $sql_episode = "INSERT INTO episodes (season_id, title, episode_number, video_url, language, duration) 
                            VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_episode = $pdo->prepare($sql_episode);
            $stmt_episode->execute([$season_id, $merged_title, 1, $merged_video_url, $merged_language, $merged_duration_sec]);

        } else {
            // --- Save Episodic Files ---

            // 2. Insert into 'seasons' table
            $sql_season = "INSERT INTO seasons (movie_id, season_number, title) VALUES (?, ?, ?)";
            $stmt_season = $pdo->prepare($sql_season);
            $season_title = "Season " . $season_number; // Auto-generate title
            $stmt_season->execute([$movie_id, $season_number, $season_title]);
            $season_id = $pdo->lastInsertId();
            
            // 3. Insert all episodes
            $sql_episode = "INSERT INTO episodes (season_id, title, episode_number, video_url, language, duration) 
                            VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_episode = $pdo->prepare($sql_episode);

            foreach ($ep_titles as $index => $ep_title) {
                $duration_sec = (int)$ep_durations_min[$index] * 60;
                
                $stmt_episode->execute([
                    $season_id,
                    trim($ep_title),
                    (int)$ep_numbers[$index],
                    trim($ep_video_urls[$index]),
                    trim($ep_languages[$index]),
                    $duration_sec
                ]);
            }
        }

        // If we got here, everything worked. Commit the transaction.
        $pdo->commit();
        
        $success = "Successfully added new series: " . htmlspecialchars($title);
        // We will *not* redirect. The page will reload and show the success message.
        // This avoids any caching issues.
        // To add another, the user can just use the (now empty) form again.

    } catch (PDOException | Exception $e) {
        // --- 3. ERROR HANDLING ---
        // An error happened! Roll back the transaction
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        // This is the *real* error message you've been looking for.
        $error = "Failed to add series: " . $e->getMessage();
    }
    
    // --- END OF PROCESSING LOGIC ---
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Series - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-900 text-white">

    <div class="container mx-auto max-w-3xl p-4 py-8">

        <header class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-red-500">Add New Series</h1>
            <div>
                <a href="admin.php" class="text-blue-400 hover:text-blue-300">&larr; Back to Admin Panel</a>
            </div>
        </header>

        <!-- THIS IS THE ERROR/SUCCESS BANNER -->
        <?php if ($error): ?>
            <div class="bg-red-500 text-white p-3 rounded-md mb-6 text-center">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="bg-green-500 text-white p-3 rounded-md mb-6 text-center">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <!-- Form: action is this page itself. -->
        <form action="admin_add_series.php" method="POST" id="series-form" class="bg-gray-800 p-6 sm:p-8 rounded-lg shadow-lg space-y-6">
            
            <!-- Series Title -->
            <div>
                <label for="series_title" class="block text-sm font-medium text-gray-300">Series Title</label>
                <input type="text" id="series_title" name="series_title" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white" required>
            </div>

            <!-- Series Description -->
            <div>
                <label for="series_description" class="block text-sm font-medium text-gray-300">Description</label>
                <textarea id="series_description" name="series_description" rows="3" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white" required></textarea>
            </div>

            <!-- Series Poster URL -->
            <div>
                <label for="series_poster_url" class="block text-sm font-medium text-gray-300">Poster Image URL</label>
                <input type="url" id="series_poster_url" name="series_poster_url" placeholder="https://..." class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white" required>
            </div>

            <!-- Series Genre -->
            <div>
                <label for="series_genre" class="block text-sm font-medium text-gray-300">Genre</label>
                <input type="text" id="series_genre" name="series_genre" placeholder="Action, Comedy, Drama" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white" required>
            </div>

            <!-- Episode Type Toggle -->
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Episode Type</label>
                <div class="flex items-center space-x-4">
                    <label class="flex items-center">
                        <input type="radio" name="episode_type" value="episodic" class="form-radio text-red-500 bg-gray-700" checked>
                        <span class="ml-2 text-white">Episodic</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="episode_type" value="merged" class="form-radio text-red-500 bg-gray-700">
                        <span class="ml-2 text-white">Merged Season File</span>
                    </label>
                </div>
            </div>

            <!-- Container for Merged File Fields -->
            <div id="merged-fields" class="hidden space-y-4 p-4 bg-gray-900 rounded-lg border border-gray-700">
                <h3 class="text-xl font-semibold">Merged Season Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="merged_season" class="block text-sm font-medium text-gray-300">Season Number</label>
                        <input type="number" id="merged_season" name="merged_season" value="1" min="0" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white">
                    </div>
                    <div class="col-span-2">
                        <label for="merged_title" class="block text-sm font-medium text-gray-300">Title (e.g., "Season 1")</label>
                        <input type="text" id="merged_title" name="merged_title" value="Season 1" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white">
                    </div>
                    <div class="col-span-2">
                        <label for="merged_video_url" class="block text-sm font-medium text-gray-300">Video URL</label>
                        <input type="url" id="merged_video_url" name="merged_video_url" placeholder="https://storage.com/..." class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white">
                    </div>
                    <div>
                        <label for="merged_language" class="block text-sm font-medium text-gray-300">Language</label>
                        <select id="merged_language" name="merged_language" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white">
                            <option value="">Select Language</option>
                            <option value="English">English</option>
                            <option value="Kannada">Kannada</option>
                            <option value="Telugu">Telugu</option>
                            <option value="Hindi">Hindi</option>
                            <option value="Multi-language">Multi-language</option>
                        </select>
                    </div>
                    <div>
                        <label for="merged_duration" class="block text-sm font-medium text-gray-300">Total Duration (minutes)</label>
                        <input type="number" id="merged_duration" name="merged_duration" placeholder="120" min="1" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white">
                    </div>
                </div>
            </div>

            <!-- Container for Episodic Fields -->
            <div id="episodic-fields" class="space-y-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-semibold">Episodes</h3>
                    <button type="button" id="add-episode-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md transition duration-300">
                        Add Another Episode
                    </button>
                </div>
                
                <!-- Season Number (for episodic) -->
                <div class="mb-4">
                    <label for="season_number" class="block text-sm font-medium text-gray-300">Season Number</label>
                    <input type="number" id="season_number" name="season_number" value="1" min="0" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white">
                </div>

                <!-- Episode container -->
                <div id="episodes-container" class="space-y-4">
                    <!-- Episode 1 (Mandatory) -->
                    <div class="p-4 bg-gray-900 rounded-lg border border-gray-700 episode-entry" data-episode-num="1">
                        <h4 class="text-lg font-semibold text-white mb-3">Episode <span class="episode-number">1</span></h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="ep_title_1" class="block text-sm font-medium text-gray-300">Episode Title</label>
                                <input type="text" id="ep_title_1" name="ep_title[]" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white">
                            </div>
                            <div>
                                <label for="ep_number_1" class="block text-sm font-medium text-gray-300">Episode Number</label>
                                <input type="number" id="ep_number_1" name="ep_number[]" value="1" min="0" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white">
                            </div>
                            <div class="col-span-2">
                                <label for="ep_video_url_1" class="block text-sm font-medium text-gray-300">Video URL</label>
                                <input type="url" id="ep_video_url_1" name="ep_video_url[]" placeholder="https://storage.com/..." class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white">
                            </div>
                            <div>
                                <label for="ep_language_1" class="block text-sm font-medium text-gray-300">Language</label>
                                <select id="ep_language_1" name="ep_language[]" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white ep-language-select">
                                    <option value="">Select Language</option>
                                    <option value="English">English</option>
                                    <option value="Kannada">Kannada</option>
                                    <option value="Telugu">Telugu</option>
                                    <option value="Hindi">Hindi</option>
                                    <option value="Multi-language">Multi-language</option>
                                </select>
                            </div>
                            <div>
                                <label for="ep_duration_1" class="block text-sm font-medium text-gray-300">Duration (minutes)</label>
                                <input type="number" id="ep_duration_1" name="ep_duration[]" placeholder="45" min="1" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-md transition duration-300">
                Add Series
            </button>
        </form>

    </div>

    <!-- Template for new episodes (for JavaScript) -->
    <template id="episode-template">
        <div class="p-4 bg-gray-900 rounded-lg border border-gray-700 episode-entry" data-episode-num="1">
            <div class="flex justify-between items-center mb-3">
                <h4 class="text-lg font-semibold text-white">Episode <span class="episode-number">1</span></h4>
                <button type="button" class="remove-episode-btn text-red-400 hover:text-red-300 font-medium">Remove</button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="ep_title_X" class="block text-sm font-medium text-gray-300">Episode Title</label>
                    <input type="text" id="ep_title_X" name="ep_title[]" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white ep-title-input">
                </div>
                <div>
                    <label for="ep_number_X" class="block text-sm font-medium text-gray-300">Episode Number</label>
                    <input type="number" id="ep_number_X" name="ep_number[]" value="1" min="0" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white ep-number-input">
                </div>
                <div class="col-span-2">
                    <label for="ep_video_url_X" class="block text-sm font-medium text-gray-300">Video URL</label>
                    <input type="url" id="ep_video_url_X" name="ep_video_url[]" placeholder="https://storage.com/..." class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white">
                </div>
                <div>
                    <label for="ep_language_X" class="block text-sm font-medium text-gray-300">Language</label>
                    <select id="ep_language_X" name="ep_language[]" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white ep-language-select">
                        <option value="">Select Language</option>
                        <option value="English">English</option>
                        <option value="Kannada">Kannada</option>
                        <option value="Telugu">Telugu</option>
                        <option value="Hindi">Hindi</option>
                        <option value="Multi-language">Multi-language</option>
                    </select>
                </div>
                <div>
                    <label for="ep_duration_X" class="block text-sm font-medium text-gray-300">Duration (minutes)</label>
                    <input type="number" id="ep_duration_X" name="ep_duration[]" placeholder="45" min="1" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white">
                </div>
            </div>
        </div>
    </template>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // This JavaScript is correct and will work.
            // Remember to Hard Refresh (Ctrl+F5) to clear your cache.
            const seriesForm = document.getElementById('series-form');
            const episodeTypeRadios = document.querySelectorAll('input[name="episode_type"]');
            const episodicFields = document.getElementById('episodic-fields');
            const mergedFields = document.getElementById('merged-fields');
            const addEpisodeBtn = document.getElementById('add-episode-btn');
            const episodesContainer = document.getElementById('episodes-container');
            const episodeTemplate = document.getElementById('episode-template');

            function toggleEpisodeFields() {
                const isMerged = document.querySelector('input[name="episode_type"]:checked').value === 'merged';
                
                if (isMerged) {
                    episodicFields.classList.add('hidden');
                    mergedFields.classList.remove('hidden');
                } else {
                    episodicFields.classList.remove('hidden');
                    mergedFields.classList.add('hidden');
                }
            }

            toggleEpisodeFields(); // Run on page load
            episodeTypeRadios.forEach(radio => radio.addEventListener('change', toggleEpisodeFields));

            addEpisodeBtn.addEventListener('click', function () {
                const newEpisodeNum = episodesContainer.children.length + 1;
                const newEpisode = episodeTemplate.content.cloneNode(true);
                const newEntry = newEpisode.querySelector('.episode-entry');
                
                newEntry.dataset.episodeNum = newEpisodeNum;
                newEntry.querySelector('.episode-number').textContent = newEpisodeNum;

                newEntry.querySelectorAll('label').forEach(label => {
                    const oldFor = label.getAttribute('for');
                    if (oldFor) label.setAttribute('for', oldFor.replace('_X', `_${newEpisodeNum}`));
                });

                newEntry.querySelectorAll('input, select').forEach(input => {
                    if (input.id) input.id = input.id.replace('_X', `_${newEpisodeNum}`);
                    if (input.classList.contains('ep-number-input')) {
                        input.value = newEpisodeNum;
                    }
                });

                newEntry.querySelector('.remove-episode-btn').addEventListener('click', function (e) {
                    e.target.closest('.episode-entry').remove();
                    updateEpisodeNumbers();
                });

                episodesContainer.appendChild(newEpisode);
            });

            function updateEpisodeNumbers() {
                const allEpisodes = episodesContainer.querySelectorAll('.episode-entry');
                allEpisodes.forEach((entry, index) => {
                    const num = index + 1;
                    entry.dataset.episodeNum = num;
                    entry.querySelector('.episode-number').textContent = num;
                    
                    if(entry.querySelector('.ep-number-input')) {
                        entry.querySelector('.ep-number-input').value = num;
                    }

                    entry.querySelectorAll('label').forEach(label => {
                        const oldFor = label.getAttribute('for');
                        if (oldFor) label.setAttribute('for', oldFor.replace(/_\d+$/, `_${num}`));
                    });
                    entry.querySelectorAll('input, select').forEach(input => {
                        if (input.id) input.id = input.id.replace(/_\d+$/, `_${num}`);
                    });
                });
            }
        });
    </script>
</body>
</html>