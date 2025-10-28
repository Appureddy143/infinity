<?php
    session_start();
    
    // --- SIMPLE PASSWORD PROTECTION ---
    // In a real app, you'd have a separate admin login.
    // For now, we'll just check for a specific session variable
    // You could set this in login_process.php: $_SESSION['is_admin'] = true;
    
    // if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    //     die("Access Denied. You are not an admin.");
    // }
    
    // Placeholder: Comment out the above for testing
    // echo "Welcome, Admin!";

    $movieMessage = $_GET['movie_msg'] ?? '';
    $seriesMessage = $_GET['series_msg'] ?? '';
?>

<!DOCTYPE html>
<html lang="en" class="bg-gray-800">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="font-sans text-gray-200 p-6">

    <h1 class="text-3xl font-bold text-white mb-8">Admin Panel</h1>

    <!-- Add Movie Section (Unchanged) -->
    <section class="bg-gray-900 p-6 rounded-lg shadow-lg mb-8 max-w-2xl mx-auto">
        <!-- ... existing movie form ... -->
        <h2 class="text-2xl font-semibold mb-4 text-white">Add New Movie</h2>
        
        <?php if ($movieMessage): ?>
            <p class="mb-4 <?php echo str_contains($movieMessage, 'Success') ? 'text-green-400' : 'text-red-400'; ?>">
                <?php echo htmlspecialchars($movieMessage); ?>
            </p>
        <?php endif; ?>

        <!-- Form now points to admin_add_movie.php -->
        <form action="admin_add_movie.php" method="POST" class="space-y-4">
            <!-- Hidden field for type -->
            <input type="hidden" name="type" value="movie">

            <div>
                <label for="movie_title" class="block text-sm font-medium">Title</label>
                <input type="text" name="title" id="movie_title" required class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white focus:border-red-500 focus:ring-red-500">
            </div>

            <div>
                <label for="movie_description" class="block text-sm font-medium">Description</label>
                <textarea name="description" id="movie_description" rows="3" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white focus:border-red-500 focus:ring-red-500"></textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="movie_poster_url" class="block text-sm font-medium">Poster URL</label>
                    <input type="text" name="poster_url" id="movie_poster_url" placeholder="https://..." required class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white focus:border-red-500 focus:ring-red-500">
                </div>
                <div>
                    <label for="movie_video_url" class="block text-sm font-medium">Video URL</label>
                    <input type="text" name="video_url" id="movie_video_url" placeholder="https://.../movie.mp4" required class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white focus:border-red-500 focus:ring-red-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="movie_language" class="block text-sm font-medium">Language</label>
                    <select name="language" id="movie_language" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white focus:border-red-500 focus:ring-red-500">
                        <option>Kannada</option>
                        <option>Telugu</option>
                        <option>Hindi</option>
                        <option>English</option>
                        <option>Multi</option>
                    </select>
                </div>
                <div>
                    <label for="movie_genre" class="block text-sm font-medium">Genre</label>
                    <input type="text" name="genre" id="movie_genre" placeholder="Action, Thriller" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white focus:border-red-500 focus:ring-red-500">
                </div>
                 <div>
                    <label for="movie_duration" class="block text-sm font-medium">Duration</DURlabel>
                    <input type="text" name="duration" id="movie_duration" placeholder="2h 15m" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white focus:border-red-500 focus:ring-red-500">
                </div>
            </div>

            <button type="submit" class="w-full bg-red-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-red-700 transition">
                Add Movie
            </button>
        </form>
    </section>

    <!-- Add Series Section (MODIFIED) -->
    <section class="bg-gray-900 p-6 rounded-lg shadow-lg max-w-2xl mx-auto">
        <h2 class="text-2xl font-semibold mb-4 text-white">Add New Series</h2>
        
        <?php if ($seriesMessage): ?>
            <p class="mb-4 <?php echo str_contains($seriesMessage, 'Success') ? 'text-green-400' : 'text-red-400'; ?>">
                <?php echo htmlspecialchars($seriesMessage); ?>
            </p>
        <?php endif; ?>

        <!-- Form now points to admin_add_series.php -->
        <form action="admin_add_series.php" method="POST" class="space-y-4">
            <!-- Hidden field for type -->
            <input type="hidden" name="type" value="series">
            
            <!-- Series Details -->
            <div>
                <label for="series_title" class="block text-sm font-medium">Series Title</label>
                <input type="text" name="title" id="series_title" required class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white focus:border-red-500 focus:ring-red-500">
            </div>
            <div>
                <label for="series_description" class="block text-sm font-medium">Series Description</label>
                <textarea name="description" id="series_description" rows="3" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white focus:border-red-500 focus:ring-red-500"></textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="series_poster_url" class="block text-sm font-medium">Series Poster URL</label>
                    <input type="text" name="poster_url" id="series_poster_url" placeholder="https://..." required class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white focus:border-red-500 focus:ring-red-500">
                </div>
                <div>
                    <label for="series_language" class="block text-sm font-medium">Language</label>
                    <select name="language" id="series_language" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white focus:border-red-500 focus:ring-red-500">
                        <option>Kannada</option>
                        <option>Telugu</option>
                        <option>Hindi</option>
                        <option>English</option>
                        <option>Multi</option>
                    </select>
                </div>
            </div>
             <div>
                <label for="series_genre" class="block text-sm font-medium">Genre</label>
                <input type="text" name="genre" id="series_genre" placeholder="Drama, Family" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white focus:border-red-500 focus:ring-red-500">
            </div>
            
            <hr class="border-gray-700 my-4">
            
            <!-- Season Details -->
            <h3 class="text-lg font-semibold text-white">Season 1 Details</h3>
            <div>
                <label for="season_title" class="block text-sm font-medium">Season Title (e.g., "Season 1")</label>
                <input type="text" name="season_title" id="season_title" required value="Season 1" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white focus:border-red-500 focus:ring-red-500">
            </div>

            <!-- NEW: Episode Format Toggle -->
            <div class="space-y-2">
                <label class="block text-sm font-medium">Episode Format</label>
                <div class="flex items-center space-x-4">
                    <label class="flex items-center">
                        <input type="radio" name="episode_format" value="episodic" checked class="text-red-600 focus:ring-red-500">
                        <span class="ml-2">Episodic</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="episode_format" value="merged" class="text-red-600 focus:ring-red-500">
                        <span class="ml-2">Merged Season File</span>
                    </label>
                </div>
            </div>
            
            <!-- NEW: Dynamic Episodes Container -->
            <div id="episodes-container" class="space-y-6">
                <!-- Episode 1 (Mandatory) -->
                <div class="episode-block pl-4 border-l-2 border-gray-700 space-y-4">
                    <h4 class="text-md font-semibold text-white">Episode 1</h4>
                    <div>
                        <label for="ep_title_1" class="block text-sm font-medium">Episode Title</label>
                        <input type="text" name="ep_title[]" id="ep_title_1" required placeholder="The Beginning" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white focus:border-red-500 focus:ring-red-500">
                    </div>
                     <div>
                        <label for="ep_description_1" class="block text-sm font-medium">Episode Description</label>
                        <textarea name="ep_description[]" id="ep_description_1" rows="2" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white focus:border-red-500 focus:ring-red-500"></textarea>
                    </div>
                    <div>
                        <label for="ep_video_url_1" class="block text-sm font-medium">Episode Video URL</label>
                        <input type="text" name="ep_video_url[]" id="ep_video_url_1" required placeholder="https://.../s1e1.mp4" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white focus:border-red-500 focus:ring-red-500">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="ep_duration_1" class="block text-sm font-medium">Episode Duration (in minutes)</label>
                            <input type="number" name="ep_duration[]" id="ep_duration_1" required placeholder="30" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white focus:border-red-500 focus:ring-red-500">
                        </div>
                        <div>
                            <label for="ep_language_1" class="block text-sm font-medium">Episode Language</label>
                            <select name="ep_language[]" id="ep_language_1" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white focus:border-red-500 focus:ring-red-500">
                                <option>Kannada</option>
                                <option>Telugu</option>
                                <option>Hindi</option>
                                <option>English</option>
                                <option>Multi</option>
                            </select>
                        </div>
                    </div>
                </div>
                <!-- More episodes will be added here by JS -->
            </div>
            
            <!-- NEW: Merged File Container (Hidden by default) -->
            <div id="merged-file-container" class="hidden space-y-4 pl-4 border-l-2 border-gray-700">
                <h4 class="text-md font-semibold text-white">Merged Season File</h4>
                <div>
                    <label for="merged_title" class="block text-sm font-medium">Title (e.g., "Full Season")</label>
                    <input type="text" name="merged_title" id="merged_title" placeholder="Full Season 1" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white focus:border-red-500 focus:ring-red-500">
                </div>
                 <div>
                    <label for="merged_description" class="block text-sm font-medium">Description</label>
                    <textarea name="merged_description" id="merged_description" rows="2" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white focus:border-red-500 focus:ring-red-500"></textarea>
                </div>
                <div>
                    <label for="merged_video_url" class="block text-sm font-medium">Video URL</label>
                    <input type="text" name="merged_video_url" id="merged_video_url" placeholder="https://.../s1_merged.mp4" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white focus:border-red-500 focus:ring-red-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="merged_duration" class="block text-sm font-medium">Total Duration (in minutes)</label>
                        <input type="number" name="merged_duration" id="merged_duration" placeholder="180" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white focus:border-red-500 focus:ring-red-500">
                    </div>
                    <div>
                        <label for="merged_language" class="block text-sm font-medium">Language</label>
                        <select name="merged_language" id="merged_language" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white focus:border-red-500 focus:ring-red-500">
                            <option>Kannada</option>
                            <option>Telugu</option>
                            <option>Hindi</option>
                            <option>English</option>
                            <option>Multi</option>
                        </select>
                    </div>
                </div>
            </div>
 
            let episodeCount = 1;

            // Function to toggle views
            function toggleFormatView() {
                if (document.querySelector('input[name="episode_format"]:checked').value === 'episodic') {
                    episodesContainer.classList.remove('hidden');
                    addEpisodeBtn.classList.remove('hidden');
                    mergedContainer.classList.add('hidden');
                    // Re-enable required fields for episodic
                    episodesContainer.querySelectorAll('input, textarea').forEach(el => el.required = true);
                    // Disable required fields for merged
                    mergedContainer.querySelectorAll('input, textarea').forEach(el => el.required = false);
                } else { // 'merged'
                    episodesContainer.classList.add('hidden');
                    addEpisodeBtn.classList.add('hidden');
                    mergedContainer.classList.remove('hidden');
                    // Disable required fields for episodic
                    episodesContainer.querySelectorAll('input, textarea').forEach(el => el.required = false);
                    // Re-enable required fields for merged
                    mergedContainer.querySelectorAll('input, textarea').forEach(el => el.required = true);
                }
            }

            // Add listener to radio buttons
            episodeFormatRadios.forEach(radio => {
                radio.addEventListener('change', toggleFormatView);
            });

            // Add listener to "Add Episode" button
            addEpisodeBtn.addEventListener('click', function() {
                episodeCount++;
                const newEpisodeBlock = document.createElement('div');
                newEpisodeBlock.className = 'episode-block pl-4 border-l-2 border-gray-700 space-y-4 pt-4 mt-4 border-t border-gray-700 relative';
                
                newEpisodeBlock.innerHTML = `
                    <button type="button" class="remove-episode-btn absolute top-4 right-0 text-gray-400 hover:text-red-500" title="Remove episode">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <h4 class="text-md font-semibold text-white">Episode ${episodeCount}</h4>
                    <div>
                        <label for="ep_title_${episodeCount}" class="block text-sm font-medium">Episode Title</label>
                        <input type="text" name="ep_title[]" id="ep_title_${episodeCount}" required placeholder="Episode ${episodeCount}" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white focus:border-red-500 focus:ring-red-500">
                    </div>
                    <div>
                        <label for="ep_description_${episodeCount}" class="block text-sm font-medium">Episode Description</label>
                        <textarea name="ep_description[]" id="ep_description_${episodeCount}" rows="2" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white focus:border-red-500 focus:ring-red-500"></textarea>
                    </div>
                    <div>
                        <label for="ep_video_url_${episodeCount}" class="block text-sm font-medium">Episode Video URL</label>
                        <input type="text" name="ep_video_url[]" id="ep_video_url_${episodeCount}" required placeholder="https://.../s1e${episodeCount}.mp4" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white focus:border-red-500 focus:ring-red-500">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="ep_duration_${episodeCount}" class="block text-sm font-medium">Episode Duration (in minutes)</label>
                            <input type="number" name="ep_duration[]" id="ep_duration_${episodeCount}" required placeholder="30" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white focus:border-red-500 focus:ring-red-500">
                        </div>
                        <div>
                            <label for="ep_language_${episodeCount}" class="block text-sm font-medium">Episode Language</label>
                            <select name="ep_language[]" id="ep_language_${episodeCount}" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md shadow-sm p-2 text-white focus:border-red-500 focus:ring-red-500">
                                <option>Kannada</option>
                                <option>Telugu</option>
                                <option>Hindi</option>
                                <option>English</option>
                                <option>Multi</option>
                            </select>
                        </div>
                    </div>
                `;
                
                episodesContainer.appendChild(newEpisodeBlock);

                // Add remove listener to the new button
                newEpisodeBlock.querySelector('.remove-episode-btn').addEventListener('click', function() {
                    newEpisodeBlock.remove();
                    // We don't decrement episodeCount to avoid ID conflicts.
                    // Renumbering is complex; this is simpler and works.
                });
            });

            // Initial check
            toggleFormatView();
        });
    </script>
</body>
</html>


