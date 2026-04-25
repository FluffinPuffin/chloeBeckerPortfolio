<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chloe Becker — Portfolio</title>
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>
    <?php require 'header.php'; ?>

    <section class="hero">
        <div class="hero-badge">Open to opportunities · Class of 2026</div>
        <h1>Chloe Becker</h1>
        <p class="hero-role">Software Engineer &amp; Digital Media Student</p>
        <p>UCF senior building web apps, backend systems, and interactive tools. I care about making things that are clean, functional, and actually useful.</p>
        <div class="hero-actions">
            <a href="portfolio.php" class="btn btn-primary">View Portfolio</a>
            <a href="contact.php" class="btn btn-secondary">Get in Touch</a>
        </div>
        <div class="skill-tags">
            <span class="skill-tag">PHP</span>
            <span class="skill-tag">JavaScript</span>
            <span class="skill-tag">Python</span>
            <span class="skill-tag">HTML / CSS</span>
            <span class="skill-tag">SQL</span>
            <span class="skill-tag">Java</span>
            <span class="skill-tag">C</span>
            <span class="skill-tag">React</span>
            <span class="skill-tag">Git</span>
            <span class="skill-tag">Docker</span>
            <span class="skill-tag">SQLite</span>
            <span class="skill-tag">Linux</span>
        </div>
    </section>

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
    <p class="packs-label">Random Project of the Day</p>
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

    <?php require 'footer.php'; ?>
</body>

</html>
