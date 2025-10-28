-- This is the SQL schema for your Neon (PostgreSQL) database.
-- Run these commands in your Neon SQL editor to create the tables.

-- Table for user accounts
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Main table for all content (movies and series)
CREATE TABLE movies (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    poster_url VARCHAR(1024),
    video_url VARCHAR(1024),  -- Used for movies only
    language VARCHAR(50),
    genre VARCHAR(100),
    duration VARCHAR(20),     -- Used for movies only (e.g., "2h 15m")
    type VARCHAR(10) NOT NULL, -- 'movie' or 'series'
    release_date DATE DEFAULT CURRENT_DATE,
    popularity INT DEFAULT 0,
    featured BOOLEAN DEFAULT false
);

-- Table for series seasons (links to 'movies' table)
CREATE TABLE seasons (
    id SERIAL PRIMARY KEY,
    movie_id INT NOT NULL REFERENCES movies(id) ON DELETE CASCADE,
    season_number INT NOT NULL,
    title VARCHAR(255) -- e.g., "Season 1"
);

-- Table for series episodes (links to 'seasons' table)
CREATE TABLE episodes (
    id SERIAL PRIMARY KEY,
    season_id INT NOT NULL REFERENCES seasons(id) ON DELETE CASCADE,
    episode_number INT NOT NULL,
    title VARCHAR(255),
    description TEXT,
    video_url VARCHAR(1024),
    duration_seconds INT -- Store duration in seconds for progress bars
);

-- Table for user watch history
-- This links to EITHER a movie ID or an episode ID.
-- We use 'content_id' as a generic term.
CREATE TABLE watch_history (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    content_id INT NOT NULL, -- This can be a 'movies.id' OR 'episodes.id'
    watch_time INT NOT NULL DEFAULT 0, -- Store in seconds
    last_watched TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Ensures a user has only one history entry per piece of content
    UNIQUE(user_id, content_id) 
);

-- Optional: Create indexes for faster lookups
CREATE INDEX idx_movies_language ON movies(language);
CREATE INDEX idx_movies_type ON movies(type);
CREATE INDEX idx_watch_history_user ON watch_history(user_id);
