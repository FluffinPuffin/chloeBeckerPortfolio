<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chloe Becker's Portfolio</title>
    <link rel="stylesheet" href="./css/style.css">
    <script src="./js/script.js"></script>
</head>

<body>
    <?php require 'header.php'; ?>
    <h1>My Portfolio</h1>
    <p>Click a pack to open it.</p>

    <?php
    require 'db.php';
    $db = get_db();

    $projects = $db->query("SELECT * FROM projects WHERE visible = 1 ORDER BY sort_order ASC, name ASC")->fetchAll(PDO::FETCH_ASSOC);

    // Load all cards for visible projects, keyed by project id
    $pack_data = [];
    if ($projects) {
        $ids = array_column($projects, 'id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $db->prepare("SELECT * FROM cards WHERE project_id IN ($placeholders) ORDER BY sort_order ASC, id ASC");
        $stmt->execute($ids);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $card) {
            $pack_data[$card['project_id']][] = $card;
        }
    }

    if ($projects):
    ?>
    <div class="packs" data-opened="<?= htmlspecialchars(json_encode(array_map('strval', $_SESSION['opened_packs'] ?? [])), ENT_QUOTES) ?>">
        <?php foreach ($projects as $p):
            $card_count = count($pack_data[$p['id']] ?? []);
            $display_name = !empty($p['custom_name']) ? $p['custom_name'] : $p['name'];
            $desc = !empty($p['custom_description']) ? $p['custom_description'] : $p['description'];
        ?>
        <div class="pack" data-project-id="<?= $p['id'] ?>" data-cards="<?= htmlspecialchars(json_encode($pack_data[$p['id']] ?? []), ENT_QUOTES) ?>">
            <div class="pack-inner">
                <h2><?= htmlspecialchars($display_name) ?></h2>
                <?php if (!empty($desc)): ?>
                <p class="pack-desc"><?= htmlspecialchars($desc) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p>No projects to show yet. <a href="https://github.com/FluffinPuffin" target="_blank" rel="noopener noreferrer">View on GitHub</a></p>
    <?php endif; ?>

    <!-- Pack preview overlay (flies from grid to center) -->
    <div id="pack-preview-overlay">
        <div id="pack-preview" class="pack">
            <div class="pack-inner">
                <h2 id="preview-name"></h2>
                <p id="preview-desc" class="pack-desc"></p>
                <div class="pack-footer">
                    <span id="preview-card-count" class="pack-card-count"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Card opening modal -->
    <div class="modal-overlay" id="modal" aria-hidden="true">
        <!-- One-at-a-time view (first open) -->
        <div class="card-stack-wrap" id="card-stack-wrap">
            <div class="modal-card" id="modal-card">
                <div class="modal-card-rarity" id="modal-card-rarity"></div>
                <div class="modal-card-title" id="modal-card-title"></div>
                <div class="modal-card-body" id="modal-card-body"></div>
            </div>
        </div>
        <!-- All-cards-spread view (re-open or end of pack) -->
        <div class="card-spread-wrap" id="card-spread-wrap"></div>
        <button class="modal-close-btn" id="modal-close-btn">✕</button>
    </div>

</body>

</html>
