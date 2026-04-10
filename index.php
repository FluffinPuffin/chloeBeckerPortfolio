<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chloe Becker's Portfolio</title>
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>
    <?php require 'header.php'; ?>
    <h1>Home</h1>
    <p>Welcome to my portfolio! Here you can find information about my background, skills, and experience as a software engineer. Feel free to explore the different sections and contact me if you have any questions or would like to work together.</p>

    <?php
    require 'db.php';
    $db = get_db();

    // Random featured pack
    $featured = $db->query("SELECT * FROM projects WHERE visible = 1 ORDER BY RANDOM() LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $featured_pack_data = [];
    if ($featured) {
        $stmt = $db->prepare("SELECT * FROM cards WHERE project_id = ? ORDER BY sort_order ASC, id ASC");
        $stmt->execute([$featured['id']]);
        $featured_pack_data[$featured['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $current = $db->query("SELECT * FROM projects WHERE is_current = 1 AND visible = 1 ORDER BY sort_order ASC, name ASC")->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <?php if ($featured): ?>
    <?php
        $feat_name = !empty($featured['custom_name']) ? $featured['custom_name'] : $featured['name'];
        $feat_desc = !empty($featured['custom_description']) ? $featured['custom_description'] : $featured['description'];
    ?>
    <p class="packs-label">Random Project Of the Day</p>
    <div class="packs" data-opened="<?= htmlspecialchars(json_encode(array_map('strval', $_SESSION['opened_packs'] ?? [])), ENT_QUOTES) ?>">
        <div class="pack" data-project-id="<?= $featured['id'] ?>" data-cards="<?= htmlspecialchars(json_encode($featured_pack_data[$featured['id']] ?? []), ENT_QUOTES) ?>">
            <div class="pack-inner">
                <h2><?= htmlspecialchars($feat_name) ?></h2>
                <?php if (!empty($feat_desc)): ?>
                <p class="pack-desc"><?= htmlspecialchars($feat_desc) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($current): ?>
    <section class="current-projects">
        <h2>Current Projects</h2>
        <div class="current-projects-grid">
            <?php foreach ($current as $p):
                $name = !empty($p['custom_name']) ? $p['custom_name'] : $p['name'];
                $desc = !empty($p['custom_description']) ? $p['custom_description'] : $p['description'];
            ?>
            <div class="current-project-card">
                <h3><?= htmlspecialchars($name) ?></h3>
                <?php if (!empty($desc)): ?>
                <p><?= htmlspecialchars($desc) ?></p>
                <?php endif; ?>
                <?php if (!empty($p['language'])): ?>
                <span class="current-project-lang"><?= htmlspecialchars($p['language']) ?></span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <section class="experience-section">
        <h2 class="section-label">Experience</h2>
        <div class="experience-list">
            <div class="experience-item">
                <div class="experience-header">
                    <div>
                        <span class="experience-title">Tutor</span>
                        <span class="experience-company">University of Central Florida</span>
                    </div>
                    <span class="experience-dates">Oct. 2025 – Present</span>
                </div>
                <ul class="experience-desc">
                    <li>Provide one-on-one and group tutoring in computer science and digital media coursework.</li>
                    <li>Develop customized learning strategies to improve student retention and problem-solving skills.</li>
                    <li>Communicate student progress and learning challenges with faculty to optimize academic support.</li>
                </ul>
            </div>
            <div class="experience-item">
                <div class="experience-header">
                    <div>
                        <span class="experience-title">Software Engineering Intern</span>
                        <span class="experience-company">BNY</span>
                    </div>
                    <span class="experience-dates">June 2025 – Aug. 2025</span>
                </div>
                <ul class="experience-desc">
                    <li>Contributed to backend logic and data processing workflows for in-house projects.</li>
                    <li>Improved internal dashboard interface to enhance usability and performance.</li>
                    <li>Worked in an Agile environment with cross-functional engineering teams.</li>
                </ul>
            </div>
            <div class="experience-item">
                <div class="experience-header">
                    <div>
                        <span class="experience-title">Teaching Assistant</span>
                        <span class="experience-company">University of Central Florida</span>
                    </div>
                    <span class="experience-dates">Jan. 2025 – May 2025</span>
                </div>
                <ul class="experience-desc">
                    <li>Assisted in teaching backend web development concepts including server-side logic and database integration.</li>
                    <li>Supported students during debugging sessions, improving code comprehension and implementation skills.</li>
                    <li>Strengthened communication and technical mentoring abilities.</li>
                </ul>
            </div>
            <div class="experience-item">
                <div class="experience-header">
                    <div>
                        <span class="experience-title">Esports Team Leader</span>
                        <span class="experience-company">University of Central Florida Esports</span>
                    </div>
                    <span class="experience-dates">Jan. 2024 – Apr. 2025</span>
                </div>
                <ul class="experience-desc">
                    <li>Led team operations, strategy planning, and communication for competitive collegiate events.</li>
                    <li>Coordinated team logistics while balancing academic responsibilities.</li>
                    <li>Developed leadership and conflict resolution skills in high-pressure environments.</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Pack preview overlay -->
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
        <div class="card-stack-wrap" id="card-stack-wrap">
            <div class="modal-card" id="modal-card">
                <div class="modal-card-rarity" id="modal-card-rarity"></div>
                <div class="modal-card-title" id="modal-card-title"></div>
                <div class="modal-card-body" id="modal-card-body"></div>
            </div>
        </div>
        <div class="card-spread-wrap" id="card-spread-wrap"></div>
        <button class="modal-close-btn" id="modal-close-btn">✕</button>
    </div>

    <script>
    const PACK_DATA    = <?= json_encode($featured_pack_data) ?>;
    const OPENED_PACKS = <?= json_encode(array_map('strval', $_SESSION['opened_packs'] ?? [])) ?>;
    </script>
    <script src="./js/script.js"></script>
</body>

</html>