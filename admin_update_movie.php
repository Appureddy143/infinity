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
        
        // 3. Get and sanitize data from the form
        $movie_id = intval($_POST['movie_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $release_date = trim($_POST['release_date'] ?? '');
        $genre = trim($_POST['genre'] ?? '');
        $poster_url = trim($_POST['poster_url'] ?? '');
        $video_url = trim($_POST['video_url'] ?? '');
        $duration_minutes = trim($_POST['duration_minutes'] ?? 0);
        $language = trim($_POST['language'] ?? 'English');

        // 4. Validate data
        if (empty($movie_id) || empty($title) || empty($video_url) || empty($duration_minutes)) {
            $error = "Invalid data. Missing required fields.";
        } else {
            // Convert duration from minutes to seconds for the database
            $duration_seconds = $duration_minutes * 60;
            
            // 5. Update database
            try {
                $sql = "UPDATE movies 
                        SET 
                            title = :title, 
                            description = :description, 
                            release_date = :release_date, 
                            genre = :genre, 
                            poster_url = :poster_url, 
                            video_url = :video_url, 
                            duration_seconds = :duration, 
                            language = :language
                        WHERE 
                            movie_id = :movie_id AND type = 'movie'";
                
                $stmt = $pdo->prepare($sql);
                
                $stmt->execute([
                    ':title' => $title,
                    ':description' => $description,
                    ':release_date' => $release_date,
                    ':genre' => $genre,
                    ':poster_url' => $poster_url,
                    ':video_url' => $video_url,
                    ':duration' => $duration_seconds,
                    ':language' => $language,
                    ':movie_id' => $movie_id
                ]);

                $success = "Movie '$title' was updated successfully! <a href='admin.php' class='font-bold underline'>&larr; Back to Admin Panel</a>";

            } catch (PDOException $e) {
                error_log($e->getMessage());
                $error = "A database error occurred. Could not update movie.";
            }
        }
    } else {
        // If not a POST request, redirect back to admin panel
        header("Location: admin.php");
        exit;
    }

    // 6. Display a feedback page to the admin
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