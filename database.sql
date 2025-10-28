-- ==========================================================
-- USERS TABLE
-- ==========================================================
CREATE TABLE users (
    user_id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================================
-- MOVIES TABLE
-- Stores both movies and series
-- ==========================================================
CREATE TABLE movies (
    movie_id SERIAL PRIMARY KEY,
    type VARCHAR(10) NOT NULL CHECK (type IN ('movie', 'series')),
    title VARCHAR(255) NOT NULL,
    description TEXT,
    poster_url VARCHAR(512),
    video_url VARCHAR(512), -- Only used for 'movie' type
    language VARCHAR(50),
    genre VARCHAR(100),
    duration_seconds INT, -- Optional for 'movie' type
    release_date DATE DEFAULT CURRENT_DATE,
    rating DECIMAL(3, 1) DEFAULT 0.0,
    popularity INT DEFAULT 0,
    featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================================
-- SEASONS TABLE
-- Links seasons to a series (movie_id of type 'series')
-- ==========================================================
CREATE TABLE seasons (
    season_id SERIAL PRIMARY KEY,
    movie_id INT NOT NULL REFERENCES movies(movie_id) ON DELETE CASCADE,
    season_number INT NOT NULL,
    title VARCHAR(255),
    UNIQUE(movie_id, season_number),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================================
-- EPISODES TABLE
-- Individual episodes within a season
-- ==========================================================
CREATE TABLE episodes (
    episode_id SERIAL PRIMARY KEY,
    season_id INT NOT NULL REFERENCES seasons(season_id) ON DELETE CASCADE,
    episode_number INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    thumbnail_url VARCHAR(512),
    video_url VARCHAR(512) NOT NULL,
    language VARCHAR(50),
    duration_seconds INT NOT NULL,
    UNIQUE(season_id, episode_number),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================================
-- WATCH HISTORY TABLE
-- Tracks user progress on movies and episodes
-- ==========================================================
CREATE TABLE watch_history (
    watch_history_id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    movie_id INT NOT NULL REFERENCES movies(movie_id) ON DELETE CASCADE,
    episode_id INT REFERENCES episodes(episode_id) ON DELETE CASCADE, -- Nullable for movies
    progress_seconds INT NOT NULL DEFAULT 0,
    total_duration_seconds INT DEFAULT 0,
    last_watched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, movie_id, episode_id)
);

-- ==========================================================
-- INDEXES FOR PERFORMANCE
-- ==========================================================
CREATE INDEX idx_movies_type_language ON movies(type, language);
CREATE INDEX idx_movies_genre ON movies(genre);
CREATE INDEX idx_movies_popularity ON movies(popularity DESC);
CREATE INDEX idx_watch_history_user ON watch_history(user_id, last_watched_at DESC);
