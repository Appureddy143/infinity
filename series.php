<?php
    session_start();

    // Helper function to format duration
    function format_duration($seconds) {
        if ($seconds < 3600) {
            $m = floor($seconds / 60);
            return "{$m}m";
        } else {
            $h = floor($seconds / 3600);
            $m = floor(($seconds % 3600) / 60);
            return "{$h}h {$m}m";
        }
    }

    // --- DATABASE CONNECTION (CONCEPT) ---
    // $dsn = "pgsql:host=...;port...;dbname=...;user=...;password=...";
    // $pdo = new PDO($dsn, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo = null; // Placeholder

    // --- Data Initialization ---
    $series = null;
    $seasons = [];
    $episodesBySeason = [];
    $error = null;

    // --- 1. Get Series ID from URL ---
    $series_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($series_id > 0) {
        try {
            // --- 2. Fetch Series Details ---
            // $stmt_series = $pdo->prepare("SELECT * FROM movies WHERE movie_id = ? AND type = 'series'");
            // $stmt_series->execute([$series_id]);
            // $series = $stmt_series->fetch(PDO::FETCH_ASSOC);

            // Mock Data (replace with DB call)
            if ($series_id == 124) { // Mocking the series from admin panel
                 $series = [
                    'movie_id' => 124,
                    'title' => 'Epic Series Title',
                    'description' => 'A thrilling adventure through space and time.',
                    'poster_url' => 'https://placehold.co/400x600/1a1a1a/ffffff?text=Series+Poster',
                    'genre' => 'Sci-Fi, Adventure',
                    'release_date' => '2024-01-15'
                ];
            } else {
                $series = false;
            }


            if ($series) {
                // --- 3. Fetch Seasons for this Series ---
                // $stmt_seasons = $pdo->prepare("SELECT * FROM seasons WHERE movie_id = ? ORDER BY season_number");
                // $stmt_seasons->execute([$series_id]);
                // $seasons = $stmt_seasons->fetchAll(PDO::FETCH_ASSOC);
                
                // Mock Data
                $seasons = [
                    ['season_id' => 457, 'movie_id' => 124, 'season_number' => 1, 'title' => 'Season 1: The Beginning']
                ];

                if ($seasons) {
                    // --- 4. Fetch All Episodes for All Seasons ---
                    // This is more efficient than N+1 queries inside the loop
                    // $season_ids = array_map(fn($s) => $s['season_id'], $seasons);
                    // $placeholders = implode(',', array_fill(0, count($season_ids), '?'));
                    
                    // $sql_episodes = "SELECT * FROM episodes WHERE season_id IN ($placeholders) ORDER BY episode_number";
                    // $stmt_episodes = $pdo->prepare($sql_episodes);
                    // $stmt_episodes->execute($season_ids);
                    
                    // $all_episodes = $stmt_episodes->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Mock Data
                    $all_episodes = [
                        ['episode_id' => 1, 'season_id' => 457, 'episode_number' => 1, 'title' => 'The Pilot', 'description' => 'Our heroes meet.', 'thumbnail_url' => 'https://placehold.co/320x180/2a2a2a/ffffff?text=Ep+1', 'language' => 'English', 'duration_seconds' => 2700],
                        ['episode_id' => 2, 'season_id' => 457, 'episode_number' => 2, 'title' => 'The Adventure', 'description' => 'The journey begins.', 'thumbnail_url' => 'https://placehold.co/320x180/2a2a2a/ffffff?text=Ep+2', 'language' => 'Kannada', 'duration_seconds' => 2850]
                    ];

                    // Group episodes by their season_id for easy lookup
                    foreach ($all_episodes as $episode) {
                        $episodesBySeason[$episode['season_id']][] = $episode;
                    }
                }

            } else {
                $error = "Series not found.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    } else {
        $error = "Invalid Series ID.";
    }
?>

