<!DOCTYPE html>
<html lang="en" class="bg-gray-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyStream - Mobile</title>
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom scrollbar for horizontal lists */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none; /* IE and Edge */
            scrollbar-width: none; /* Firefox */
        }
        
        /* Custom styles for the video player seek bar */
        input[type="range"] {
            -webkit-appearance: none;
            appearance: none;
            background: transparent;
            cursor: pointer;
            width: 100%;
        }

        /* Chrome, Safari, Opera, and Edge */
        input[type="range"]::-webkit-slider-runnable-track {
            background: rgba(255, 255, 255, 0.3);
            height: 0.25rem;
            border-radius: 0.25rem;
        }

        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            margin-top: -5px; /* Centers thumb on track */
            background-color: #fff;
            height: 0.75rem;
            width: 0.75rem;
            border-radius: 50%;
            border: none;
            transition: background-color 0.15s ease-in-out;
        }
        
        input[type="range"]:hover::-webkit-slider-thumb {
            background-color: #f0f0f0;
        }

        /* Firefox */
        input[type="range"]::-moz-range-track {
            background: rgba(255, 255, 255, 0.3);
            height: 0.25rem;
            border-radius: 0.25rem;
            border: none;
        }

        input[type="range"]::-moz-range-thumb {
            background-color: #fff;
            height: 0.75rem;
            width: 0.75rem;
            border-radius: 50%;
            border: none;
            transition: background-color 0.15s ease-in-out;
        }
        
        input[type="range"]:hover::-moz-range-thumb {
            background-color: #f0f0f0;
        }

        /* Hide controls by default, show on container hover */
        #video-container:hover #controls-overlay {
            opacity: 1;
        }
        #controls-overlay {
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }
        #video-container.playing #controls-overlay {
            /* Keep it hidden when playing unless hovered */
            opacity: 0;
        }
         #video-container.playing:hover #controls-overlay {
            opacity: 1;
        }
    </style>
