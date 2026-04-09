<?php
require 'admin_auth.php';
require 'config.php';
require 'db.php';

$db = get_db();
$message = '';

if ($is_authed) {
    // Sync from GitHub
    if (isset($_POST['sync'])) {
        $context = stream_context_create(['http' => [
            'header' => "User-Agent: FluffinPuffin-Portfolio\r\nAuthorization: Bearer " . GITHUB_TOKEN . "\r\n"
        ]]);
        $json = file_get_contents('https://api.github.com/users/FluffinPuffin/repos?sort=updated&per_page=100', false, $context);
        $repos = json_decode($json, true);

        if ($repos) {
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
            $message = 'Synced ' . count($repos) . ' repos from GitHub.';
        } else {
            $message = 'Failed to fetch repos from GitHub.';
        }
    }

    // Save edits
    if (isset($_POST['save'])) {
        $stmt = $db->prepare("
            UPDATE projects SET
                custom_name        = :custom_name,
                custom_description = :custom_description,
                homepage           = :homepage,
                visible            = :visible,
                sort_order         = :sort_order
            WHERE id = :id
        ");
        foreach ($_POST['projects'] as $id => $data) {
            $stmt->execute([
                ':id'                 => (int)$id,
                ':custom_name'        => $data['custom_name'] ?? '',
                ':custom_description' => $data['custom_description'] ?? '',
                ':homepage'           => $data['homepage'] ?? '',
                ':visible'            => isset($data['visible']) ? 1 : 0,
                ':sort_order'         => (int)($data['sort_order'] ?? 0),
            ]);
        }
        $message = 'Changes saved.';
    }

    $projects = $db->query("
        SELECT p.*, COUNT(c.id) AS card_count
        FROM projects p
        LEFT JOIN cards c ON c.project_id = p.id
        GROUP BY p.id
        ORDER BY p.sort_order ASC, p.name ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin — Portfolio</title>
    <link rel="stylesheet" href="./css/admin.css">
</head>
<body>
<?php if (!$is_authed): ?>
    <div class="admin-login-page">
        <form method="post">
            <label>Admin password</label>
            <input type="password" name="admin_login" autofocus>
            <button type="submit">Log in</button>
            <?php if ($login_error): ?>
                <span class="error"><?= htmlspecialchars($login_error) ?></span>
            <?php endif; ?>
        </form>
    </div>
<?php else: ?>
    <h1>Portfolio Admin</h1>

    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="actions">
        <form method="post" style="margin:0">
            <button name="sync" value="1">Sync from GitHub</button>
        </form>
    </div>

    <?php if (empty($projects)): ?>
        <p>No projects yet. Click "Sync from GitHub" to import your repos.</p>
    <?php else: ?>
    <form method="post">
        <table>
            <thead>
                <tr>
                    <th>Repo</th>
                    <th>Pack Name</th>
                    <th>Custom Description</th>
                    <th>Homepage / Demo URL</th>
                    <th>Cards</th>
                    <th>Order</th>
                    <th>Visible</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $p): ?>
                <tr>
                    <td class="repo-name">
                        <a href="<?= htmlspecialchars($p['html_url']) ?>" target="_blank"><?= htmlspecialchars($p['name']) ?></a>
                        <?php if ($p['is_fork']): ?><span class="fork-badge">fork</span><?php endif; ?>
                        <div class="meta">
                            <?= htmlspecialchars($p['language'] ?? '—') ?>
                            &nbsp;·&nbsp; ★ <?= $p['stars'] ?>
                            &nbsp;·&nbsp; <?= htmlspecialchars($p['description'] ?? 'No description') ?>
                        </div>
                    </td>
                    <td>
                        <input type="text" name="projects[<?= $p['id'] ?>][custom_name]" value="<?= htmlspecialchars($p['custom_name'] ?? '') ?>" placeholder="<?= htmlspecialchars($p['name']) ?>">
                    </td>
                    <td>
                        <textarea name="projects[<?= $p['id'] ?>][custom_description]"><?= htmlspecialchars($p['custom_description'] ?? '') ?></textarea>
                    </td>
                    <td>
                        <input type="text" name="projects[<?= $p['id'] ?>][homepage]" value="<?= htmlspecialchars($p['homepage'] ?? '') ?>">
                    </td>
                    <td>
                        <a class="edit-cards" href="admin_cards.php?project_id=<?= $p['id'] ?>">Edit Cards (<?= (int)$p['card_count'] ?>)</a>
                    </td>
                    <td>
                        <input type="number" name="projects[<?= $p['id'] ?>][sort_order]" value="<?= (int)$p['sort_order'] ?>">
                    </td>
                    <td>
                        <input type="checkbox" name="projects[<?= $p['id'] ?>][visible]" <?= $p['visible'] ? 'checked' : '' ?>>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <br>
        <button type="submit" name="save" value="1">Save Changes</button>
    </form>
    <?php endif; ?>
<?php endif; ?>
</body>
</html>
