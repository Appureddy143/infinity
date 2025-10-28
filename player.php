<?php
    session_start();
    $isLoggedIn = isset($_SESSION['user_id']);
    $userId = $_SESSION['user_id'] ?? null;

    // --- DATABASE CONNECTION (CONCEPT) ---
    // $dsn = "pgsql:host=...;port=...;dbname=...;user=...;password=...";
    // $pdo = new PDO($dsn);

    // --- DATA FETCHING ---
    $contentId = null;
    $contentTitle = "Video Title";
    $videoUrl = "https://placehold.co/1920x1080.mp4"; // Default placeholder
    $watchTime = 0; // Default start time

    // Check if it's a movie ID
    if (isset($_GET['id'])) {
        $contentId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        // $sql = "SELECT id, title, video_url FROM movies WHERE id = ? AND type = 'movie'";
        // $stmt = $pdo->prepare($sql);
        // $stmt->execute([$contentId]);
        // $content = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Placeholder
        $content = ['id' => $contentId, 'title' => 'Fetched Movie Title', 'video_url' => 'https://www.w3schools.com/html/mov_bbb.mp4'];

    // Check if it's an episode ID
    } elseif (isset($_GET['episode'])) {
        $contentId = filter_input(INPUT_GET, 'episode', FILTER_VALIDATE_INT);
        // $sql = "SELECT e.id, e.title, e.video_url, s.movie_id 
        //         FROM episodes e 
        //         JOIN seasons s ON e.season_id = s.id
        //         WHERE e.id = ?";
        // $stmt = $pdo->prepare($sql);
        // $stmt->execute([$contentId]);
        // $content = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Placeholder
        $content = ['id' => $contentId, 'title' => 'Fetched Episode Title', 'video_url' => 'https://www.w3schools.com/html/mov_bbb.mp4'];
    }

    if ($contentId && $content) {
        $contentTitle = $content['title'];
        $videoUrl = $content['video_url'];

        // If user is logged in, get their watch history
        if ($isLoggedIn) {
            // $sql_history = "SELECT watch_time FROM watch_history WHERE user_id = ? AND content_id = ?";
            // $stmt_history = $pdo->prepare($sql_history);
            // $stmt_history->execute([$userId, $contentId]);
            // $history = $stmt_history->fetch(PDO::FETCH_ASSOC);
            
            // if ($history) {
            //     $watchTime = $history['watch_time'];
            // }

            // Placeholder
            if ($contentId == 1) $watchTime = 30; // Start 30s in for movie ID 1
        }
    } else {
        // If no valid ID, just stop
        die("Content not found.");
    }
