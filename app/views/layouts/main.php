<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="csrf-token" content="<?php echo e(generateCsrfToken()); ?>">
    <title><?php echo isset($pageTitle) ? e($pageTitle) . ' - ' : ''; ?>Dicksord Fest 2026 - Newcastle</title>
    <meta name="description" content="Dicksord Fest 2026 - Newcastle event management platform">
    <link rel="stylesheet" href="/css/styles.css?v=<?php echo time(); ?>">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="/index.php" class="logo">Dicksord Fest 2026</a>
                
                <?php if (!isLoggedIn()): ?>
                <!-- Mobile login button (visible on mobile without toggle) -->
                <a href="/index.php?page=login" class="btn-login-mobile <?php echo ($currentPage ?? '') === 'login' ? 'active' : ''; ?>">Login</a>
                <?php endif; ?>
                
                <button class="nav-toggle" aria-label="Toggle navigation">☰</button>
                
                <?php if (isLoggedIn()): ?>
                <nav class="nav">
                    <ul>
                        <li><a href="/index.php?page=home" class="<?php echo ($currentPage ?? '') === 'home' ? 'active' : ''; ?>">Home</a></li>
                        <li><a href="/index.php?page=dashboard" class="<?php echo ($currentPage ?? '') === 'dashboard' ? 'active' : ''; ?>">My Plans</a></li>
                        <li><a href="/index.php?page=profile" class="<?php echo ($currentPage ?? '') === 'profile' ? 'active' : ''; ?>">My Profile</a></li>
                        <li><a href="/index.php?page=activities" class="<?php echo ($currentPage ?? '') === 'activities' ? 'active' : ''; ?>">Activities</a></li>
                        <li><a href="/index.php?page=meals" class="<?php echo ($currentPage ?? '') === 'meals' ? 'active' : ''; ?>">Meals</a></li>
                        <li><a href="/index.php?page=carshare" class="<?php echo ($currentPage ?? '') === 'carshare' ? 'active' : ''; ?>">Carshare</a></li>
                        <li><a href="/index.php?page=hosting" class="<?php echo ($currentPage ?? '') === 'hosting' ? 'active' : ''; ?>">Hosting</a></li>
                        <li><a href="/index.php?page=polls" class="<?php echo ($currentPage ?? '') === 'polls' ? 'active' : ''; ?>">Polls</a></li>
                        <li><a href="/index.php?page=hotels" class="<?php echo ($currentPage ?? '') === 'hotels' ? 'active' : ''; ?>">Hotels</a></li>
                        <?php if (isAdmin()): ?>
                        <li><a href="/index.php?page=admin" class="<?php echo ($currentPage ?? '') === 'admin' ? 'active' : ''; ?>">Admin</a></li>
                        <?php endif; ?>
                        <li><a href="/index.php?action=logout">Logout</a></li>
                    </ul>
                </nav>
                <?php elseif (isGuestMode()): ?>
                <nav class="nav">
                    <ul>
                        <li><a href="/index.php?page=home" class="<?php echo ($currentPage ?? '') === 'home' ? 'active' : ''; ?>">Home</a></li>
                        <li><a href="/index.php?page=activities" class="<?php echo ($currentPage ?? '') === 'activities' ? 'active' : ''; ?>">Activities</a></li>
                        <li><a href="/index.php?page=meals" class="<?php echo ($currentPage ?? '') === 'meals' ? 'active' : ''; ?>">Meals</a></li>
                        <li><a href="/index.php?page=carshare" class="<?php echo ($currentPage ?? '') === 'carshare' ? 'active' : ''; ?>">Carshare</a></li>
                        <li><a href="/index.php?page=hosting" class="<?php echo ($currentPage ?? '') === 'hosting' ? 'active' : ''; ?>">Hosting</a></li>
                        <li><a href="/index.php?page=polls" class="<?php echo ($currentPage ?? '') === 'polls' ? 'active' : ''; ?>">Polls</a></li>
                        <li><a href="/index.php?page=hotels" class="<?php echo ($currentPage ?? '') === 'hotels' ? 'active' : ''; ?>">Hotels</a></li>
                        <li><a href="/index.php?page=login" style="color: #FDDC62;">Login / Register</a></li>
                    </ul>
                </nav>
                <?php endif; ?>
                <!-- Login button already shown via btn-login-mobile above -->
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
