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

    // 2. Check for movie_id in URL
    if (!isset($_GET['movie_id'])) {
        header("Location: admin.php");
        exit;
    }
    
    $movie_id = intval($_GET['movie_id']);
    if ($movie_id <= 0) {
        header("Location: admin.php");
        exit;
    }

    // 3. Use a transaction to delete all related data
    $pdo->beginTransaction();
    try {
        // First, get the type
        $stmt_type = $pdo->prepare("SELECT type, title FROM movies WHERE movie_id = ?");
        $stmt_type->execute([$movie_id]);
        $movie = $stmt_type->fetch(PDO::FETCH_ASSOC);

        if (!$movie) {
            throw new Exception("Movie not found.");
        }

        // If it's a series, delete all children first
        if ($movie['type'] == 'series') {
            // Find all season_ids for this series
            $stmt_seasons = $pdo->prepare("SELECT season_id FROM seasons WHERE movie_id = ?");
            $stmt_seasons->execute([$movie_id]);
            $season_ids_raw = $stmt_seasons->fetchAll(PDO::FETCH_COLUMN);

            if ($season_ids_raw) {
                $in_query = implode(',', array_fill(0, count($season_ids_raw), '?'));
                
                // Delete episodes
                $stmt_del_ep = $pdo->prepare("DELETE FROM episodes WHERE season_id IN ($in_query)");
                $stmt_del_ep->execute($season_ids_raw);
            }
            
            // Delete seasons
            $stmt_del_se = $pdo->prepare("DELETE FROM seasons WHERE movie_id = ?");
            $stmt_del_se->execute([$movie_id]);
        }

        // Delete from watch history (for movies and series)
        $stmt_del_wh = $pdo->prepare("DELETE FROM watch_history WHERE movie_id = ?");
        $stmt_del_wh->execute([$movie_id]);

        // Finally, delete the main movie/series record
        $stmt_del_mov = $pdo->prepare("DELETE FROM movies WHERE movie_id = ?");
        $stmt_del_mov->execute([$movie_id]);

        // If all queries succeeded, commit the transaction
        $pdo->commit();
        $success = "Successfully deleted '{$movie['title']}'.";

    } catch (Exception $e) {
        // If any query failed, roll back all changes
        $pdo->rollBack();
        error_log($e->getMessage());
        $error = "Failed to delete item: " . $e->getMessage();
    }

    // 4. Redirect back to admin panel with a message
    // We'll use session flash messages for this
    if ($success) {
        $_SESSION['admin_feedback'] = ['type' => 'success', 'message' => $success];
    }
    if ($error) {
        $_SESSION['admin_feedback'] = ['type' => 'error', 'message' => $error];
    }
    
    header("Location: admin.php");
    exit;
?>