?>
<!DOCTYPE html>
<html lang="en" class="bg-black">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Playing: <?php echo htmlspecialchars($contentTitle); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Base styles */
        body, html { overflow: hidden; height: 100%; }
        video::-webkit-media-controls { display: none !important; }
        video::-webkit-media-controls-enclosure { display: none !important; }
        video::-webkit-media-controls-panel { display: none !important; }
        
        /* Custom controls container */
        .video-controls {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
            padding: 10px 15px 25px 15px; /* Extra padding for safe area */
        }
        .video-container:hover .video-controls,
        .video-container.controls-visible .video-controls {
            opacity: 1;
        }

        /* Seek bar custom styles */
        input[type=range] {
            -webkit-appearance: none;
            width: 100%;
            height: 5px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 5px;
            outline: none;
            cursor: pointer;
            transition: background 0.3s;
        }
        input[type=range]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 16px;
            height: 16px;
            background: #E50914;
            border-radius: 50%;
            cursor: pointer;
        }
        input[type=range]::-moz-range-thumb {
            width: 16px;
            height: 16px;
            background: #E50914;
            border-radius: 50%;
            cursor: pointer;
        }
        
        /* Custom menus for settings, subs, audio */
        .controls-menu {
            position: absolute;
            bottom: 90px; /* Position above control bar */
            right: 15px;
            background-color: rgba(0, 0, 0, 0.9);
            border-radius: 8px;
            padding: 10px;
            display: none; /* Hidden by default */
            width: 200px;
        }
        .controls-menu-item {
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 4px;
        }
        .controls-menu-item:hover, .controls-menu-item.active {
            background-color: rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="text-white">

    <div id="video-container" class="w-full h-full relative flex justify-center items-center bg-black">
        
        <!-- The Video Player -->
        <video id="video-player" class="w-full h-full" preload="metadata">
            <!-- Source is now set by PHP -->
            <source src="<?php echo htmlspecialchars($videoUrl); ?>" type="video/mp4">
            
            <!-- Example subtitle/audio tracks. These must be part of your video file or provided as separate files -->
            <track kind="subtitles" label="English" srclang="en" src="path/to/english.vtt">
            <track kind="subtitles" label="Kannada" srclang="kn" src="path/to/kannada.vtt">
            <track kind.="audio" label="English" srclang="en">
            <track kind="audio" label="Kannada" srclang="kn">
        </video>

        <!-- Loading Spinner -->
        <div id="loading-spinner" class="absolute z-10 hidden">
            <svg class="animate-spin h-12 w-12 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
        
        <!-- Back Button (Top Left) -->
        <a href="javascript:history.back()" class="absolute top-4 left-4 p-2 bg-black/50 rounded-full z-20">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>

        <!-- Center Play/Pause Button -->
        <button id="center-play-pause" class="absolute z-10 p-4 bg-black/50 rounded-full hidden">
            <!-- Play Icon -->
            <svg id="center-play-icon" xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
            </svg>
            <!-- Pause Icon (hidden by default) -->
            <svg id="center-pause-icon" xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 hidden" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd" />
            </svg>
        </button>


        <!-- Custom Controls -->
        <div class="video-controls z-20">
            <!-- Seek Bar -->
            <input id="seek-bar" type="range" value="0" min="0" max="100" class="w-full mb-2">
            
            <div class="flex justify-between items-center">
                <!-- Left Controls: Play/Pause, Time -->
                <div class="flex items-center space-x-4">
                    <button id="play-pause-btn">
                        <!-- Play Icon -->
                        <svg id="play-icon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                        </svg>
                        <!-- Pause Icon (hidden) -->
                        <svg id="pause-icon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 hidden" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <span id="time-display" class="text-sm font-mono">00:00 / 00:00</span>
                </div>
                
                <!-- Right Controls: Subs, Audio, Fullscreen -->
                <div class="flex items-center space-x-4">
                    <button id="subs-btn" class="hidden"> <!-- Hidden until we detect tracks -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                          <path stroke-linecap="round" stroke-linejoin="round" d="M16 12a4 4 0 11-8 0 4 4 0 018 0zm-2 0a2 2 0 11-4 0 2 2 0 014 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 4.5l15 15" />
                        </svg>
                    </button>
                    <button id="audio-btn" class="hidden"> <!-- Hidden until we detect tracks -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .89-1.077 1.337-1.707.707L5.586 15z" />
                        </svg>
                    </button>
                    <button id="fullscreen-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v4m0 0h-4m4 0l-5-5" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Subtitles Menu -->
        <div id="subs-menu" class="controls-menu z-30">
            <div id="subs-menu-items">
                <!-- Items will be populated by JS -->
            </div>
        </div>

        <!-- Audio Menu -->
        <div id="audio-menu" class="controls-menu z-30">
            <div id="audio-menu-items">
                <!-- Items will be populated by JS -->
            </div>
        </div>

    </div>

    <script>
        // --- PHP Data Passed to JavaScript ---
        const config = {
            isLoggedIn: <?php echo json_encode($isLoggedIn); ?>,
            contentId: <?php echo json_encode($contentId); ?>,
            startWatchTime: <?php echo json_encode($watchTime); ?>
        };
        // -------------------------------------

        const videoContainer = document.getElementById('video-container');
        const video = document.getElementById('video-player');
        
        // Control Buttons
        const playPauseBtn = document.getElementById('play-pause-btn');
        const playIcon = document.getElementById('play-icon');
        const pauseIcon = document.getElementById('pause-icon');
        const centerPlayPauseBtn = document.getElementById('center-play-pause');
        const centerPlayIcon = document.getElementById('center-play-icon');
        const centerPauseIcon = document.getElementById('center-pause-icon');
        const fullscreenBtn = document.getElementById('fullscreen-btn');
        
        // Seek & Time
        const seekBar = document.getElementById('seek-bar');
        const timeDisplay = document.getElementById('time-display');
        const loadingSpinner = document.getElementById('loading-spinner');

        // Menus
        const subsBtn = document.getElementById('subs-btn');
        const audioBtn = document.getElementById('audio-btn');
        const subsMenu = document.getElementById('subs-menu');
        const audioMenu = document.getElementById('audio-menu');
        const subsMenuItems = document.getElementById('subs-menu-items');
        const audioMenuItems = document.getElementById('audio-menu-items');

        let controlsTimeout;
        let lastWatchTimeUpdate = 0;

        // --- Core Functions ---
        
        function togglePlayPause() {
            if (video.paused) {
                video.play();
            } else {
                video.pause();
            }
        }

        function updatePlayPauseIcons() {
            if (video.paused) {
                playIcon.classList.remove('hidden');
                pauseIcon.classList.add('hidden');
                centerPlayIcon.classList.remove('hidden');
                centerPauseIcon.classList.add('hidden');
                centerPlayPauseBtn.classList.remove('hidden');
            } else {
                playIcon.classList.add('hidden');
                pauseIcon.classList.remove('hidden');
                centerPlayIcon.classList.add('hidden');
                centerPauseIcon.classList.remove('hidden');
                centerPlayPauseBtn.classList.add('hidden');
            }
        }

        function formatTime(seconds) {
            const m = Math.floor(seconds / 60);
            const s = Math.floor(seconds % 60);
            return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
        }
        
        function updateTimeDisplay() {
            const currentTime = formatTime(video.currentTime);
            const duration = formatTime(video.duration || 0);
            timeDisplay.textContent = `${currentTime} / ${duration}`;
            
            if (video.duration) {
                seekBar.value = (video.currentTime / video.duration) * 100;
            }
        }

        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                videoContainer.requestFullscreen().catch(err => {
                    console.error(`Error attempting to enable full-screen mode: ${err.message}`);
                });
            } else {
                document.exitFullscreen();
            }
        }

        function hideControls() {
            if (!video.paused) {
                videoContainer.classList.remove('controls-visible');
                subsMenu.style.display = 'none';
                audioMenu.style.display = 'none';
            }
        }

        function showControls() {
            videoContainer.classList.add('controls-visible');
            clearTimeout(controlsTimeout);
            controlsTimeout = setTimeout(hideControls, 3000);
        }

        // --- Event Listeners ---

        video.addEventListener('play', updatePlayPauseIcons);
        video.addEventListener('pause', updatePlayPauseIcons);
        video.addEventListener('loadedmetadata', () => {
            updateTimeDisplay();
            // Start video at the fetched watch time
            if(config.startWatchTime > 0) {
                video.currentTime = config.startWatchTime;
            }
            populateTrackMenus();
        });
        video.addEventListener('timeupdate', () => {
            updateTimeDisplay();
            // --- Send history update every 15 seconds ---
            const now = Date.now();
            if (config.isLoggedIn && (now - lastWatchTimeUpdate > 15000)) {
                lastWatchTimeUpdate = now;
                updateWatchHistory(video.currentTime);
            }
        });
        
        // Send final history update on unload
        window.addEventListener('beforeunload', () => {
            if (config.isLoggedIn && video.currentTime > 0) {
                updateWatchHistory(video.currentTime, true); // Send synchronously
            }
        });

        // Loading states
        video.addEventListener('waiting', () => loadingSpinner.classList.remove('hidden'));
        video.addEventListener('playing', () => loadingSpinner.classList.add('hidden'));

        // Controls visibility
        videoContainer.addEventListener('mousemove', showControls);
        videoContainer.addEventListener('click', (e) => {
            // Only toggle play/pause if click is on video, not controls
            if (e.target === videoContainer || e.target === video || e.target === centerPlayPauseBtn || centerPlayPauseBtn.contains(e.target)) {
                togglePlayPause();
            }
            showControls();
        });
        
        // Control buttons
        playPauseBtn.addEventListener('click', togglePlayPause);
        centerPlayPauseBtn.addEventListener('click', togglePlayPause);
        fullscreenBtn.addEventListener('click', toggleFullscreen);

        // Seek bar
        seekBar.addEventListener('input', (e) => {
            if (video.duration) {
                video.currentTime = (e.target.value / 100) * video.duration;
            }
        });
        
        // --- Subtitle & Audio Track Logic ---

        function populateTrackMenus() {
            const textTracks = video.textTracks;
            const audioTracks = video.audioTracks;

            // Clear previous items
            subsMenuItems.innerHTML = '';
            audioMenuItems.innerHTML = '';

            // Populate Subtitles
            if (textTracks && textTracks.length > 0) {
                subsBtn.classList.remove('hidden');
                let foundActive = false;
                
                // Add "Off" button
                const offItem = document.createElement('div');
                offItem.textContent = 'Off';
                offItem.className = 'controls-menu-item';
                offItem.onclick = () => setSubtitleTrack(null);
                subsMenuItems.appendChild(offItem);

                for (let i = 0; i < textTracks.length; i++) {
                    const track = textTracks[i];
                    if (track.kind === 'subtitles' || track.kind === 'captions') {
                        const item = document.createElement('div');
                        item.textContent = track.label;
                        item.className = 'controls-menu-item';
                        item.onclick = () => setSubtitleTrack(track.language);
                        
                        if (track.mode === 'showing') {
                            item.classList.add('active');
                            foundActive = true;
                        }
                        subsMenuItems.appendChild(item);
                    }
                }
                
                if (!foundActive) {
                    offItem.classList.add('active');
                }
            }

            // Populate Audio
            if (audioTracks && audioTracks.length > 1) { // Only show if more than one track
                audioBtn.classList.remove('hidden');
                for (let i = 0; i < audioTracks.length; i++) {
                    const track = audioTracks[i];
                    const item = document.createElement('div');
                    item.textContent = track.label || track.language;
                    item.className = 'controls-menu-item';
                    item.onclick = () => setAudioTrack(track.id);
                    
                    if (track.enabled) {
                        item.classList.add('active');
                    }
                    audioMenuItems.appendChild(item);
                }
            }
        }

        function setSubtitleTrack(lang) {
            for (let i = 0; i < video.textTracks.length; i++) {
                const track = video.textTracks[i];
                if (track.kind === 'subtitles' || track.kind === 'captions') {
                    track.mode = (track.language === lang) ? 'showing' : 'disabled';
                }
            }
            populateTrackMenus(); // Re-populate to show active state
            subsMenu.style.display = 'none'; // Hide menu
        }

        function setAudioTrack(id) {
            for (let i = 0; i < video.audioTracks.length; i++) {
                video.audioTracks[i].enabled = (video.audioTracks[i].id === id);
            }
            populateTrackMenus(); // Re-populate
            audioMenu.style.display = 'none'; // Hide menu
        }
        
        subsBtn.addEventListener('click', () => {
            audioMenu.style.display = 'none'; // Hide other menu
            subsMenu.style.display = (subsMenu.style.display === 'block') ? 'none' : 'block';
            showControls(); // Keep controls visible
        });
        
        audioBtn.addEventListener('click', () => {
            subsMenu.style.display = 'none'; // Hide other menu
            audioMenu.style.display = (audioMenu.style.display === 'block') ? 'none' : 'block';
            showControls(); // Keep controls visible
        });
        
        // --- Watch History API Call ---
        
        function updateWatchHistory(time, sync = false) {
            if (!config.isLoggedIn || !config.contentId) return;

            const formData = new FormData();
            formData.append('content_id', config.contentId);
            formData.append('watch_time', Math.floor(time));

            if (sync && navigator.sendBeacon) {
                // Use sendBeacon for synchronous requests on page unload
                // Note: sendBeacon sends POST data as blob, so PHP side needs adjustment
                // For simplicity, we'll stick to async fetch, but beacon is more robust
                // For now, we'll just use a standard fetch
                 fetch('update_history.php', { method: 'POST', body: formData, keepalive: true });
            } else if (!sync) {
                // Regular async update
                fetch('update_history.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        // console.log('History updated');
                    } else {
                        // console.error('Failed to update history:', data.error);
                    }
                })
                .catch(error => {
                    // console.error('Error updating history:', error);
                });
            }
        }

    </script>

</body>
</html>

