<?php
    session_start();
    require_once 'db_connect.php';

    $video_url = '';
    $video_title = 'Video Player';
    $poster_url = 'https://placehold.co/1280x720/000000/ffffff?text=Loading...';
    $start_at_seconds = 0; // Default to start from beginning
    $movie_id = null;
    $episode_id = null;
    $user_id = $_SESSION['user_id'] ?? null;
    $error = '';
    $subtitles = [];
    $audio_tracks = [];

    try {
        if (isset($_GET['movie_id'])) {
            // --- IT'S A MOVIE ---
            $movie_id = intval($_GET['movie_id']);
            $stmt = $pdo->prepare("SELECT * FROM movies WHERE movie_id = ? AND type = 'movie'");
            $stmt->execute([$movie_id]);
            $video = $stmt->fetch();

            if ($video) {
                $video_url = $video['video_url'];
                $video_title = $video['title'];
                $poster_url = $video['poster_url'];
                
                // TODO: Add logic here to find subtitle/audio tracks if stored
                // Example: $subtitles = [['label' => 'English', 'src' => $video['subtitle_en_url']]];
                // Example: $audio_tracks = [['label' => 'Hindi', 'src' => $video['audio_hi_url']]];

            } else {
                $error = "Movie not found.";
            }

        } elseif (isset($_GET['episode_id'])) {
            // --- IT'S A SERIES EPISODE ---
            $episode_id = intval($_GET['episode_id']);
            $stmt = $pdo->prepare("
                SELECT e.*, m.movie_id, m.title AS series_title, s.season_number 
                FROM episodes e
                JOIN seasons s ON e.season_id = s.season_id
                JOIN movies m ON s.movie_id = m.movie_id
                WHERE e.episode_id = ?
            ");
            $stmt->execute([$episode_id]);
            $video = $stmt->fetch();

            if ($video) {
                $video_url = $video['video_url'];
                $video_title = $video['series_title'] . " - S" . $video['season_number'] . ":E" . $video['episode_number'] . " " . $video['title'];
                $poster_url = $video['thumbnail_url'] ?? 'httpsRead.co/1280x720/000000/ffffff?text=Loading...';
                $movie_id = $video['movie_id']; // The parent series ID
                
                // Set language based on episode data
                if ($video['language'] && $video['video_url']) {
                     // This is a simplified example. Real audio tracks are muxed into the video.
                     // But we can use this to show the *label* of the current file's language.
                     $audio_tracks[] = ['label' => $video['language'], 'src' => $video['video_url']];
                }

            } else {
                $error = "Episode not found.";
            }
        } else {
            $error = "No video selected.";
        }

        // --- FETCH WATCH HISTORY (if user is logged in) ---
        if ($user_id && ($movie_id || $episode_id)) {
            $stmt_history = null;
            if ($episode_id) {
                $stmt_history = $pdo->prepare("SELECT progress_seconds FROM watch_history WHERE user_id = ? AND episode_id = ?");
                $stmt_history->execute([$user_id, $episode_id]);
            } else {
                $stmt_history = $pdo->prepare("SELECT progress_seconds FROM watch_history WHERE user_id = ? AND movie_id = ? AND episode_id IS NULL");
                $stmt_history->execute([$user_id, $movie_id]);
            }
            
            if ($stmt_history) {
                $history = $stmt_history->fetch();
                if ($history && $history['progress_seconds']) {
                    $start_at_seconds = (int)$history['progress_seconds'];
                }
            }
        }

    } catch (PDOException $e) {
        error_log($e->getMessage());
        $error = "A database error occurred.";
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($video_title); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #000; }
        .video-controls {
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
            background: linear-gradient(to top, rgba(0,0,0,0.8), rgba(0,0,0,0.4), transparent);
        }
        .video-container:hover .video-controls { opacity: 1; }
        .video-container.paused .video-controls { opacity: 1; }
        /* Style for custom range input */
        input[type=range] { -webkit-appearance: none; width: 100%; background: transparent; }
        input[type=range]:focus { outline: none; }
        input[type=range]::-webkit-slider-runnable-track {
            width: 100%; height: 5px; cursor: pointer; background: rgba(255, 255, 255, 0.3); border-radius: 3px;
        }
        input[type=range]::-webkit-slider-thumb {
            -webkit-appearance: none; height: 16px; width: 16px; border-radius: 50%; background: #ef4444; /* red-500 */
            cursor: pointer; margin-top: -5.5px;
        }
        input[type=range]:focus::-webkit-slider-runnable-track { background: rgba(255, 255, 255, 0.5); }
        /* Firefox */
        input[type=range]::-moz-range-track {
            width: 100%; height: 5px; cursor: pointer; background: rgba(255, 255, 255, 0.3); border-radius: 3px;
        }
        input[type=range]::-moz-range-thumb {
            height: 16px; width: 16px; border-radius: 50%; background: #ef4444; cursor: pointer; border: none;
        }
        /* Settings menu */
        .settings-menu {
            display: none;
            position: absolute;
            bottom: 60px; /* Adjust based on control bar height */
            right: 10px;
            background-color: rgba(0, 0, 0, 0.85);
            border-radius: 8px;
            width: 200px;
            padding: 10px;
            z-index: 50;
        }
        .settings-menu.active { display: block; }
        .settings-menu-item {
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 4px;
        }
        .settings-menu-item:hover { background-color: rgba(255, 255, 255, 0.2); }
    </style>
</head>
<body class="antialiased text-white">

    <div id="player-container" class="w-full h-screen max-w-lg mx-auto flex items-center justify-center">

        <?php if ($error): ?>
            <div class="text-center text-red-400 p-4">
                <p><?php echo htmlspecialchars($error); ?></p>
                <a href="index.php" class="text-red-500 hover:text-red-400 mt-2 block">&larr; Back to Home</a>
            </div>

        <?php elseif ($video_url): ?>
            <!-- Video Container -->
            <div id="video-container" class="video-container w-full h-full relative group bg-black">
                <video id="video-player" class="w-full h-full" poster="<?php echo htmlspecialchars($poster_url); ?>" preload="metadata">
                    <!-- The src is set by JavaScript to prevent autoplay issues -->
                    
                    <!-- Subtitle Tracks -->
                    <?php foreach ($subtitles as $sub): ?>
                        <track kind="subtitles" label="<?php echo htmlspecialchars($sub['label']); ?>" src="<?php echo htmlspecialchars($sub['src']); ?>" srclang="<?php echo htmlspecialchars($sub['srclang']); ?>">
                    <?php endforeach; ?>
                </video>
                
                <!-- Back Button -->
                <a href="index.php" class="absolute top-4 left-4 z-40 text-white bg-black bg-opacity-50 rounded-full p-2 hover:bg-opacity-75 transition-colors duration-200 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                
                <!-- Center Play/Pause Button (for click-to-play) -->
                <div id="video-click-overlay" class="absolute inset-0 z-20 flex items-center justify-center">
                    <button id="center-play-pause" class="w-16 h-16 bg-black bg-opacity-50 rounded-full flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition-opacity">
                        <!-- Play Icon -->
                        <svg id="center-play-icon" xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                        </svg>
                        <!-- Pause Icon -->
                        <svg id="center-pause-icon" xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 hidden" viewBox="0 0 20 20" fill="currentColor">
                             <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <!-- Custom Controls -->
                <div class="video-controls absolute bottom-0 left-0 right-0 w-full p-4 z-30">
                    <!-- Timeline / Seek Bar -->
                    <input id="seek-bar" type="range" min="0" value="0" step="1" class="w-full mb-2 cursor-pointer">

                    <div class="flex justify-between items-center">
                        <!-- Left Controls -->
                        <div class="flex items-center space-x-3">
                            <button id="play-pause-btn">
                                <!-- Play Icon -->
                                <svg id="play-icon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                                </svg>
                                <!-- Pause Icon -->
                                <svg id="pause-icon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 hidden" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <span id="time-display" class="text-sm font-medium">00:00 / 00:00</span>
                        </div>
                        
                        <!-- Right Controls (Settings) -->
                        <div class="flex items-center space-x-3">
                            <button id="settings-btn" class="relative">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106A1.532 1.532 0 0111.49 3.17zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                                </svg>
                            </button>

                            <!-- Settings Menu Pop-up -->
                            <div id="settings-menu" class="settings-menu">
                                <!-- Subtitles Menu -->
                                <div id="subtitles-menu-item" class="settings-menu-item hidden">Subtitles</div>
                                <div id="subtitles-options" class="hidden pl-4">
                                    <div class="settings-menu-item" data-track="off">Off</div>
                                    <?php foreach ($subtitles as $i => $sub): ?>
                                        <div class="settings-menu-item" data-track="<?php echo $i; ?>"><?php echo htmlspecialchars($sub['label']); ?></div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <!-- Audio Menu -->
                                <div id="audio-menu-item" class="settings-menu-item hidden">Audio</div>
                                <div id="audio-options" class="hidden pl-4">
                                     <?php foreach ($audio_tracks as $i => $track): ?>
                                        <div class="settings-menu-item" data-track="<?php echo $i; ?>"><?php echo htmlspecialchars($track['label']); ?></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center text-gray-400 p-4">
                <p>An unknown error occurred. The video cannot be loaded.</p>
                <a href="index.php" class="text-red-500 hover:text-red-400 mt-2 block">&larr; Back to Home</a>
            </div>
        <?php endif; ?>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const videoContainer = document.getElementById('video-container');
            const video = document.getElementById('video-player');
            const playPauseBtn = document.getElementById('play-pause-btn');
            const playIcon = document.getElementById('play-icon');
            const pauseIcon = document.getElementById('pause-icon');
            const centerPlayPauseBtn = document.getElementById('center-play-pause');
            const centerPlayIcon = document.getElementById('center-play-icon');
            const centerPauseIcon = document.getElementById('center-pause-icon');
            const videoClickOverlay = document.getElementById('video-click-overlay');
            const seekBar = document.getElementById('seek-bar');
            const timeDisplay = document.getElementById('time-display');
            const settingsBtn = document.getElementById('settings-btn');
            const settingsMenu = document.getElementById('settings-menu');
            const subtitlesMenuItem = document.getElementById('subtitles-menu-item');
            const subtitlesOptions = document.getElementById('subtitles-options');
            const audioMenuItem = document.getElementById('audio-menu-item');
            const audioOptions = document.getElementById('audio-options');

            // --- Data from PHP ---
            const videoSrc = <?php echo json_encode($video_url); ?>;
            const startAt = <?php echo (int)$start_at_seconds; ?>;
            const isLoggedIn = <?php echo json_encode($user_id !== null); ?>;
            const contentMovieId = <?php echo json_encode($movie_id); ?>;
            const contentEpisodeId = <?php echo json_encode($episode_id); ?>;
            const hasSubtitles = <?php echo json_encode(!empty($subtitles)); ?>;
            const hasAudioTracks = <?php echo json_encode(!empty($audio_tracks)); ?>;
            // ---------------------

            if (!video) return; // Exit if no video element

            // Set the video source
            video.src = videoSrc;

            let isPlaying = false;
            let historyUpdateInterval = null;

            // --- Player Functions ---
            function formatTime(seconds) {
                const h = Math.floor(seconds / 3600);
                const m = Math.floor((seconds % 3600) / 60);
                const s = Math.floor(seconds % 60);
                const m_str = m.toString().padStart(2, '0');
                const s_str = s.toString().padStart(2, '0');
                if (h > 0) {
                    return `${h}:${m_str}:${s_str}`;
                }
                return `${m_str}:${s_str}`;
            }

            function togglePlay() {
                if (video.paused || video.ended) {
                    video.play();
                } else {
                    video.pause();
                }
            }

            function updatePlayButton() {
                if (video.paused) {
                    playIcon.classList.remove('hidden');
                    pauseIcon.classList.add('hidden');
                    centerPlayIcon.classList.remove('hidden');
                    centerPauseIcon.classList.add('hidden');
                    centerPlayPauseBtn.classList.remove('hidden');
                    videoContainer.classList.add('paused');
                    isPlaying = false;
                    stopHistoryUpdates();
                } else {
                    playIcon.classList.add('hidden');
                    pauseIcon.classList.remove('hidden');
                    centerPlayIcon.classList.add('hidden');
                    centerPauseIcon.classList.remove('hidden');
                    centerPlayPauseBtn.classList.add('hidden');
                    videoContainer.classList.remove('paused');
                    isPlaying = true;
                    startHistoryUpdates();
                }
            }

            function updateTimeAndSeek() {
                const currentTime = video.currentTime;
                const duration = video.duration;
                if (!isNaN(duration)) {
                    timeDisplay.textContent = `${formatTime(currentTime)} / ${formatTime(duration)}`;
                    seekBar.value = (currentTime / duration) * 100;
                }
            }

            // --- History Functions (Only for logged-in users) ---
            function startHistoryUpdates() {
                if (!isLoggedIn || historyUpdateInterval) return;
                
                historyUpdateInterval = setInterval(() => {
                    sendHistoryUpdate();
                }, 5000); // Send update every 5 seconds
            }

            function stopHistoryUpdates() {
                if (historyUpdateInterval) {
                    clearInterval(historyUpdateInterval);
                    historyUpdateInterval = null;
                }
            }

            function sendHistoryUpdate() {
                if (!isLoggedIn || video.paused || video.ended) return;

                const payload = {
                    movie_id: contentMovieId,
                    episode_id: contentEpisodeId,
                    currentTime: video.currentTime,
                    totalDuration: video.duration
                };

                navigator.sendBeacon('update_history.php', JSON.stringify(payload));
            }

            // --- Event Listeners ---
            playPauseBtn.addEventListener('click', togglePlay);
            videoClickOverlay.addEventListener('click', togglePlay);
            centerPlayPauseBtn.addEventListener('click', togglePlay);

            video.addEventListener('play', updatePlayButton);
            video.addEventListener('pause', updatePlayButton);
            
            video.addEventListener('loadedmetadata', () => {
                updateTimeAndSeek();
                // Jump to saved time
                if (startAt > 0 && startAt < video.duration - 10) { // Don't start at the very end
                    video.currentTime = s