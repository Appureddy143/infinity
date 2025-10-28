<?php
    session_start();
    require_once 'db_connect.php';

    // 1. Check if user is an Admin
    if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
        header("Location: login.php");
        exit;
    }

    $error = '';
    $success = '';

    // 2. Check if the form was submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        
        $pdo->beginTransaction();
        try {
            // 3. Get main series data
            $movie_id = intval($_POST['movie_id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $release_date = trim($_POST['release_date'] ?? '');
            $genre = trim($_POST['genre'] ?? '');
            $poster_url = trim($_POST['poster_url'] ?? '');

            if (empty($movie_id) || empty($title)) {
                throw new Exception("Invalid series data. Missing required fields.");
            }

            // 4. Update the main series in `movies` table
            $stmt_movie = $pdo->prepare("
                UPDATE movies 
                SET title = ?, description = ?, release_date = ?, genre = ?, poster_url = ?
                WHERE movie_id = ? AND type = 'series'
            ");
            $stmt_movie->execute([$title, $description, $release_date, $genre, $poster_url, $movie_id]);

            // 5. Get data for deletions
            $seasons_to_delete = $_POST['delete_season'] ?? [];
            $episodes_to_delete = $_POST['delete_episode'] ?? [];

            // 6. Process Seasons and Episodes
            $season_ids = $_POST['season_id'] ?? [];
            
            foreach ($season_ids as $season_id) {
                // $season_id will be an integer (e.g., 123) for existing or a string (e.g., 'new_1') for new

                // --- 6a. Handle Season Deletion ---
                if (in_array($season_id, $seasons_to_delete)) {
                    if (is_numeric($season_id)) {
                        // Delete existing season (and its episodes) from DB
                        $stmt_del_ep = $pdo->prepare("DELETE FROM episodes WHERE season_id = ?");
                        $stmt_del_ep->execute([$season_id]);
                        $stmt_del_se = $pdo->prepare("DELETE FROM seasons WHERE season_id = ? AND movie_id = ?");
                        $stmt_del_se->execute([$season_id, $movie_id]);
                    }
                    continue; // Skip to next season_id
                }
                
                $season_title = $_POST['season_title'][$season_id] ?? 'New Season';
                $season_number = $_POST['season_number'][$season_id] ?? 1;
                $current_season_db_id = null;

                // --- 6b. Handle Season Update/Insert ---
                if (is_numeric($season_id)) {
                    // This is an EXISTING season, UPDATE it
                    $current_season_db_id = $season_id;
                    $stmt_se = $pdo->prepare("UPDATE seasons SET title = ?, season_number = ? WHERE season_id = ? AND movie_id = ?");
                    $stmt_se->execute([$season_title, $season_number, $current_season_db_id, $movie_id]);
                } else {
                    // This is a NEW season, INSERT it
                    $stmt_se = $pdo->prepare("INSERT INTO seasons (movie_id, title, season_number) VALUES (?, ?, ?)");
                    $stmt_se->execute([$movie_id, $season_title, $season_number]);
                    $current_season_db_id = $pdo->lastInsertId(); // Get the new ID for adding episodes
                }

                // --- 6c. Process Episodes for this Season ---
                $episode_ids = $_POST['episode_id'][$season_id] ?? [];
                
                foreach ($episode_ids as $episode_id) {
                    // $episode_id will be an int (e.g., 456) for existing or string (e.g., 'new_1') for new
                    
                    // Handle Episode Deletion
                    if (in_array($episode_id, $episodes_to_delete)) {
                        if (is_numeric($episode_id)) {
                            $stmt_del_ep = $pdo->prepare("DELETE FROM episodes WHERE episode_id = ? AND season_id = ?");
                            $stmt_del_ep->execute([$episode_id, $current_season_db_id]);
                        }
                        continue; // Skip to next episode_id
                    }

                    // Get episode data
                    $ep_title = $_POST['ep_title'][$season_id][$episode_id] ?? 'New Episode';
                    $ep_number = $_POST['ep_number'][$season_id][$episode_id] ?? 1;
                    $ep_video_url = $_POST['ep_video_url'][$season_id][$episode_id] ?? '';
                    $ep_thumbnail_url = $_POST['ep_thumbnail_url'][$season_id][$episode_id] ?? '';
                    $ep_duration_minutes = $_POST['ep_duration_minutes'][$season_id][$episode_id] ?? 0;
                    $ep_duration_seconds = $ep_duration_minutes * 60;
                    $ep_language = $_POST['ep_language'][$season_id][$episode_id] ?? 'English';

                    // Handle Episode Update/Insert
                    if (is_numeric($episode_id)) {
                        // EXISTING episode, UPDATE it
                        $stmt_ep = $pdo->prepare("
                            UPDATE episodes 
                            SET title = ?, episode_number = ?, video_url = ?, thumbnail_url = ?, duration_seconds = ?, language = ?
                            WHERE episode_id = ? AND season_id = ?
                        ");
                        $stmt_ep->execute([$ep_title, $ep_number, $ep_video_url, $ep_thumbnail_url, $ep_duration_seconds, $ep_language, $episode_id, $current_season_db_id]);
                    } else {
                        // NEW episode, INSERT it
                        $stmt_ep = $pdo->prepare("
                            INSERT INTO episodes (season_id, title, episode_number, video_url, thumbnail_url, duration_seconds, language)
                            VALUES (?, ?, ?, ?, ?, ?, ?)
                        ");
                        $stmt_ep->execute([$current_season_db_id, $ep_title, $ep_number, $ep_video_url, $ep_thumbnail_url, $ep_duration_seconds, $ep_language]);
                    }
                }
            }

            // 7. Commit transaction
            $pdo->commit();
            $success = "Series '$title' was updated successfully! <a href='admin.php' class='font-bold underline'>&larr; Back to Admin Panel</a>";

        } catch (Exception $e) {
            $pdo->rollBack();
            error_log($e->getMessage());
            $error = "A database error occurred. Could not update series: " . $e->getMessage();
        }
    } else {
        header("Location: admin.php");
        exit;
    }

    // 8. Display a feedback page
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