<?php $current_page = basename($_SERVER['PHP_SELF']); ?>
<header class="site-header">
    <a class="site-title" href="index.php">Chloe Becker</a>
    <nav>
        <ul>
            <li><a href="index.php" <?= $current_page === 'index.php' ? 'class="active"' : '' ?>>Home</a></li>
            <li><a href="bio.php" <?= $current_page === 'bio.php' ? 'class="active"' : '' ?>>Bio</a></li>
            <li><a href="portfolio.php" <?= $current_page === 'portfolio.php' ? 'class="active"' : '' ?>>Portfolio</a></li>
            <li><a href="contact.php" <?= $current_page === 'contact.php' ? 'class="active"' : '' ?>>Contact</a></li>
        </ul>
    </nav>
</header>