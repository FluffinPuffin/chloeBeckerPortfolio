<?php
function get_db() {
    $db = new PDO('sqlite:' . __DIR__ . '/projects.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("CREATE TABLE IF NOT EXISTS projects (
        id             INTEGER PRIMARY KEY AUTOINCREMENT,
        github_id      INTEGER UNIQUE,
        name           TEXT NOT NULL,
        description    TEXT,
        custom_description TEXT,
        homepage       TEXT,
        language       TEXT,
        topics         TEXT,
        stars          INTEGER DEFAULT 0,
        forks          INTEGER DEFAULT 0,
        html_url       TEXT,
        is_fork        INTEGER DEFAULT 0,
        visible        INTEGER DEFAULT 1,
        sort_order     INTEGER DEFAULT 0,
        created_at     TEXT,
        updated_at     TEXT,
        synced_at      TEXT
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS cards (
        id         INTEGER PRIMARY KEY AUTOINCREMENT,
        project_id INTEGER NOT NULL,
        title      TEXT,
        body       TEXT,
        rarity     TEXT DEFAULT 'common',
        sort_order INTEGER DEFAULT 0,
        FOREIGN KEY (project_id) REFERENCES projects(id)
    )");

    // Migrations
    try { $db->exec("ALTER TABLE projects ADD COLUMN custom_name TEXT"); } catch (PDOException $e) {}
    try { $db->exec("ALTER TABLE projects ADD COLUMN is_current INTEGER DEFAULT 0"); } catch (PDOException $e) {}

    return $db;
}
