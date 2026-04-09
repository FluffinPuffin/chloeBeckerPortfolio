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

function sync_from_github(PDO $db): bool {
    require_once __DIR__ . '/config.php';
    $token = GITHUB_TOKEN;
    if (!$token || $token === 'YOUR_NEW_TOKEN_HERE') return false;

    $context = stream_context_create(['http' => [
        'header'  => "User-Agent: FluffinPuffin-Portfolio\r\nAuthorization: Bearer $token\r\n",
        'timeout' => 10,
    ]]);
    $json = @file_get_contents('https://api.github.com/users/FluffinPuffin/repos?sort=updated&per_page=100', false, $context);
    $repos = $json ? json_decode($json, true) : null;
    if (!$repos || !is_array($repos)) return false;

    $stmt = $db->prepare("
        INSERT INTO projects (github_id, name, description, homepage, language, topics, stars, forks, html_url, is_fork, created_at, updated_at, synced_at)
        VALUES (:github_id, :name, :description, :homepage, :language, :topics, :stars, :forks, :html_url, :is_fork, :created_at, :updated_at, datetime('now'))
        ON CONFLICT(github_id) DO UPDATE SET
            name        = excluded.name,
            description = excluded.description,
            homepage    = excluded.homepage,
            language    = excluded.language,
            topics      = excluded.topics,
            stars       = excluded.stars,
            forks       = excluded.forks,
            html_url    = excluded.html_url,
            is_fork     = excluded.is_fork,
            updated_at  = excluded.updated_at,
            synced_at   = datetime('now')
    ");
    foreach ($repos as $repo) {
        $stmt->execute([
            ':github_id'   => $repo['id'],
            ':name'        => $repo['name'],
            ':description' => $repo['description'],
            ':homepage'    => $repo['homepage'],
            ':language'    => $repo['language'],
            ':topics'      => json_encode($repo['topics']),
            ':stars'       => $repo['stargazers_count'],
            ':forks'       => $repo['forks_count'],
            ':html_url'    => $repo['html_url'],
            ':is_fork'     => (int)$repo['fork'],
            ':created_at'  => $repo['created_at'],
            ':updated_at'  => $repo['updated_at'],
        ]);
    }
    return true;
}
