-- ============================================
--  Lost & Found Campus Hub — database.sql
--  Run this file once to set up all tables
--  In phpMyAdmin: Import > choose this file
-- ============================================

CREATE DATABASE IF NOT EXISTS lost_found CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lost_found;

-- USERS table
CREATE TABLE IF NOT EXISTS users (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(100)        NOT NULL,
  email       VARCHAR(150) UNIQUE NOT NULL,
  password    VARCHAR(255)        NOT NULL,   -- store hashed with password_hash()
  student_id  VARCHAR(20),
  avatar      VARCHAR(255),                   -- profile picture filename
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ITEMS table
CREATE TABLE IF NOT EXISTS items (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  user_id     INT            NOT NULL,
  name        VARCHAR(150)   NOT NULL,
  category    ENUM('bag','electronics','keys','clothing','other') DEFAULT 'other',
  status      ENUM('unresolved','found','returned') DEFAULT 'unresolved',
  location    VARCHAR(200)   NOT NULL,
  item_date   DATE           NOT NULL,
  item_time   TIME           NOT NULL,
  description TEXT,
  image       VARCHAR(255),                   -- uploaded image filename
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- CLAIMS table
CREATE TABLE IF NOT EXISTS claims (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  item_id     INT          NOT NULL,
  user_id     INT          NOT NULL,          -- person making the claim
  proof       TEXT         NOT NULL,
  status      ENUM('pending','approved','rejected') DEFAULT 'pending',
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- CONVERSATIONS table
CREATE TABLE IF NOT EXISTS conversations (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  user1_id    INT NOT NULL,
  user2_id    INT NOT NULL,
  item_id     INT,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user1_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (user2_id) REFERENCES users(id) ON DELETE CASCADE
);

-- MESSAGES table
CREATE TABLE IF NOT EXISTS messages (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  conversation_id INT     NOT NULL,
  sender_id       INT     NOT NULL,
  message         TEXT    NOT NULL,
  is_read         TINYINT DEFAULT 0,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
  FOREIGN KEY (sender_id)       REFERENCES users(id)         ON DELETE CASCADE
);
