<?php
require 'db.php';

$db = get_db();
$message = '';

$project_id = (int)($_GET['project_id'] ?? 0);
if (!$project_id) { header('Location: admin.php'); exit; }

$project = $db->prepare("SELECT * FROM projects WHERE id = ?");
$project->execute([$project_id]);
$project = $project->fetch(PDO::FETCH_ASSOC);
if (!$project) { header('Location: admin.php'); exit; }

// Delete a card
if (isset($_POST['delete'])) {
    $db->prepare("DELETE FROM cards WHERE id = ? AND project_id = ?")
       ->execute([(int)$_POST['delete'], $project_id]);
    $message = 'Card deleted.';
}

// Add a new card
if (isset($_POST['add'])) {
    $db->prepare("INSERT INTO cards (project_id, title, body, rarity, sort_order) VALUES (?, ?, ?, ?, ?)")
       ->execute([
           $project_id,
           $_POST['new_title'] ?? '',
           $_POST['new_body'] ?? '',
           $_POST['new_rarity'] ?? 'common',
           (int)($_POST['new_sort_order'] ?? 0),
       ]);
    $message = 'Card added.';
}

// Save edits to existing cards
if (isset($_POST['save'])) {
    $stmt = $db->prepare("UPDATE cards SET title = ?, body = ?, rarity = ?, sort_order = ? WHERE id = ? AND project_id = ?");
    foreach ($_POST['cards'] as $id => $data) {
        $stmt->execute([
            $data['title'] ?? '',
            $data['body'] ?? '',
            $data['rarity'] ?? 'common',
            (int)($data['sort_order'] ?? 0),
            (int)$id,
            $project_id,
        ]);
    }
    $message = 'Changes saved.';
}

$cards = $db->prepare("SELECT * FROM cards WHERE project_id = ? ORDER BY sort_order ASC, id ASC");
$cards->execute([$project_id]);
$cards = $cards->fetchAll(PDO::FETCH_ASSOC);

$rarities = ['common', 'uncommon', 'rare', 'epic', 'legendary'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Edit Cards — <?= htmlspecialchars($project['name']) ?></title>
    <link rel="stylesheet" href="./css/admin.css">
</head>
<body>
    <a class="back" href="admin.php">← Back to Admin</a>
    <h1>Edit Cards: <?= htmlspecialchars($project['name']) ?></h1>

    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if (empty($cards)): ?>
        <p>No cards yet. Add one below.</p>
    <?php else: ?>
    <form method="post">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Body</th>
                    <th>Rarity</th>
                    <th>Order</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cards as $c): ?>
                <tr>
                    <td><input type="text" name="cards[<?= $c['id'] ?>][title]" value="<?= htmlspecialchars($c['title'] ?? '') ?>"></td>
                    <td><textarea name="cards[<?= $c['id'] ?>][body]"><?= htmlspecialchars($c['body'] ?? '') ?></textarea></td>
                    <td>
                        <select name="cards[<?= $c['id'] ?>][rarity]">
                            <?php foreach ($rarities as $r): ?>
                            <option value="<?= $r ?>" <?= $c['rarity'] === $r ? 'selected' : '' ?> class="rarity-<?= $r ?>"><?= ucfirst($r) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><input type="number" name="cards[<?= $c['id'] ?>][sort_order]" value="<?= (int)$c['sort_order'] ?>"></td>
                    <td>
                        <button type="submit" name="delete" value="<?= $c['id'] ?>" class="btn-delete">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit" name="save" value="1">Save Changes</button>
    </form>
    <?php endif; ?>

    <h2>Add Card</h2>
    <form method="post">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Body</th>
                    <th>Rarity</th>
                    <th>Order</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="text" name="new_title"></td>
                    <td><textarea name="new_body"></textarea></td>
                    <td>
                        <select name="new_rarity">
                            <?php foreach ($rarities as $r): ?>
                            <option value="<?= $r ?>"><?= ucfirst($r) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><input type="number" name="new_sort_order" value="0"></td>
                    <td><button type="submit" name="add" value="1">Add</button></td>
                </tr>
            </tbody>
        </table>
    </form>
</body>
</html>
