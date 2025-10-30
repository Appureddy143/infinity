<?php
// This must be at the very top, before any HTML.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'db_connect.php';

// Security Check: Make sure user is an admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: login.php?error=Access denied. Admins only.');
    exit;
}

// --- NEW SINGLE-FILE LOGIC ---
$error = null;
$success = null;

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // We wrap the *entire* processing logic in a try...catch block
    try {
        // 1. Check top-level series details
        $series_title = trim($_POST['series_title'] ?? '');
        $series_description = trim($_POST['series_description'] ?? '');
        $series_poster_url = trim($_POST['series_poster_url'] ?? '');
        $series_genre = trim($_POST['series_genre'] ?? '');
        $episode_type = $_POST['episode_type'] ?? '';

        if (empty($series_title)) throw new Exception("Series Title is required.");
        if (empty($series_description)) throw new Exception("Series Description is required.");
        if (empty($series_poster_url)) throw new Exception("Series Poster URL is required.");
        if (empty($series_genre)) throw new Exception("Series Genre is required.");
        if (empty($episode_type)) throw new Exception("Episode Type is required.");

        $type = 'series'; // lowercase for the database check constraint

        // Start database transaction
        $pdo->beginTransaction();

        // 2. Insert the main series data into 'movies' table
        $sql = "INSERT INTO movies (title, description, poster_url, genre, type, is_series, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        if (!$stmt) throw new Exception("Failed to prepare the movie insertion query.");
        
        $stmt->execute([
            $series_title,
            $series_description,
            $series_poster_url,
            $series_genre,
            $type,
            true
        ]);

        $movieId = $pdo->lastInsertId();
        if (!$movieId) throw new Exception("Failed to get new movie ID after insertion.");

        // 3. Handle different episode types
        if ($episode_type === 'merged') {
            // --- Validation for Merged File ---
            $season_number = filter_var($_POST['merged_season'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
            $title = trim($_POST['merged_title'] ?? '');
            $video_url = trim($_POST['merged_video_url'] ?? '');
            $language = trim($_POST['merged_language'] ?? '');
            $duration = filter_var($_POST['merged_duration'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

            if ($season_number === false) throw new Exception("Invalid Merged Season Number (must be 0 or more).");
            if (empty($title)) throw new Exception("Merged Season Title is required.");
            if (empty($video_url)) throw new Exception("Merged Video URL is required.");
            if (empty($language)) throw new Exception("Merged Language is required.");
            if ($duration === false) throw new Exception("Invalid Merged Duration (must be 1 or more).");

            // Insert the single season
            $sql_season = "INSERT INTO seasons (movie_id, season_number, title) VALUES (?, ?, ?)";
            $stmt_season = $pdo->prepare($sql_season);
            if (!$stmt_season) throw new Exception("Failed to prepare season query.");
            $stmt_season->execute([$movieId, $season_number, $title]);
            $seasonId = $pdo->lastInsertId();
            if (!$seasonId) throw new Exception("Failed to get new season ID.");

            // Insert the single "episode"
            $sql_ep = "INSERT INTO episodes (season_id, episode_number, title, video_url, language, duration_seconds) 
                       VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_ep = $pdo->prepare($sql_ep);
            if (!$stmt_ep) throw new Exception("Failed to prepare merged episode query.");
            $stmt_ep->execute([$seasonId, 1, $title, $video_url, $language, $duration * 60]);

        } elseif ($episode_type === 'episodic') {
            // --- Validation for Episodic Files ---
            $season_number = filter_var($_POST['season_number'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
            if ($season_number === false) throw new Exception("Invalid Season Number (must be 0 or more).");
            
            // Insert the season
            $sql_season = "INSERT INTO seasons (movie_id, season_number, title) VALUES (?, ?, ?)";
            $stmt_season = $pdo->prepare($sql_season);
            if (!$stmt_season) throw new Exception("Failed to prepare season query.");
            $stmt_season->execute([$movieId, $season_number, "Season " . $season_number]);
            $seasonId = $pdo->lastInsertId();
            if (!$seasonId) throw new Exception("Failed to get new season ID.");

            // Check for episode arrays
            if (!isset($_POST['ep_title']) || !is_array($_POST['ep_title'])) {
                throw new Exception("No episode data was submitted.");
            }

            $ep_titles = $_POST['ep_title'];
            $ep_numbers = $_POST['ep_number'];
            $ep_video_urls = $_POST['ep_video_url'];
            $ep_languages = $_POST['ep_language'];
            $ep_durations = $_POST['ep_duration'];

            $sql_ep = "INSERT INTO episodes (season_id, episode_number, title, video_url, language, duration_seconds) 
                       VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_ep = $pdo->prepare($sql_ep);
            if (!$stmt_ep) throw new Exception("Failed to prepare episodic episodes query.");

            // Loop and validate *each episode*
            for ($i = 0; $i < count($ep_titles); $i++) {
                $title = trim($ep_titles[$i] ?? '');
                $number = filter_var($ep_numbers[$i] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                $video_url = trim($ep_video_urls[$i] ?? '');
                $language = trim($ep_languages[$i] ?? '');
                $duration = filter_var($ep_durations[$i] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

                // Stricter checks
                if (empty($title)) throw new Exception("Episode " . ($i + 1) . " is missing a title.");
                if ($number === false) throw new Exception("Episode " . ($i + 1) . " has an invalid number (must be 1 or more).");
                if (empty($video_url)) throw new Exception("Episode " . ($i + 1) . " is missing a video URL.");
                if (empty($language)) throw new Exception("Episode " . ($i + 1) . " is missing a language.");
                if ($duration === false) throw new Exception("Episode " . ($i + 1) . " has an invalid duration (must be 1 or more).");

                // Execute the insertion for this episode
                $stmt_ep->execute([$seasonId, $number, $title, $video_url, $language, $duration * 60]);
            }
        } else {
            throw new Exception("Invalid episode type submitted.");
        }

        // If all checks passed, commit the transaction
        $pdo->commit();
        
        // Success! Redirect to the main admin page.
        header('Location: admin.php?success=Series added successfully!');
        exit;

    } catch (Exception $e) {
        // Catch *any* exception (PDO or our custom ones)
        // Roll back if a transaction was started
        if ($pdo && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        // --- THIS IS THE FIX ---
        // Instead of redirecting, just set the $error variable.
        // The page will reload and display this error.
        $error = $e->getMessage();
    }
}
// --- END OF SINGLE-FILE LOGIC ---
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Add Series</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-900 text-white">

    <div class="container mx-auto max-w-4xl p-4">

        <header class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-red-500">Add New Series</h1>
            <div>
                <a href="admin.php" class="text-blue-400 hover:text-blue-300 mr-4">&larr; Back to Admin Panel</a>
                <a href="logout.php" class="text-red-500 hover:text-red-400">Logout</a>
            </div>
        </header>

        <!-- Error/Success Banners -->
        <!-- This will now display the $error variable from the PHP logic above -->
        <?php if ($success): ?>
            <div class="bg-green-500 text-white p-3 rounded-md mb-6 text-center">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="bg-red-500 text-white p-3 rounded-md mb-6 text-center">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <!-- End Banners -->


        <!-- Section 2: Add New Series -->
        <section class="bg-gray-800 p-6 rounded-lg shadow-lg">
            
            <!-- The form now submits to *itself* (this same page) -->
            <form action="admin_add_series.php" method="POST" id="series-form">
                <!-- Series Title -->
                <div class="mb-4">
                    <label for="series_title" class="block text-sm font-medium text-gray-300">Series Title</label>
                    <input type="text" id="series_title" name="series_title" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white" required>
                </div>

                <!-- Series Description -->
                <div class="mb-4">
                    <label for="series_description" class="block text-sm font-medium text-gray-300">Description</label>
                    <textarea id="series_description" name="series_description" rows="3" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white" required></textarea>
                </div>

                <!-- Series Poster URL -->
                <div class="mb-4">
                    <label for="series_poster_url" class="block text-sm font-medium text-gray-300">Poster Image URL</label>
                    <input type="url" id="series_poster_url" name="series_poster_url" placeholder="https://..." class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white" required>
                </div>

                <!-- Series Genre -->
                <div class="mb-6">
                    <label for="series_genre" class="block text-sm font-medium text-gray-300">Genre</label>
                    <input type="text" id="series_genre" name="series_genre" placeholder="Action, Comedy, Drama" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white" required>
                </div>

                <!-- Episode Type Toggle -->
                <div class="mb-6">
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
                <div id="merged-fields" class="hidden space-y-4 mb-6 p-4 bg-gray-900 rounded-lg">
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
                            <label for="merged_video_url" class="block text-sm font-medium text-gray-300">Video URL</to-label>
                            <input type="url" id="merged_video_url" name="merged_video_url" placeholder="https://storage.com/..." class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white">
                        </div>
                        <div class="col-span-2">
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
                <div id="episodic-fields" class="space-y-4 mb-6">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-semibold">Episodes</h3>
                        <button type="button" id="add-episode-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md transition duration-300">
                            Add Another Episode
                        </button>
                    </div>
                    
                    <!-- Season 1 (default) -->
                    <div class="mb-4">
                        <label for="season_number_1" class="block text-sm font-medium text-gray-300">Season Number</label>
                        <input type="number" id="season_number_1" name="season_number" value="1" min="0" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white" required>
                    </div>

                    <!-- Episode container -->
                    <div id="episodes-container" class="space-y-4">
                        <!-- Episode 1 (Mandatory) -->
                        <div class="p-4 bg-gray-900 rounded-lg episode-entry" data-episode-num="1">
                            <h4 class="text-lg font-semibold text-white mb-3">Episode <span class="episode-number">1</span></h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="ep_title_1" class="block text-sm font-medium text-gray-300">Episode Title</label>
                                    <input type="text" id="ep_title_1" name="ep_title[]" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white" required>
                                </div>
                                <div>
                                    <label for="ep_number_1" class="block text-sm font-medium text-gray-300">Episode Number</LAbel>
                                    <input type="number" id="ep_number_1" name="ep_number[]" value="1" min="1" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white" required>
                                </div>
                                <div class="col-span-2">
                                    <label for="ep_video_url_1" class="block text-sm font-medium text-gray-300">Video URL</LAbel>
                                    <input type="url" id="ep_video_url_1" name="ep_video_url[]" placeholder="https://storage.com/..." class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white" required>
                                </div>
                                <div>
                                    <label for="ep_language_1" class="block text-sm font-medium text-gray-300">Language</LAbel>
                                    <select id="ep_language_1" name="ep_language[]" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white ep-language-select" required>
                                        <option value="">Select Language</option>
                                        <option value="English">English</option>
                                        <option value="Kannada">Kannada</option>
                                        <option value="Telugu">Telugu</option>
                                        <option value="Hindi">Hindi</option>
                                        <option value="Multi-language">Multi-language</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="ep_duration_1" class="block text-sm font-medium text-gray-300">Duration (minutes)</LAbel>
                                    <input type="number" id="ep_duration_1" name="ep_duration[]" placeholder="45" min="1" class="mt-1 block w-full bg-gray-70a-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white" required>
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
        </section>

    </div>

    <!-- Template for new episodes (for JavaScript) -->
    <template id="episode-template">
        <div class="p-4 bg-gray-900 rounded-lg episode-entry" data-episode-num="1">
            <div class="flex justify-between items-center mb-3">
                <h4 class="text-lg font-semibold text-white">Episode <span class="episode-number">1</span></h4>
                <button type="button" class="remove-episode-btn text-red-400 hover:text-red-300 font-medium">Remove</button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="ep_title_X" class="block text-sm font-medium text-gray-300">Episode Title</label>
                    <input type="text" id="ep_title_X" name="ep_title[]" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white ep-title-input" required>
                </div>
                <div>
                    <label for="ep_number_X" class="block text-sm font-medium text-gray-300">Episode Number</LAbel>
                    <input type="number" id="ep_number_X" name="ep_number[]" value="1" min="1" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white ep-number-input" required>
                </div>
                <div class="col-span-2">
                    <label for="ep_video_url_X" class="block text-sm font-medium text-gray-300">Video URL</LAbel>
                    <input type="url" id="ep_video_url_X" name="ep_video_url[]" placeholder="https://storage.com/..." class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white" required>
                </div>
                <div>
                    <label for="ep_language_X" class="block text-sm font-medium text-gray-300">Language</LAbel>
                    <select id="ep_language_X" name="ep_language[]" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white ep-language-select" required>
                         <option value="">Select Language</option>
                        <option value="English">English</option>
                        <option value="Kannada">Kannada</option>
                        <option value="Telugu">Telugu</option>
                        <option value="Hindi">Hindi</option>
                        <option value="Multi-language">Multi-language</option>
                    </select>
                </div>
                <div>
                    <label for="ep_duration_X" class="block text-sm font-medium text-gray-300">Duration (minutes)</LAbel>
                    <input type="number" id="ep_duration_X" name="ep_duration[]" placeholder="45" min="1" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white" required>
                </div>
            </div>
        </div>
    </template>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const seriesForm = document.getElementById('series-form');
            const episodeTypeRadios = document.querySelectorAll('input[name="episode_type"]');
            const episodicFields = document.getElementById('episodic-fields');
            const mergedFields = document.getElementById('merged-fields');
            const addEpisodeBtn = document.getElementById('add-episode-btn');
            const episodesContainer = document.getElementById('episodes-container');
            const episodeTemplate = document.getElementById('episode-template');

            // Function to toggle fields based on radio button
            function toggleEpisodeFields() {
                const isMerged = document.querySelector('input[name="episode_type"]:checked').value === 'merged';
                
                mergedFields.classList.toggle('hidden', !isMerged);
                episodicFields.classList.toggle('hidden', isMerged);
                
                // Toggle 'required' attribute for inputs
                // When merged is selected, its fields are required, and episodic fields are not.
                mergedFields.querySelectorAll('input, select').forEach(el => {
                    // Don't require season number, it has a default
                    if (el.name === 'merged_season') {
                        el.required = false;
                    } else {
                        el.required = isMerged;
                    }
                });

                // When episodic is selected, its fields are required, and merged fields are not.
                episodicFields.querySelectorAll('input, select').forEach(el => el.required = !isMerged);
            }

            // Initial check
            toggleEpisodeFields();

            // Add change listener to radio buttons
            episodeTypeRadios.forEach(radio => radio.addEventListener('change', toggleEpisodeFields));

            // Function to add a new episode
            addEpisodeBtn.addEventListener('click', function () {
                const newEpisodeNum = episodesContainer.children.length + 1;
                const newEpisode = episodeTemplate.content.cloneNode(true);
                const newEntry = newEpisode.querySelector('.episode-entry');
                
                newEntry.dataset.episodeNum = newEpisodeNum;
                newEntry.querySelector('.episode-number').textContent = newEpisodeNum;

                // Update IDs and 'for' attributes to be unique
                newEntry.querySelectorAll('label').forEach(label => {
                    const oldFor = label.getAttribute('for');
                    if (oldFor) {
                        const newFor = oldFor.replace('_X', `_${newEpisodeNum}`);
                        label.setAttribute('for', newFor);
                    }
                });

                newEntry.querySelectorAll('input, select').forEach(input => {
                    const oldId = input.id;
                    if (oldId) {
                        const newId = oldId.replace('_X', `_${newEpisodeNum}`);
                        input.id = newId;
                    }
                    if (input.classList.contains('ep-number-input')) {
                        input.value = newEpisodeNum;
                    }
                    // Only set required if we are in episodic mode
                    if(document.querySelector('input[name="episode_type"]:checked').value === 'episodic') {
                        input.required = true;
                    }
                });

                // Add remove functionality
                newEntry.querySelector('.remove-episode-btn').addEventListener('click', function (e) {
                    e.target.closest('.episode-entry').remove();
                    updateEpisodeNumbers();
                });

                episodesContainer.appendChild(newEpisode);
            });

            // Function to re-number episodes after one is removed
            function updateEpisodeNumbers() {
                const allEpisodes = episodesContainer.querySelectorAll('.episode-entry');
                allEpisodes.forEach((entry, index) => {
                    const num = index + 1;
                    entry.dataset.episodeNum = num;
                    entry.querySelector('.episode-number').textContent = num;
                    
                    // Update IDs and 'for' attributes
                    entry.querySelectorAll('label').forEach(label => {
                        const oldFor = label.getAttribute('for');
                        if (oldFor) {
                            const newFor = oldFor.replace(/_\d+$/, `_${num}`);
                            label.setAttribute('for', newFor);
                        }
                    });
                    entry.querySelectorAll('input, select').forEach(input => {
                        const oldId = input.id;
                        if (oldId) {
                            const newId = oldId.replace(/_\d+$/, `_${num}`);
                            input.id = newId;
                        }
                    });
                });
            }
        });
    </script>
</body>
</html>
