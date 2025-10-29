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
    $stmt = $pdo->query("
        SELECT m.* FROM movies m 
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
    </style>
</head>
<body class="bg-gray-900 text-white">

    <div class="container mx-auto max-w-6xl p-4">

        <header class="flex flex-wrap justify-between items-center mb-8 gap-4">
            <h1 class="text-3xl font-bold text-red-500">Admin Panel</h1>
            <div>
                <span class="text-gray-400 mr-4">Welcome, <?= htmlspecialchars($currentUser['email'] ?? 'Admin') ?>!</span>
                <a href="index.php" class="text-blue-400 hover:text-blue-300 mr-4">&larr; Back to Site</a>
                <a href="logout.php" class="text-red-500 hover:text-red-400">Logout</a>
            </div>
        </header>

        <!-- Error/Success Banners -->
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
         <?php if (isset($admin_error)): ?>
            <div class="bg-red-500 text-white p-3 rounded-md mb-6 text-center">
                <?= htmlspecialchars($admin_error) ?>
            </div>
        <?php endif; ?>

        <!-- Add Content Buttons -->
        <section class="mb-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            <a href="admin_add_movie.php" class="bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-6 rounded-lg text-center text-xl transition duration-300">
                + Add New Movie
            </a>
            <a href="admin_add_series.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-lg text-center text-xl transition duration-300">
                + Add New Series
            </a>
        </section>

        <!-- Manage Content Table -->
        <section class="bg-gray-800 p-6 rounded-lg shadow-lg mt-8">
            <h2 class="text-2xl font-semibold mb-6">Manage Content</h2>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Title</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Type</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Genre</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-gray-800 divide-y divide-gray-700">
                        <?php 
                        if (empty($content)) {
                            echo '<tr><td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-400 text-center">No content found.</td></tr>';
                        } else {
                            foreach ($content as $item) {
                                echo '<tr>';
                                echo '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">' . htmlspecialchars($item['title']) . '</td>';
                                echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">' . htmlspecialchars($item['is_series'] ? 'Series' : 'Movie') . '</td>';
                                echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">' . htmlspecialchars($item['genre']) . '</td>';
                                echo '<td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">';
                                
                                if ($item['is_series']) {
                                    echo '<a href="admin_edit_series.php?id=' . $item['movie_id'] . '" class="text-blue-400 hover:text-blue-300 mr-3">Edit</a>';
                                } else {
                                    echo '<a href="admin_edit_movie.php?id=' . $item['movie_id'] . '" class="text-blue-400 hover:text-blue-300 mr-3">Edit</a>';
                                }
                                
                                echo '<a href="admin_delete.php?id=' . $item['movie_id'] . '" class="text-red-400 hover:text-red-300" onclick="return confirm(\'Are you sure you want to delete this? This action cannot be undone.\')">Delete</a>';
                                echo '</td>';
                                echo '</tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>

    </div>
</body>
</html>


