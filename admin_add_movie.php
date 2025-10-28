<?php
    session_start();
    require_once 'db_connect.php';

    // 1. Check if user is an Admin
    if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
        header("Location: index.php");
        exit;
    }

    $error = '';
    $success = '';

    // 2. Check if the form was submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        
        // Use a database transaction
        $pdo->beginTransaction();
        
        try {
            // 3. Get and sanitize Series data
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $release_date = trim($_POST['release_date'] ?? '');
            $genre = trim($_POST['genre'] ?? '');
            $poster_url = trim($_POST['poster_url'] ?? '');
            $upload_type = trim($_POST['upload_type'] ?? 'episodic');

            // 4. Validate Series data
            if (empty($title) || empty($description) || empty($poster_url)) {
                throw new Exception("Please fill in all required series fields (Title, Description, Poster).");
            }

            // 5. Insert the main Series into 'movies' table
            $sql_movie = "INSERT INTO movies (type, title, description, release_date, genre, poster_url, language) 
                          VALUES ('series', :title, :description, :release_date, :genre, :poster_url, 'Multi')";
            
            $stmt_movie = $pdo->prepare($sql_movie);
            $stmt_movie->execute([
                ':title' => $title,
                ':description' => $description,
                ':release_date' => $release_date,
                ':genre' => $genre,
                ':poster_url' => $poster_url
            ]);
            
            // Get the ID of the new series we just created
            $series_movie_id = $pdo->lastInsertId();

            // 6. Insert the Season
            // For now, we assume one season per series submission for simplicity.
            // You could expand this to select an existing series or season.
            $season_number = 1; 
            $season_title = trim($_POST['season_title'] ?? "Season 1");
            
            $sql_season = "INSERT INTO seasons (movie_id, season_number, title) VALUES (:movie_id, :season_number, :title)";
            $stmt_season = $pdo->prepare($sql_season);
            $stmt_season->execute([
                ':movie_id' => $series_movie_id,
                ':season_number' => $season_number,
                ':title' => $season_title
            ]);
            
            $season_id = $pdo->lastInsertId();

            // 7. Process Episodes based on upload type
            $sql_episode = "INSERT INTO episodes (
                                season_id, episode_number, title, video_url, 
                                thumbnail_url, duration_seconds, language
                            ) VALUES (
                                :season_id, :ep_num, :ep_title, :ep_video_url, 
                                :ep_thumb_url, :ep_duration, :ep_lang
                            )";
            $stmt_episode = $pdo->prepare($sql_episode);

            if ($upload_type == 'merged') {
                // --- MERGED FILE ---
                $video_url = trim($_POST['merged_video_url'] ?? '');
                $duration_min = trim($_POST['merged_duration_minutes'] ?? 0);
                $language = trim($_POST['merged_language'] ?? 'English');
                
                if (empty($video_url) || $duration_min <= 0) {
                    throw new Exception("Merged file is missing a video URL or duration.");
                }

                $stmt_episode->execute([
                    ':season_id' => $season_id,
                    ':ep_num' => 1,
                    ':ep_title' => "Full Season",
                    ':ep_video_url' => $video_url,
                    ':ep_thumb_url' => $poster_url, // Use main poster as thumb
                    ':ep_duration' => $duration_min * 60,
                    ':ep_lang' => $language
                ]);

            } else {
                // --- EPISODIC FILES ---
                $ep_titles = $_POST['ep_title'] ?? [];
                $ep_video_urls = $_POST['ep_video_url'] ?? [];
                $ep_thumb_urls = $_POST['ep_thumbnail_url'] ?? [];
                $ep_durations = $_POST['ep_duration_minutes'] ?? [];
                $ep_languages = $_POST['ep_language'] ?? [];

                if (empty($ep_titles) || empty($ep_video_urls)) {
                    throw new Exception("No episodes were submitted.");
                }

                $episode_count = count($ep_titles);
                for ($i = 0; $i < $episode_count; $i++) {
                    $ep_title = trim($ep_titles[$i] ?? "Episode " . ($i + 1));
                    $ep_video_url = trim($ep_video_urls[$i] ?? '');
                    $ep_thumb_url = trim($ep_thumb_urls[$i] ?? $poster_url); // Default to poster
                    $ep_duration = trim($ep_durations[$i] ?? 0);
                    $ep_lang = trim($ep_languages[$i] ?? 'English');

                    if (empty($ep_video_url) || $ep_duration <= 0) {
                        throw new Exception("Episode " . ($i + 1) . " is missing a video URL or duration.");
                    }

                    $stmt_episode->execute([
                        ':season_id' => $season_id,
                        ':ep_num' => ($i + 1), // Episode number
                        ':ep_title' => $ep_title,
                        ':ep_video_url' => $ep_video_url,
                        ':ep_thumb_url' => $ep_thumb_url,
                        ':ep_duration' => $ep_duration * 60,
                        ':ep_lang' => $ep_lang
                    ]);
                }
            }
            
            // 8. If all was successful, commit the transaction
            $pdo->commit();
            $success = "Series '$title' was added successfully! <a href='admin.php' class='font-bold underline'>Add another?</a>";

        } catch (Exception $e) {
            // 9. If anything failed, roll back the transaction
            $pdo->rollBack();
            $error = "Failed to add series: " . $e->getMessage();
        }
    } else {
        // If not a POST request, redirect
        header("Location: admin.php");
        exit;
    }

    // 10. Display a feedback page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Feedback - YourStream</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f0f0f; color: #ffffff; }
    </style>
</head>
<body class="antialiased">
    <div class="container mx-auto max-w-lg min-h-screen bg-black flex flex-col items-center justify-center p-6">
        <h1 class="text-3xl font-bold text-red-500 mb-8">Admin Action Status</h1>
        
        <?php if ($error): ?>
            <div class="w-full max-w-sm bg-red-900 border border-red-700 text-red-100 px-4 py-3 rounded-lg relative" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                <a href="admin.php" class="block text-center mt-4 font-semibold text-red-200 hover:text-white">&larr; Go Back to Admin Panel</a>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="w-full max-w-sm bg-green-900 border border-green-700 text-green-100 px-4 py-3 rounded-lg relative" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline"><?php echo $success; // Already includes a link ?></span>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>