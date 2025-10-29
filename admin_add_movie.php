<?php
require 'db_connect.php';
// Security Check: Make sure user is an admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: login.php?error=Access denied. Admins only.');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Movie - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-900 text-white">
    <div class="container mx-auto max-w-2xl p-4">
        <header class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-red-500">Add New Movie</h1>
            <a href="admin.php" class="text-blue-400 hover:text-blue-300">&larr; Back to Admin Panel</a>
        </header>

        <!-- Form Section -->
        <section class="bg-gray-800 p-6 rounded-lg shadow-lg">
            <!-- The form action MUST point to the process file -->
            <form action="admin_add_movie_process.php" method="POST">
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
    </div>
</body>
</html>