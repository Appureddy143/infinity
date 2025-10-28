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

-- Your Neon PostgreSQL Database Schema
-- This file defines the structure of your database.

-- Table for user information
CREATE TABLE users (
    user_id SERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Main table for all media (movies and series)
CREATE TABLE movies (
    movie_id SERIAL PRIMARY KEY,
    type VARCHAR(10) NOT NULL, -- 'movie' or 'series'
    title VARCHAR(255) NOT NULL,
    description TEXT,
    poster_url VARCHAR(255),
    video_url VARCHAR(255),     -- Only for 'movie' type
    language VARCHAR(50),      -- e.g., 'Kannada', 'Multi'
    genre VARCHAR(100),        -- 'Action, Thriller'
    duration VARCHAR(20),      -- Only for 'movie' type (e.g., '2h 15m')
    release_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for seasons (linked to a 'series' in the movies table)
CREATE TABLE seasons (
    season_id SERIAL PRIMARY KEY,
    movie_id INT NOT NULL REFERENCES movies(movie_id) ON DELETE CASCADE,
    season_number INT NOT NULL,
    title VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for individual episodes (linked to a season)
-- UPDATED: Added 'language' column
CREATE TABLE episodes (
    episode_id SERIAL PRIMARY KEY,
    season_id INT NOT NULL REFERENCES seasons(season_id) ON DELETE CASCADE,
    episode_number INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    video_url VARCHAR(255) NOT NULL,
    duration_seconds INT,      -- Stored in seconds for easy calculations
    thumbnail_url VARCHAR(255),
    language VARCHAR(50),      -- Language for this specific episode
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table to track user watch history
CREATE TABLE watch_history (
    history_id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    movie_id INT REFERENCES movies(movie_id) ON DELETE CASCADE,     -- For tracking movies
    episode_id INT REFERENCES episodes(episode_id) ON DELETE CASCADE, -- For tracking episodes
    watch_time_seconds INT NOT NULL,
    last_watched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Ensure a user has only one history entry per item
    UNIQUE(user_id, movie_id),
    UNIQUE(user_id, episode_id)
);

-- Optional: Create indexes for faster queries
CREATE INDEX idx_watch_history_user ON watch_history(user_id);
CREATE INDEX idx_movies_language ON movies(language);
CREATE INDEX idx_movies_type ON movies(type);