</head>
<body class="font-sans">

    <!-- 
      Main mobile screen container.
      max-w-md restricts width on desktop to simulate a phone.
      mx-auto centers it.
    -->
    <div class="max-w-md mx-auto bg-gray-900 text-white min-h-screen">

        <!-- Header -->
        <header class="p-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-red-600">MyStream</h1>
            <div class="flex space-x-4">
                <!-- Search Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <!-- Profile Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
        </header>

        <!-- Main Content -->
        <main class="pb-16">
            <!-- Featured Movie -->
            <section class="relative h-96">
                <img src="https://placehold.co/600x800/1a1a1a/ffffff?text=Movie+Poster" 
                     alt="Featured Movie Poster" 
                     class="w-full h-full object-cover opacity-50">
                <div class="absolute bottom-0 left-0 p-6">
                    <h2 class="text-3xl font-bold">Movie Title Here</h2>
                    <p class="text-sm text-gray-300 mt-1">Action • Sci-Fi • 2h 15m</p>
                    <button id="play-featured" class="mt-4 bg-red-600 text-white font-bold py-2 px-6 rounded-lg flex items-center space-x-2 hover:bg-red-700 transition">
                        <!-- Play Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                        </svg>
                        <span>Play</span>
                    </button>
                </div>
            </section>

            <!-- Movie Lists -->
            <section class="mt-8 space-y-6">
                <!-- Category: New Releases -->
                <div>
                    <h3 class="text-xl font-semibold px-4 mb-3">New Releases</h3>
                    <div class="flex space-x-4 overflow-x-auto no-scrollbar px-4">
                        <!-- Movie Card -->
                        <div class="flex-shrink-0 w-32">
                            <img src="https://placehold.co/300x450/2a2a2a/ffffff?text=Movie+1" alt="Movie 1" class="rounded-lg w-full h-48 object-cover play-button-trigger">
                        </div>
                        <!-- Movie Card -->
                        <div class="flex-shrink-0 w-32">
                            <img src="https://placehold.co/300x450/3a3a3a/ffffff?text=Movie+2" alt="Movie 2" class="rounded-lg w-full h-48 object-cover play-button-trigger">
                        </div>
                        <!-- Movie Card -->
                        <div class="flex-shrink-0 w-32">
                            <img src="https://placehold.co/300x450/4a4a4a/ffffff?text=Movie+3" alt="Movie 3" class="rounded-lg w-full h-48 object-cover play-button-trigger">
                        </div>
                        <!-- Movie Card -->
                        <div class="flex-shrink-0 w-32">
                            <img src="https://placehold.co/300x450/5a5a5a/ffffff?text=Movie+4" alt="Movie 4" class="rounded-lg w-full h-48 object-cover play-button-trigger">
                        </div>
                    </div>
                </div>

                <!-- Category: Popular on MyStream -->
                <div>
                    <h3 class="text-xl font-semibold px-4 mb-3">Popular on MyStream</h3>
                    <div class="flex space-x-4 overflow-x-auto no-scrollbar px-4">
                        <!-- Movie Card -->
                        <div class="flex-shrink-0 w-32">
                            <img src="https://placehold.co/300x450/222222/ffffff?text=Movie+A" alt="Movie A" class="rounded-lg w-full h-48 object-cover play-button-trigger">
                        </div>
                        <!-- Movie Card -->
                        <div class="flex-shrink-0 w-32">
                            <img src="https://placehold.co/300x450/333333/ffffff?text=Movie+B" alt="Movie B" class="rounded-lg w-full h-48 object-cover play-button-trigger">
                        </div>
                        <!-- Movie Card -->
                        <div class="flex-shrink-0 w-32">
                            <img src="https://placehold.co/300x450/444444/ffffff?text=Movie+C" alt="Movie C" class="rounded-lg w-full h-48 object-cover play-button-trigger">
                        </div>
                        <!-- Movie Card -->
                        <div class="flex-shrink-0 w-32">
                            <img src="https://placehold.co/300x450/555555/ffffff?text=Movie+D" alt="Movie D" class="rounded-lg w-full h-48 object-cover play-button-trigger">
                        </div>
                    </div>
                </div>
            </section>
        </main>
        
        <!-- Bottom Navigation Bar (Fixed) -->
        <nav class="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-gray-800 border-t border-gray-700 p-3 flex justify-around">
            <!-- Home (Active) -->
            <button class="flex flex-col items-center text-red-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                </svg>
                <span class="text-xs">Home</span>
            </button>
            <!-- Coming Soon -->
            <button class="flex flex-col items-center text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-xs">Soon</span>
            </button>
            <!-- Downloads -->
            <button class="flex flex-col items-center text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span class="text-xs">Downloads</span>
            </button>
        </nav>
    </div>

    <!-- 
      CUSTOM VIDEO PLAYER MODAL
      This is hidden by default and overlays the entire screen when active.
    -->
    <div id="player-modal" class="hidden fixed inset-0 bg-black z-50 flex items-center justify-center">
        <!-- Video Container -->
        <div id="video-container" class="relative w-full h-full sm:w-auto sm:h-auto bg-black">
            
            <!-- The actual video element -->
            <video id="video-player" class="w-full h-full" src="https://test-videos.co.uk/vids/bigbuckbunny/mp4/h264/360/Big_Buck_Bunny_360_10s_1MB.mp4" playsinline>
                <!-- This 'src' is a placeholder. You will replace this with the URL from your storage provider. -->
            </video>

            <!-- Custom Controls Overlay -->
            <div id="controls-overlay" class="absolute inset-0 flex flex-col justify-between p-4 text-white opacity-0">
                
                <!-- Top Controls (Close Button) -->
                <div class="flex justify-end">
                    <button id="close-player" class="p-2 rounded-full hover:bg-white/20">
                        <!-- Close (X) Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
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

                <!-- Bottom Controls Bar -->
                <div class="space-y-2">
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
                            <!-- Volume Controls -->
                            <button id="volume-button" class="p-2">
                                <!-- Volume High Icon -->
                                <svg id="volume-high" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM14.657 2.929a1 1 0 011.414 0A9 9 0 0119 10a9 9 0 01-2.929 7.071 1 1 0 01-1.414-1.414A7 7 0 0017 10a7 7 0 00-1.414-4.95 1 1 0 010-1.121zM16.07 4.343a1 1 0 011.414 0A5 5 0 0119 10a5 5 0 01-1.515 3.536 1 1 0 11-1.414-1.414A3 3 0 0017 10a3 3 0 00-.93-2.121 1 1 0 010-1.536z" clip-rule="evenodd" />
                                </svg>
                                <!-- Volume Muted Icon (hidden) -->
                                <svg id="volume-muted" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 hidden" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM12.293 7.293a1 1 0 011.414 0L15 8.586l1.293-1.293a1 1 0 111.414 1.414L16.414 10l1.293 1.293a1 1 0 01-1.414 1.414L15 11.414l-1.293 1.293a1 1 0 01-1.414-1.414L13.586 10l-1.293-1.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <input id="volume-bar" type="range" value="100" min="0" max="100" class="w-20">
                            
                            <!-- Fullscreen Button -->
                            <button id="fullscreen-button" class="p-2">
                                <!-- Fullscreen Icon -->
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


    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Get all elements
            const playerModal = document.getElementById('player-modal');
            const videoContainer = document.getElementById('video-container');
            const video = document.getElementById('video-player');
            const closePlayerButton = document.getElementById('close-player');
            
            // All "Play" buttons
            const playFeaturedButton = document.getElementById('play-featured');
            const playButtons = document.querySelectorAll('.play-button-trigger');

            // Control elements
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

            // --- Modal Open/Close ---
            
            function openPlayer() {
                playerModal.classList.remove('hidden');
                video.play();
                videoContainer.classList.add('playing');
            }

            function closePlayer() {
                playerModal.classList.add('hidden');
                video.pause();
                videoContainer.classList.remove('playing');
            }
            
            playFeaturedButton.addEventListener('click', openPlayer);
            playButtons.forEach(btn => btn.addEventListener('click', openPlayer));
            closePlayerButton.addEventListener('click', closePlayer);

            // --- Video Player Logic ---

            // Toggle Play/Pause
            function togglePlay() {
                if (video.paused || video.ended) {
                    video.play();
                    videoContainer.classList.add('playing');
                } else {
                    video.pause();
                    videoContainer.classList.remove('playing');
                }
            }
            
            // Update Play/Pause icons
            function updatePlayPauseIcons() {
                const isPaused = video.paused;
                playIcons.forEach(icon => icon.classList.toggle('hidden', !isPaused));
                pauseIcons.forEach(icon => icon.classList.toggle('hidden', isPaused));
            }

            // Format tim