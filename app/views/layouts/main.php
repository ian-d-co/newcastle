<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="csrf-token" content="<?php echo e(generateCsrfToken()); ?>">
    <title><?php echo isset($pageTitle) ? e($pageTitle) . ' - ' : ''; ?>Dicksord Fest 2026 - Newcastle</title>
    <meta name="description" content="Dicksord Fest 2026 - Newcastle event management platform">
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="/index.php" class="logo">Dicksord Fest 2026</a>
                
                <button class="nav-toggle" aria-label="Toggle navigation">☰</button>
                
                <?php if (isLoggedIn()): ?>
                <nav class="nav">
                    <ul>
                        <li><a href="/index.php?page=home" class="<?php echo ($currentPage ?? '') === 'home' ? 'active' : ''; ?>">Home</a></li>
                        <li><a href="/index.php?page=activities" class="<?php echo ($currentPage ?? '') === 'activities' ? 'active' : ''; ?>">Activities</a></li>
                        <li><a href="/index.php?page=meals" class="<?php echo ($currentPage ?? '') === 'meals' ? 'active' : ''; ?>">Meals</a></li>
                        <li><a href="/index.php?page=carshare" class="<?php echo ($currentPage ?? '') === 'carshare' ? 'active' : ''; ?>">Carshare</a></li>
                        <li><a href="/index.php?page=hosting" class="<?php echo ($currentPage ?? '') === 'hosting' ? 'active' : ''; ?>">Hosting</a></li>
                        <li><a href="/index.php?page=polls" class="<?php echo ($currentPage ?? '') === 'polls' ? 'active' : ''; ?>">Polls</a></li>
                        <li><a href="/index.php?page=hotels" class="<?php echo ($currentPage ?? '') === 'hotels' ? 'active' : ''; ?>">Hotels</a></li>
                        <li><a href="/index.php?page=dashboard" class="<?php echo ($currentPage ?? '') === 'dashboard' ? 'active' : ''; ?>">Dashboard</a></li>
                        <?php if (isAdmin()): ?>
                        <li><a href="/index.php?page=admin" class="<?php echo ($currentPage ?? '') === 'admin' ? 'active' : ''; ?>">Admin</a></li>
                        <?php endif; ?>
                        <li><a href="/index.php?action=logout">Logout</a></li>
                    </ul>
                </nav>
                <?php else: ?>
                <nav class="nav">
                    <ul>
                        <li><a href="/index.php?page=login" class="<?php echo ($currentPage ?? '') === 'login' ? 'active' : ''; ?>">Login</a></li>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <?php echo $content ?? ''; ?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2026 Dicksord Fest - Newcastle. All rights reserved.</p>
            <p>November 20-22, 2026</p>
        </div>
    </footer>

    <script src="/js/app.js"></script>
    <script src="/js/scripts.js"></script>
    <script src="/js/modals.js"></script>
    <script src="/js/forms.js"></script>
    <script src="/js/charts.js"></script>
</body>
</html>
