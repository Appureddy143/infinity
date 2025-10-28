<?php
// This must be at the very top, before any HTML.
require 'db_connect.php';

// Security Check: Make sure user is an admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: login.php?error=Access denied. Admins only.');
    exit;
}

// Fetch existing content for the "Manage Content" section
try {
    // We join with users to show who added what, just as an example
    $stmt = $pdo->query("
        SELECT m.*, u.email 
        FROM movies m 
        LEFT JOIN users u ON m.added_by_user_id = u.user_id 
        ORDER BY m.created_at DESC
    ");
    $content = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $content = []; // Start with an empty array on error
    $admin_error = "Error fetching content: " . $e->getMessage();
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
        body { font-family: 'Inter', sans-serif; }
        /* Simple toggle switch CSS */
        .toggle-checkbox:checked {
            right: 0;
            border-color: #EF4444;
        }
        .toggle-checkbox:checked + .toggle-label {
            background-color: #EF4444;
        }
    </style>
</head>
<body class="bg-gray-900 text-white">

    <div class="container mx-auto max-w-6xl p-4">

        <header class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-red-500">Admin Panel</h1>
            <div>
                <span class="text-gray-400 mr-4">Welcome, <?= htmlspecialchars($currentUser['email'] ?? 'Admin') ?>!</span>
                <a href="index.php" class="text-blue-400 hover:text-blue-300 mr-4">&larr; Back to Site</a>
                <a href="logout.php" class="text-red-500 hover:text-red-400">Logout</a>
            </div>
        </header>

        <?php if (isset($_GET['success'])): ?>
            <div class="bg-green-500 text-white p-3 rounded-md mb-6 text-center">
                <?= htmlspecialchars($_GET['success']) ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-500 text-white p-3 rounded-md mb-6 text-center">
                <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>

        <!-- Grid for Admin Forms -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

            <!-- Section 1: Add New Movie -->
            <section class="bg-gray-800 p-6 rounded-lg shadow-lg">
                <h2 class="text-2xl font-semibold mb-6">Add New Movie</h2>
                <form action="admin_add_movie.php" method="POST">
                    <!-- Movie Title -->
                    <div class="mb-4">
                        <label for="movie_title" class="block text-sm font-medium text-gray-300">Movie Title</label>
                        <input type="text" id="movie_title" name="movie_title" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white" required>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label for="movie_description" class="block text-sm font-medium text-gray-300">Description</label>
                        <textarea id="movie_description" name="movie_description" rows="3" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white" required></textarea>
                    </div>

                    <!-- Poster Image URL -->
                    <div class="mb-4">
                        <label for="movie_poster_url" class="block text-sm font-medium text-gray-300">Poster Image URL</label>
                        <input type="url" id="movie_poster_url" name="movie_poster_url" placeholder="https://..." class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white" required>
                    </div>

                    <!-- Video URL -->
                    <div class="mb-4">
                        <label for="movie_video_url" class="block text-sm font-medium text-gray-300">Video URL</label>
                        <input type="url" id="movie_video_url" name="movie_video_url" placeholder="https://storage.com/..." class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white" required>
                    </div>

                    <!-- Genre -->
                    <div class="mb-4">
                        <label for="movie_genre" class="block text-sm font-medium text-gray-300">Genre</label>
                        <input type="text" id="movie_genre" name="movie_genre" placeholder="Action, Comedy, Drama" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white" required>
                    </div>

                    <!-- Language -->
                    <div class="mb-4">
                        <label for="movie_language" class="block text-sm font-medium text-gray-300">Language</label>
                        <select id="movie_language" name="movie_language" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white" required>
                            <option value="English">English</option>
                            <option value="Kannada">Kannada</option>
                            <option value="Telugu">Telugu</option>
                            <option value="Hindi">Hindi</option>
                            <option value="Multi-language">Multi-language</option>
                        </select>
                    </div>
                    
                    <!-- Duration -->
                    <div class="mb-6">
                        <label for="movie_duration" class="block text-sm font-medium text-gray-300">Duration (in minutes)</label>
                        <input type="number" id="movie_duration" name="movie_duration" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white" required>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-md transition duration-300">
                        Add Movie
                    </button>
                </form>
            </section>

            <!-- Section 2: Add New Series -->
            <section class="bg-gray-800 p-6 rounded-lg shadow-lg">
                <h2 class="text-2xl font-semibold mb-6">Add New Series</h2>
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
                                <input type="number" id="merged_season" name="merged_season" value="1" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white">
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
                                    <option value="English">English</option>
                                    <option value="Kannada">Kannada</option>
                                    <option value="Telugu">Telugu</option>
                                    <!-- UPDATE: Added new options -->
                                    <option value="Hindi">Hindi</option>
                                    <option value="Multi-language">Multi-language</option>
                                </select>
                            </div>
                            <div>
                                <label for="merged_duration" class="block text-sm font-medium text-gray-300">Total Duration (minutes)</label>
                                <input type="number" id="merged_duration" name="merged_duration" placeholder="120" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white">
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
                            <input type="number" id="season_number_1" name="season_number" value="1" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white" required>
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
                                        <input type="number" id="ep_number_1" name="ep_number[]" value="1" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white" required>
                                    </div>
                                    <div class="col-span-2">
                                        <label for="ep_video_url_1" class="block text-sm font-medium text-gray-300">Video URL</LAbel>
                                        <input type="url" id="ep_video_url_1" name="ep_video_url[]" placeholder="https://storage.com/..." class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white" required>
                                    </div>
                                    <div>
                                        <label for="ep_language_1" class="block text-sm font-medium text-gray-300">Language</LAbel>
                                        <select id="ep_language_1" name="ep_language[]" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white ep-language-select">
                                            <option value="English">English</option>
                                            <option value="Kannada">Kannada</option>
                                            <option value="Telugu">Telugu</option>
                                            <!-- UPDATE: Added new options -->
                                            <option value="Hindi">Hindi</option>
                                            <option value="Multi-language">Multi-language</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="ep_duration_1" class="block text-sm font-medium text-gray-300">Duration (minutes)</LAbel>
                                        <input type="number" id="ep_duration_1" name="ep_duration[]" placeholder="45" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 text-white" required>
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

        <!-- Section 3: Manage Content -->
        <section class="bg-gray-800 p-6 rounded-lg shadow-lg mt-8">
            <h2 class="text-2xl font-semibold mb-6">Manage Content</h2>
            
            <?php if (isset($admin_error)): ?>
                <div class="bg-red-500 text-white p-3 rounded-md mb-6 text-center">
                    <?= htmlspecialchars($admin_error) ?>
                </div>
            <?php endif; ?>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Title</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Type</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Genre</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Added By</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray