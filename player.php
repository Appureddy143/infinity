<!DOCTYPE html>
<html lang="en" class="bg-black">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Now Playing - MyStream</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Base styles from your original player */
        input[type="range"] {
            -webkit-appearance: none;
            appearance: none;
            background: transparent;
            cursor: pointer;
            width: 100%;
        }
        input[type="range"]::-webkit-slider-runnable-track {
            background: rgba(255, 255, 255, 0.3);
            height: 0.25rem;
            border-radius: 0.25rem;
        }
        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            margin-top: -5px;
            background-color: #fff;
            height: 0.75rem;
            width: 0.75rem;
            border-radius: 50%;
        }
        input[type="range"]::-moz-range-track {
            background: rgba(255, 255, 255, 0.3);
            height: 0.25rem;
            border-radius: 0.25rem;
        }
        input[type="range"]::-moz-range-thumb {
            background-color: #fff;
            height: 0.75rem;
            width: 0.75rem;
            border-radius: 50%;
            border: none;
        }
        #video-container:hover #controls-overlay,
        #video-container.paused #controls-overlay {
            opacity: 1;
        }
        #controls-overlay {
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
            background-image: linear-gradient(to top, rgba(0,0,0,0.7), transparent 30%), linear-gradient(to bottom, rgba(0,0,0,0.7), transparent 30%);
        }
        /* Style for the active track in the menus */
        .track-option.active {
            font-weight: bold;
            background-color: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="font-sans">

    <?php
        // --- DATABASE LOGIC (CONCEPT) ---
        session_start();

        // 1. Connect to your Neon (PostgreSQL) database
        //    $pdo = new PDO($dsn, ...);

        // Check if user is logged in
        $isLoggedIn = isset($_SESSION['user_id']);
        $userId = $_SESSION['user_id'] ?? null;

        // 2. Get the Movie or Episode ID from URL
        $movieId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $episodeId = filter_input(INPUT_GET, 'episode', FILTER_VALIDATE_INT);
        
        $videoUrl = '';
        $title = 'Video Not Found';
        $contentId = null;
        $startTime = 0; // Default start time is 0

        if ($movieId) {
            // --- It's a Movie ---
            // 3a. Fetch movie video URL from `movies` table
            //     $stmt = $pdo->prepare("SELECT title, video_url FROM movies WHERE id = ?");
            //     $stmt->execute([$movieId]);
            //     $movie = $stmt->fetch();
            //     if ($movie) {
            //         $videoUrl = $movie['video_url'];
            //         $title = $movie['title'];
            //         $contentId = $movieId; // Used for history
            //     }
            
            // Placeholder:
            // This example video has multiple audio tracks and subtitles
            $videoUrl = 'https://cdn.bitmovin.com/content/assets/sintel/sintel.mpd';
            $title = 'Placeholder Movie (Sintel)';
            $contentId = $movieId;

        } elseif ($episodeId) {
            // --- It's a Series Episode ---
            // 3b. Fetch episode video URL from `episodes` table
            //     $stmt = $pdo->prepare("SELECT title, video_url FROM episodes WHERE id = ?");
            //     $stmt->execute([$episodeId]);
            //     $episode = $stmt->fetch();
            //     if ($episode) {
            //         $videoUrl = $episode['video_url'];
            //         $title = $episode['title'];
            //         $contentId = $episodeId; // Use episode ID for history
            //     }

            // Placeholder:
            $videoUrl = 'https://test-videos.co.uk/vids/sintel/mp4/480/Sintel_480_10s_1MB.mp4';
            $title = 'Placeholder Episode';
            $contentId = $episodeId;
        }

        // 4. If logged in, get the watch history
        if ($isLoggedIn && $contentId) {
            //    $stmt = $pdo->prepare("SELECT watch_time FROM watch_history WHERE user_id = ? AND content_id = ?");
            //    $stmt->execute([$userId, $contentId]);
            //    $history = $stmt->fetch();
            //    if ($history) {
            //        $startTime = $history['watch_time'];
            //    }
            
            // Placeholder:
            if ($contentId == 1) $startTime = 3; // Start 3s in for placeholder movie
        }
    ?>

    <!-- This is the player modal, now as a full page -->
    <div id="player-page" class="fixed inset-0 bg-black z-50 flex items-center justify-center">
        <!-- Video Container -->
        <div id="video-container" class="relative w-full h-full bg-black">
            
            <!-- The actual video element -->
            <video id="video-player" class="w-full h-full" src="<?php echo htmlspecialchars($videoUrl); ?>" playsinline crossorigin="anonymous">
                <!-- This 'src' is loaded by PHP -->
                <!-- Example of hard-coded tracks for demo. Real videos (like .m3u8 or .mpd) will have these built-in -->
                <track kind="subtitles" label="English" srclang="en" src="path/to/english.vtt">
                <track kind="subtitles" label="Español" srclang="es" src="path/to/spanish.vtt">
            </video>

            <!-- Custom Controls Overlay -->
            <div id="controls-overlay" class="absolute inset-0 flex flex-col justify-between p-4 text-white">
                
                <!-- Top Controls (Back Button & Title) -->
                <div class="flex justify-between items-center">
                    <button id="back-button" class="p-2 rounded-full hover:bg-white/20">
                        <!-- Back Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <h2 class="text-lg font-semibold"><?php echo htmlspecialchars($title); ?></h2>
                    <div class="w-10"></div> <!-- Spacer -->
                </div>

                <!-- Middle Controls (Big Play/Pause) - Toggled by JS -->
                <div class="flex-grow flex items-center justify-center">
                    <button id="center-play-pause" class="p-4 rounded-full bg-black/50 hover:bg-black/75">
                         <!-- Play Icon -->
                        <svg id="center-play-icon" xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                        </svg>
                        <!-- Pause Icon (hidden by default) -->
                        <svg id="center-pause-icon" xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 hidden" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 00-1 1v2a1 1 0 002 0V9a1 1 0 00-1-1zm6 0a1 1 0 00-1 1v2a1 1 0 002 0V9a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <!-- Pop-up Menus -->
                <div class="absolute bottom-16 right-4 space-y-2 z-20">
                    <!-- Subtitles Menu -->
                    <div id="subtitles-menu" class="hidden bg-black/75 rounded-lg p-2 space-y-1 max-h-48 overflow-y-auto">
                        <!-- Populated by JS -->
                    </div>
                    
                    <!-- Audio Menu -->
                    <div id="audio-menu" class="hidden bg-black/75 rounded-lg p-2 space-y-1 max-h-48 overflow-y-auto">
                        <!-- Populated by JS -->
                    </div>
                </div>

                <!-- Bottom Controls Bar -->
                <div class="space-y-2 z-10">
                    <!-- Seek Bar -->
                    <div class="flex items-center space-x-2">
                        <span id="current-time" class="text-xs w-10 text-center">0:00</span>
                        <input id="seek-bar" type="range" value="0" min="0" max="100" class="flex-grow">
                        <span id="duration" class="text-xs w-10 text-center">0:00</span>
                    </div>
                    <!-- Main Controls -->
                    <div class="flex justify-between items-center">
                        <button id="play-pause" class="p-2">
                            <!-- Play Icon -->
                            <svg id="play-icon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                            </svg>
                            <!-- Pause Icon (hidden by default) -->
                            <svg id="pause-icon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 hidden" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 00-1 1v2a1 1 0 002 0V9a1 1 0 00-1-1zm6 0a1 1 0 00-1 1v2a1 1 0 002 0V9a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        
                        <div class="flex items-center space-x-2">
                            <!-- Subtitles Button -->
                            <button id="subtitles-button" class="p-2 hidden"> <!-- Hidden by default, shown by JS -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0zM10 9H8v6h2V9zm6 0h-2v6h2V9z" />
                                </svg>
                            </button>
                            
                            <!-- Audio/Language Button -->
                            <button id="audio-button" class="p-2 hidden"> <!-- Hidden by default, shown by JS -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                            </button>
                            
                            <button id="volume-button" class="p-2">
                                <svg id="volume-high" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM14.657 2.929a1 1 0 011.414 0A9 9 0 0119 10a9 9 0 01-2.929 7.071 1 1 0 01-1.414-1.414A7 7 0 0017 10a7 7 0 00-1.414-4.95 1 1 0 010-1.121zM16.07 4.343a1 1 0 011.414 0A5 5 0 0119 10a5 5 0 01-1.515 3.536 1 1 0 11-1.414-1.414A3 3 0 0017 10a3 3 0 00-.93-2.121 1 1 0 010-1.536z" clip-rule="evenodd" />
                                </svg>
                                <svg id="volume-muted" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 hidden" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM12.293 7.293a1 1 0 011.414 0L15 8.586l1.293-1.293a1 1 0 111.414 1.414L16.414 10l1.293 1.293a1 1 0 01-1.414 1.414L15 11.414l-1.293 1.293a1 1 0 01-1.414-1.414L13.586 10l-1.293-1.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <input id="volume-bar" type="range" value="100" min="0" max="100" class="w-20">
                            
                            <button id="fullscreen-button" class="p-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1v4m0 0h-4m4 0l-5-5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 0h-4m4 0l-5 5" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Need to load HLS/DASH player if using .m3u8 or .mpd files -->
    <!-- This is required for the example Sintel video to work -->
    <script src="https://cdn.dashjs.org/latest/dash.all.min.js"></script>

    <script>
        // --- Pass PHP variables to JavaScript ---
        const isLoggedIn = <?php echo json_encode($isLoggedIn); ?>;
        const contentId = <?php echo json_encode($contentId); ?>;
        const startTime = <?php echo json_encode($startTime); ?>;
        const videoSrc = <?php echo json_encode($videoUrl); ?>;
        // ----------------------------------------

        document.addEventListener('DOMContentLoaded', () => {
            const videoContainer = document.getElementById('video-container');
            const video = document.getElementById('video-player');
            const controlsOverlay = document.getElementById('controls-overlay');
            const backButton = document.getElementById('back-button');
            
            const playPauseBtn = document.getElementById('play-pause');
            const centerPlayPauseBtn = document.getElementById('center-play-pause');
            const playIcons = [document.getElementById('play-icon'), document.getElementById('center-play-icon')];
            const pauseIcons = [document.getElementById('pause-icon'), document.getElementById('center-pause-icon')];
            
            const seekBar = document.getElementById('seek-bar');
            const currentTimeEl = document.getElementById('current-time');
            const durationEl = document.getElementById('duration');
            
            const volumeBtn = document.getElementById('volume-button');
            const volumeHighIcon = document.getElementById('volume-high');
            const volumeMutedIcon = document.getElementById('volume-muted');
            const volumeBar = document.getElementById('volume-bar');

            const fullscreenBtn = document.getElementById('fullscreen-button');

            // --- Audio/Subtitle Elements ---
            const subtitlesButton = document.getElementById('subtitles-button');
            const audioButton = document.getElementById('audio-button');
            const subtitlesMenu = document.getElementById('subtitles-menu');
            const audioMenu = document.getElementById('audio-menu');

            // --- Player Logic ---

            // Handle HLS/DASH streams
            if (videoSrc.endsWith('.mpd')) {
                const player = dashjs.MediaPlayer().create();
                player.initialize(video, videoSrc, false); // false = don't autoplay
            } else if (videoSrc.endsWith('.m3u8')) {
                // You would need hls.js for HLS streams
                // <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
                // if (Hls.isSupported()) {
                //     const hls = new Hls();
                //     hls.loadSource(videoSrc);
                //     hls.attachMedia(video);
                // }
                alert('HLS streaming not yet supported in this player.');
            }
            
            // Go back to the previous page
            backButton.addEventListener('click', () => {
                history.back();
            });
            
            function togglePlay() {
                if (video.paused || video.ended) {
                    video.play();
                } else {
                    video.pause();
                }
            }
            
            function updatePlayPauseIcons() {
                const isPaused = video.paused;
                videoContainer.classList.toggle('paused', isPaused); // For controls overlay
                playIcons.forEach(icon => icon.classList.toggle('hidden', !isPaused));
                pauseIcons.forEach(icon => icon.classList.toggle('hidden', isPaused));
            }

            function formatTime(timeInSeconds) {
                if (isNaN(timeInSeconds)) return '0:00';
                const minutes = Math.floor(timeInSeconds / 60);
                const seconds = Math.floor(timeInSeconds % 60);
                return `${minutes}:${seconds.toString().padStart(2, '0')}`;
            }

            function updateTime() {
                if (isNaN(video.duration)) return;
                seekBar.value = (video.currentTime / video.duration) * 100;
                currentTimeEl.textContent = formatTime(video.currentTime);
            }

            function seek() {
                if (isNaN(video.duration)) return;
                video.currentTime = (seekBar.value / 100) * video.duration;
            }

            function onVideoLoaded() {
                if (isNaN(video.duration)) return;
                durationEl.textContent = formatTime(video.duration);
                
                if (startTime > 0 && startTime < video.duration - 5) {
                    video.currentTime = startTime;
                }
                
                video.play();
                
                // --- Setup Tracks ---
                // Wait a moment for tracks to be available, especially for DASH/HLS
                setTimeout(() => {
                    setupSubtitleTracks();
                    setupAudioTracks();
                }, 500);
            }

            function toggleMute() {
                video.muted = !video.muted;
            }

            function updateVolumeIcons() {
                volumeHighIcon.classList.toggle('hidden', video.muted || video.volume === 0);
                volumeMutedIcon.classList.toggle('hidden', !video.muted && video.volume > 0);
            }
            
            function setVolume() {
                video.volume = volumeBar.value / 100;
                video.muted = video.volume === 0;
            }
            
            function toggleFullscreen() {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen();
                } else {
                    document.exitFullscreen();
                }
            }

            // --- Subtitle/Audio Logic ---
            function hideTrackMenus() {
                subtitlesMenu.classList.add('hidden');
                audioMenu.classList.add('hidden');
            }

            function setupSubtitleTracks() {
                const tracks = video.textTracks;
                if (!tracks || tracks.length === 0) {
                    return; // No subtitle tracks
                }

                subtitlesButton.classList.remove('hidden');
                subtitlesMenu.innerHTML = ''; 

                const offButton = document.createElement('button');
                offButton.classList.add('track-option', 'text-white', 'w-full', 'text-left', 'p-2', 'rounded', 'hover:bg-white/20');
                offButton.innerText = 'Off';
                offButton.addEventListener('click', (e) => {
                    e.stopPropagation();
                    [...tracks].forEach(track => track.mode = 'hidden');
                    updateActiveTrackButton(subtitlesMenu, offButton);
                    hideTrackMenus();
                });
                subtitlesMenu.appendChild(offButton);
                
                let activeTrack = null;

                [...tracks].forEach((track, index) => {
                    track.mode = 'hidden'; // Set all to hidden by default
                    const trackButton = document.createElement('button');
                    trackButton.classList.add('track-option', 'text-white', 'w-full', 'text-left', 'p-2', 'rounded', 'hover:bg-white/20');
                    trackButton.innerText = track.label || track.language || `Track ${index + 1}`;
                    
                    trackButton.addEventListener('click', (e) => {
                        e.stopPropagation();
                        [...tracks].forEach(t => t.mode = 'hidden');
                        track.mode = 'showing';
                        updateActiveTrackButton(subtitlesMenu, trackButton);
                        hideTrackMenus();
                    });
                    
                    subtitlesMenu.appendChild(trackButton);

                    if (track.mode === 'showing') { // Check if one was showing by default
                        activeTrack = trackButton;
                    }
                });
                
                updateActiveTrackButton(subtitlesMenu, activeTrack || offButton);
            }

            function setupAudioTracks() {
                const tracks = video.audioTracks;
                if (!tracks || tracks.length <= 1) { // 0 or 1 track means no options
                    return; 
                }

                audioButton.classList.remove('hidden');
                audioMenu.innerHTML = '';
                
                let activeTrack = null;

                [...tracks].forEach((track, index) => {
                    const trackButton = document.createElement('button');
                    trackButton.classList.add('track-option', 'text-white', 'w-full', 'text-left', 'p-2', 'rounded', 'hover:bg-white/20');
                    trackButton.innerText = track.label || track.language || `Audio ${index + 1}`;
                    
                    trackButton.addEventListener('click', (e) => {
                        e.stopPropagation();
                        [...tracks].forEach(t => t.enabled = false);
                        track.enabled = true;
                        updateActiveTrackButton(audioMenu, trackButton);
                        hideTrackMenus();
                    });
                    
                    audioMenu.appendChild(trackButton);

                    if (track.enabled) {
                        activeTrack = trackButton;
                    }
                });

                if (activeTrack) {
                    updateActiveTrackButton(audioMenu, activeTrack);
                }
            }

            function updateActiveTrackButton(menu, activeButton) {
                menu.querySelectorAll('.track-option').forEach(btn => {
                    btn.classList.remove('active');
                });
                if(activeButton) {
                    activeButton.classList.add('active');
                }
            }
            
            // --- HISTORY TRACKING (CONCEPT) ---
            let lastUpdateTime = 0;
            
            function updateWatchHistory() {
                if (!isLoggedIn || !contentId) return; 

                const currentTime = Math.floor(video.currentTime);
                const now = Date.now();
                
                if (currentTime > 0 && !video.paused && (now - lastUpdateTime > 15000)) {
                    lastUpdateTime = now;
                    
                    const formData = new FormData();
                    formData.append('content_id', contentId);
                    formData.append('watch_time', currentTime);

                    fetch('update_history.php', {
                        method: 'POST',
                        body: formData
                    })
                    .catch(error => console.error('Error updating history:', error));
                }
            }

            // --- Event Listeners ---
            playPauseBtn.addEventListener('click', togglePlay);
            centerPlayPauseBtn.addEventListener('click', togglePlay);
            video.addEventListener('click', (e) => {
                if (e.target === video) { // Only toggle play if clicking video, not controls
                   togglePlay();
                   hideTrackMenus();
                }
            });
            
            video.addEventListener('play', updatePlayPauseIcons);
            video.addEventListener('pause', updatePlayPauseIcons);
            
            video.addEventListener('loadedmetadata', onVideoLoaded);
            video.addEventListener('canplay', onVideoLoaded); // Fallback for some browsers
            
            video.addEventListener('timeupdate', () => {
                updateTime();
                updateWatchHistory();
            });
            seekBar.addEventListener('input', seek);
            
            volumeBtn.addEventListener('click', toggleMute);
            video.addEventListener('volumechange', updateVolumeIcons);
            volumeBar.addEventListener('input', setVolume);

            fullscreenBtn.addEventListener('click', toggleFullscreen);

            // --- Track Menu Listeners ---
            subtitlesButton.addEventListener('click', (e) => {
                e.stopPropagation();
                audioMenu.classList.add('hidden');
V                subtitlesMenu.classList.toggle('hidden');
                // Re-check active track
                const activeTrack = [...video.textTracks].find(t => t.mode === 'showing');
                const buttons = subtitlesMenu.querySelectorAll('.track-option');
                if (activeTrack) {
                    const buttonToSelect = [...buttons].find(b => b.innerText.includes(activeTrack.label) || b.innerText.includes(activeTrack.language));
                    updateActiveTrackButton(subtitlesMenu, buttonToSelect || buttons[0]);
                } else {
                    updateActiveTrackButton(subtitlesMenu, buttons[0]); // "Off" button
                }
            });

            audioButton.addEventListener('click', (e) => {
                e.stopPropagation();
                subtitlesMenu.classList.add('hidden');
                audioMenu.classList.toggle('hidden');
                // Re-check active track
                const activeTrack = [...video.audioTracks].find(t => t.enabled);
                const buttons = audioMenu.querySelectorAll('.track-option');
                if (activeTrack) {
                    const buttonToSelect = [...buttons].find(b => b.innerText.includes(activeTrack.label) || b.innerText.includes(activeTrack.language));
                    updateActiveTrackButton(audioMenu, buttonToSelect);
                }
            });

            // Hide menus if clicking anywhere else on the overlay
            controlsOverlay.addEventListener('click', (e) => {
                if (e.target === controlsOverlay) {
                    hideTrackMenus();
                }
            });

            // Handle errors
            video.addEventListener('error', (e) => {
                console.error("Video playback error", e);
            });

        });
    </script>

</body>
</html>

