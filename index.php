<!DOCTYPE html>
<html lang="en">

<head>
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
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
    $current = $db->query("SELECT * FROM projects WHERE is_current = 1 AND visible = 1 ORDER BY sort_order ASC, name ASC")->fetchAll(PDO::FETCH_ASSOC);
    if ($current):
    ?>
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
</body>

</html>