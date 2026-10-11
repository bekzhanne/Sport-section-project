CREATE DATABASE IF NOT EXISTS kbtu_culturist
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE kbtu_culturist;

CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name          VARCHAR(100) NOT NULL,
    email         VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sports_sections (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug        VARCHAR(80) NOT NULL,
    name        VARCHAR(120) NOT NULL,
    description TEXT NOT NULL,
    instructor  VARCHAR(120) NOT NULL,
    location    VARCHAR(160) NOT NULL,
    capacity    SMALLINT UNSIGNED NOT NULL DEFAULT 20,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_sports_sections_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS section_memberships (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id    INT UNSIGNED NOT NULL,
    section_id INT UNSIGNED NOT NULL,
    status     ENUM('active', 'cancelled') NOT NULL DEFAULT 'active',
    joined_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_section_membership (user_id, section_id),
    KEY ix_memberships_section_status (section_id, status),
    CONSTRAINT fk_memberships_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_memberships_section FOREIGN KEY (section_id) REFERENCES sports_sections (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS class_sessions (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    section_id INT UNSIGNED NOT NULL,
    starts_at  DATETIME NOT NULL,
    capacity   SMALLINT UNSIGNED NOT NULL DEFAULT 20,
    is_active  TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_section_session_start (section_id, starts_at),
    KEY ix_sessions_start (starts_at),
    CONSTRAINT fk_sessions_section FOREIGN KEY (section_id) REFERENCES sports_sections (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS class_bookings (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id    INT UNSIGNED NOT NULL,
    session_id INT UNSIGNED NOT NULL,
    status     ENUM('booked', 'cancelled') NOT NULL DEFAULT 'booked',
    booked_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_class_booking (user_id, session_id),
    KEY ix_bookings_session_status (session_id, status),
    CONSTRAINT fk_bookings_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_bookings_session FOREIGN KEY (session_id) REFERENCES class_sessions (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO sports_sections (slug, name, description, instructor, location, capacity) VALUES
    ('strength-training', 'Strength training', 'Build strength and confidence with coached resistance training for different experience levels.', 'Alex Morgan', 'KBTU Sports Hall · Gym 1', 20),
    ('yoga', 'Yoga', 'Improve mobility, balance, and recovery through guided yoga practice.', 'Amina Sadykova', 'KBTU Sports Hall · Studio 2', 18),
    ('boxing', 'Boxing', 'Learn boxing fundamentals, footwork, and conditioning in a coached group.', 'Daniyar Karimov', 'KBTU Sports Hall · Ring', 16),
    ('basketball', 'Basketball', 'Practice team skills, shooting, and match play on the indoor court.', 'Timur Nurlan', 'KBTU Sports Hall · Court 1', 16),
    ('football', 'Football', 'Train ball control, passing, and team play in a welcoming group.', 'Arman Bek', 'KBTU Sports Ground', 20),
    ('swimming', 'Swimming', 'Develop swimming technique and endurance with instructor guidance.', 'Madina Kassen', 'KBTU Pool', 12),
    ('volleyball', 'Volleyball', 'Work on serves, movement, and team coordination through drills and games.', 'Nursultan Ali', 'KBTU Sports Hall · Court 2', 16),
    ('functional-fitness', 'Functional fitness', 'Combine strength, balance, and cardio exercises in adaptable circuits.', 'Dana Ospan', 'KBTU Sports Hall · Studio 1', 18);

INSERT IGNORE INTO class_sessions (section_id, starts_at, capacity)
SELECT id, TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '18:00:00'), capacity
FROM sports_sections
WHERE is_active = 1;

INSERT IGNORE INTO class_sessions (section_id, starts_at, capacity)
SELECT id, TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 8 DAY), '18:00:00'), capacity
FROM sports_sections
WHERE is_active = 1;
