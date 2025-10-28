<?php
    session_start();
    require_once 'db_connect.php';

    // 1. Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'] ?? 'User';
    $user_email = '';
    $watch_history = [];

    try {
        // 2. Get User's Email
        $stmt_user = $pdo->prepare("SELECT email FROM users WHERE user_id = ?");
        $stmt_user->execute([$user_id]);
        $user_email = $stmt_user->fetchColumn();

        // 3. Get User's Full Watch History
        // This query gets the most recently watched item, whether it's a movie or an episode,
        // and groups them so a series only appears once.
        $stmt_history = $pdo->prepare("
            SELECT 
                m.movie_id, 
                m.title, 
                m.poster_url, 
                m.type,
                wh.progress_seconds,
                wh.total_duration_seconds,
                e.title AS episode_title,
                s.season_number,
                e.episode_number,
                wh.last_watched_at
            FROM watch_history wh
            JOIN movies m ON wh.movie_id = m.movie_id
            LEFT JOIN episodes e ON wh.episode_id = e.episode_id
            LEFT JOIN seasons s ON e.season_id = s.season_id
            WHERE wh.user_id = ?
            AND wh.progress_seconds > 0
            -- This part gets only the *latest* episode watched for each series
            AND (wh.episode_id = (
                SELECT sub_wh.episode_id 
                FROM watch_history sub_wh
                WHERE sub_wh.user_id = wh.user_id AND sub_wh.movie_id = wh.movie_id
                ORDER BY sub_wh.last_watched_at DESC
                LIMIT 1
            ) OR wh.episode_id IS NULL)
            ORDER BY wh.last_watched_at DESC;
        ");
        $stmt_history->execute([$user_id]);
        $watch_history = $stmt_history->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log($e->getMessage());
        $error = "A database error occurred.";
    }

    // Helper function to format progress
    function get_progress_percent($current, $total) {
        if (!$total || $total == 0) return 0;
        return round(($current / $total) * 100);
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - YourStream</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f0f0f; color: #ffffff; }
        .progress-bar-bg { background-color: #404040; } /* neutral-700 */
        .progress-bar-fg { background-color: #ef4444; } /* red-500 */
    </style>
</head>
<body class="antialiased pb-24">

    <!-- Header -->
    <header class="bg-black sticky top-0 z-50 py-4 px-4 shadow-lg shadow-zinc-900/50">
        <div class="container mx-auto max-w-lg flex justify-between items-center">
            <h1 class="text-2xl font-bold text-red-500">YourStream</h1>
            <!-- Search Icon -->
            <button id="search-btn" class="text-white hover:text-red-500 transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </button>
        </div>
    </header>

    <!-- Search Bar (Hidden by default) -->
    <div id="search-bar" class="hidden bg-zinc-900 p-4 sticky top-[64px] z-40">
        <form action="search.php" method="GET" class="container mx-auto max-w-lg">
            <input type="search" name="q" class="w-full bg-zinc-800 text-white placeholder-gray-400 rounded-lg py-3 px-4 focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Search for movies or series...">
        </form>
    </div>

    <!-- Main Content -->
    <main class="container mx-auto max-w-lg p-4">

        <!-- Profile Info -->
        <section class="mb-8">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-zinc-800 rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-semibold"><?php echo htmlspecialchars($username); ?></h2>
                    <p class="text-gray-400"><?php echo htmlspecialchars($user_email); ?></p>
                </div>
            </div>
            <a href="logout.php" class="block w-full text-center bg-zinc-800 hover:bg-zinc-700 text-white font-medium py-3 px-4 rounded-lg mt-6 transition-colors duration-200">
                Log Out
            </a>
            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                <a href="admin.php" class="block w-full text-center bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-4 rounded-lg mt-4 transition-colors duration-200">
                    Go to Admin Panel
                </a>
            <?php endif; ?>
        </section>

        <!-- Watch History -->
        <section>
            <h3 class="text-xl font-semibold mb-4">My Watch History</h3>

            <div class="space-y-4">
                <?php if (empty($watch_history)): ?>
                    <p class="text-gray-400">You haven't watched anything yet.</p>
                <?php else: ?>
                    <?php foreach ($watch_history as $item): ?>
                        <div class="bg-zinc-900 rounded-lg overflow-hidden flex items-start space-x-3 p-3">
                            <img src="<?php echo htmlspecialchars($item['poster_url']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="w-20 h-28 object-cover rounded-md" onerror="this.src='httpsRead.co/80x112/000000/ffffff?text=Error'">
                            <div class="flex-1">
                                <h4 class="font-semibold text-lg"><?php echo htmlspecialchars($item['title']); ?></h4>
                                <?php if ($item['type'] == 'series' && $item['episode_title']): ?>
                                    <p class="text-sm text-gray-400">
                                        S<?php echo htmlspecialchars($item['season_number']); ?>:E<?php echo htmlspecialchars($item['episode_number']); ?> - <?php echo htmlspecialchars($item['episode_title']); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <p class="text-xs text-gray-500 mt-1">
                                    Watched on: <?php echo date('M d, Y', strtotime($item['last_watched_at'])); ?>
                                </p>

                                <!-- Progress Bar -->
                                <?php $progress = get_progress_percent($item['progress_seconds'], $item['total_duration_seconds']); ?>
                                <?php if ($progress > 0 && $progress < 95): // Don't show for fully watched ?>
                                    <div class="w-full progress-bar-bg rounded-full h-1.5 mt-2">
                                        <div class="progress-bar-fg h-1.5 rounded-full" style="width: <?php echo $progress; ?>%"></div>
                                    </div>
                                <?php elseif ($progress >= 95): ?>
                                    <p class="text-xs font-medium text-green-400 mt-2">Completed</p>
                                <?php endif; ?>

                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

    </main>

    <!-- Bottom Navigation -->
    <nav class="fixed bottom-0 left-0 right-0 bg-black border-t border-zinc-800 shadow-lg z-50">
        <div class="container mx-auto max-w-lg flex justify-around py-3">
            <!-- Home -->
            <a href="index.php" class="flex flex-col items-center justify-center text-gray-400 hover:text-red-500 transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1V9a1 1 0 011-1h2a1 1 0 011 1v10a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="text-xs font-medium mt-1">Home</span>
            </a>
            
            <!-- Profile -->
            <a href="profile.php" class="flex flex-col items-center justify-center text-red-500"> <!-- Active Link -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="text-xs font-medium mt-1">Profile</span>
            </a>
        </div>
    </nav>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchBtn = document.getElementById('search-btn');
            const searchBar = document.getElementById('search-bar');

            searchBtn.addEventListener('click', () => {
                searchBar.classList.toggle('hidden');
            });
        });
    </script>

</body>
</html>

