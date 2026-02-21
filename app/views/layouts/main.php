<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="csrf-token" content="<?php echo e(generateCsrfToken()); ?>">
    <title><?php echo isset($pageTitle) ? e($pageTitle) . ' - ' : ''; ?>Dicksord Fest 2026 - Newcastle</title>
    <meta name="description" content="Dicksord Fest 2026 - Newcastle event management platform">
    <link rel="stylesheet" href="/css/styles.css?v=<?php echo time(); ?>">
    <script src="/js/app.js" defer></script>
    <script src="/js/scripts.js" defer></script>
    <script src="/js/modals.js" defer></script>
    <script src="/js/forms.js" defer></script>
    <script src="/js/charts.js" defer></script>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="/index.php" class="logo">Dicksord Fest 2026</a>

                <?php if (!isLoggedIn() && !isGuestMode()): ?>
                <!-- Mobile login button (visible without toggle for non-logged-in) -->
                <a href="/index.php?page=login" class="btn-login-mobile <?php echo ($currentPage ?? '') === 'login' ? 'active' : ''; ?>">Login</a>
                <?php endif; ?>

                <?php if (isLoggedIn()): ?>
                <!-- Desktop inline header links (hidden on mobile) -->
                <div class="header-links">
                    <a href="/index.php?page=home" class="<?php echo ($currentPage ?? '') === 'home' ? 'active' : ''; ?>">Home</a>
                    <a href="/index.php?page=dashboard" class="<?php echo ($currentPage ?? '') === 'dashboard' ? 'active' : ''; ?>">My Plans</a>
                    <a href="/index.php?page=profile" class="<?php echo ($currentPage ?? '') === 'profile' ? 'active' : ''; ?>">My Profile</a>
                    <a href="/index.php?page=whos_going" class="<?php echo ($currentPage ?? '') === 'whos_going' ? 'active' : ''; ?>">Who's Going</a>
                    <a href="/index.php?page=whos_doing_what" class="<?php echo ($currentPage ?? '') === 'whos_doing_what' ? 'active' : ''; ?>">Who's Doing What</a>
                </div>
                <?php endif; ?>

                <button class="nav-toggle" aria-label="Toggle navigation" aria-expanded="false">☰</button>

                <?php if (isLoggedIn()): ?>
                <nav class="nav" id="main-nav">
                    <!-- Mobile-only: primary header links repeated in hamburger -->
                    <div class="nav-mobile-header">
                        <ul>
                            <li><a href="/index.php?page=home" class="<?php echo ($currentPage ?? '') === 'home' ? 'active' : ''; ?>">Home</a></li>
                            <li><a href="/index.php?page=dashboard" class="<?php echo ($currentPage ?? '') === 'dashboard' ? 'active' : ''; ?>">My Plans</a></li>
                            <li><a href="/index.php?page=profile" class="<?php echo ($currentPage ?? '') === 'profile' ? 'active' : ''; ?>">My Profile</a></li>
                            <li><a href="/index.php?page=whos_going" class="<?php echo ($currentPage ?? '') === 'whos_going' ? 'active' : ''; ?>">Who's Going</a></li>
                            <li><a href="/index.php?page=whos_doing_what" class="<?php echo ($currentPage ?? '') === 'whos_doing_what' ? 'active' : ''; ?>">Who's Doing What</a></li>
                        </ul>
                    </div>
                    <!-- Things to Do -->
                    <span class="nav-section-label">Things to Do</span>
                    <ul>
                        <li><a href="/index.php?page=activities" class="<?php echo ($currentPage ?? '') === 'activities' ? 'active' : ''; ?>">Activities</a></li>
                        <li><a href="/index.php?page=meals" class="<?php echo ($currentPage ?? '') === 'meals' ? 'active' : ''; ?>">Meals</a></li>
                    </ul>
                    <!-- Travelling -->
                    <span class="nav-section-label">Travelling</span>
                    <ul>
                        <li><a href="/index.php?page=carshare" class="<?php echo ($currentPage ?? '') === 'carshare' ? 'active' : ''; ?>">Car Share</a></li>
                        <li><a href="/index.php?page=travel_advice" class="<?php echo ($currentPage ?? '') === 'travel_advice' ? 'active' : ''; ?>">Travel Advice</a></li>
                    </ul>
                    <!-- Staying Over -->
                    <span class="nav-section-label">Staying Over</span>
                    <ul>
                        <li><a href="/index.php?page=hosting" class="<?php echo ($currentPage ?? '') === 'hosting' ? 'active' : ''; ?>">Hosting</a></li>
                        <li><a href="/index.php?page=hotels" class="<?php echo ($currentPage ?? '') === 'hotels' ? 'active' : ''; ?>">Hotels</a></li>
                        <li><a href="/index.php?page=hotel_sharing" class="<?php echo ($currentPage ?? '') === 'hotel_sharing' ? 'active' : ''; ?>">Hotel Sharing</a></li>
                    </ul>
                    <!-- Other -->
                    <ul>
                        <li><a href="/index.php?page=polls" class="<?php echo ($currentPage ?? '') === 'polls' ? 'active' : ''; ?>">Polls</a></li>
                        <?php if (isAdmin()): ?>
                        <li><a href="/index.php?page=admin" class="<?php echo ($currentPage ?? '') === 'admin' ? 'active' : ''; ?>">Admin</a></li>
                        <?php endif; ?>
                        <li><a href="/index.php?action=logout">Logout</a></li>
                    </ul>
                </nav>
                <?php elseif (isGuestMode()): ?>
                <nav class="nav" id="main-nav">
                    <!-- Mobile-only header link for guest -->
                    <div class="nav-mobile-header">
                        <ul>
                            <li><a href="/index.php?page=home" class="<?php echo ($currentPage ?? '') === 'home' ? 'active' : ''; ?>">Home</a></li>
                        </ul>
                    </div>
                    <!-- Things to Do -->
                    <span class="nav-section-label">Things to Do</span>
                    <ul>
                        <li><a href="/index.php?page=activities" class="<?php echo ($currentPage ?? '') === 'activities' ? 'active' : ''; ?>">Activities</a></li>
                        <li><a href="/index.php?page=meals" class="<?php echo ($currentPage ?? '') === 'meals' ? 'active' : ''; ?>">Meals</a></li>
                    </ul>
                    <!-- Travelling -->
                    <span class="nav-section-label">Travelling</span>
                    <ul>
                        <li><a href="/index.php?page=carshare" class="<?php echo ($currentPage ?? '') === 'carshare' ? 'active' : ''; ?>">Car Share</a></li>
                    </ul>
                    <!-- Staying Over -->
                    <span class="nav-section-label">Staying Over</span>
                    <ul>
                        <li><a href="/index.php?page=hosting" class="<?php echo ($currentPage ?? '') === 'hosting' ? 'active' : ''; ?>">Hosting</a></li>
                        <li><a href="/index.php?page=hotels" class="<?php echo ($currentPage ?? '') === 'hotels' ? 'active' : ''; ?>">Hotels</a></li>
                    </ul>
                    <ul>
                        <li><a href="/index.php?page=polls" class="<?php echo ($currentPage ?? '') === 'polls' ? 'active' : ''; ?>">Polls</a></li>
                        <li><a href="/index.php?page=login" style="color: #FDDC62;">Login / Register</a></li>
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
            <p>November 20-22, 2026</p>
        </div>
    </footer>

    <!-- Interest "Who?" Modal (shared across pages) -->
    <div class="modal" id="interest-who-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="interest-who-title">Who's Interested?</h3>
                <button class="modal-close" onclick="modalManager.close('interest-who-modal')" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body">
                <!-- Tabs -->
                <div style="display: flex; border-bottom: 2px solid #FDDC62; margin-bottom: 1rem;">
                    <button class="interest-who-tab active" data-tab="interested"
                            onclick="switchInterestTab('interested')"
                            style="padding: 0.5rem 1rem; border: none; background: none; cursor: pointer; font-weight: 600; color: #613704; border-bottom: 2px solid #613704; margin-bottom: -2px;">
                        👍 Interested
                    </button>
                    <button class="interest-who-tab" data-tab="maybe"
                            onclick="switchInterestTab('maybe')"
                            style="padding: 0.5rem 1rem; border: none; background: none; cursor: pointer; font-weight: 500; color: #666; border-bottom: 2px solid transparent; margin-bottom: -2px;">
                        🤔 Maybe
                    </button>
                    <button class="interest-who-tab" data-tab="not_interested"
                            onclick="switchInterestTab('not_interested')"
                            style="padding: 0.5rem 1rem; border: none; background: none; cursor: pointer; font-weight: 500; color: #666; border-bottom: 2px solid transparent; margin-bottom: -2px;">
                        👎 Not Interested
                    </button>
                </div>
                <div id="interest-who-loading" style="text-align: center; padding: 1rem; display: none;">Loading...</div>
                <div id="interest-who-content">
                    <div id="interest-tab-interested" class="interest-tab-panel">
                        <ul id="interest-list-interested" style="list-style: none; padding: 0; margin: 0;"></ul>
                    </div>
                    <div id="interest-tab-maybe" class="interest-tab-panel" style="display: none;">
                        <ul id="interest-list-maybe" style="list-style: none; padding: 0; margin: 0;"></ul>
                    </div>
                    <div id="interest-tab-not_interested" class="interest-tab-panel" style="display: none;">
                        <ul id="interest-list-not_interested" style="list-style: none; padding: 0; margin: 0;"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    var _interestWhoCurrentTab = 'interested';

    window.openInterestWhoModal = function(itemType, itemId, itemName) {
        document.getElementById('interest-who-title').textContent = 'Who\'s interested? — ' + itemName;
        document.getElementById('interest-who-loading').style.display = 'block';
        document.getElementById('interest-who-content').style.display = 'none';
        modalManager.open('interest-who-modal');
        switchInterestTab('interested');

        fetch('/api/interest-who.php?item_type=' + encodeURIComponent(itemType) + '&item_id=' + encodeURIComponent(itemId))
            .then(function(r) { return r.json(); })
            .then(function(data) {
                document.getElementById('interest-who-loading').style.display = 'none';
                document.getElementById('interest-who-content').style.display = 'block';
                if (data.success) {
                    ['interested', 'maybe', 'not_interested'].forEach(function(level) {
                        var ul = document.getElementById('interest-list-' + level);
                        ul.innerHTML = '';
                        var names = data.data[level] || [];
                        if (names.length === 0) {
                            var li = document.createElement('li');
                            li.style.color = '#999';
                            li.style.padding = '0.5rem 0';
                            li.textContent = 'Nobody yet.';
                            ul.appendChild(li);
                        } else {
                            names.forEach(function(name) {
                                var li = document.createElement('li');
                                li.style.padding = '0.375rem 0';
                                li.style.borderBottom = '1px solid #f0f0f0';
                                li.textContent = name;
                                ul.appendChild(li);
                            });
                        }
                    });
                } else {
                    document.getElementById('interest-who-content').innerHTML = '<p style="color:#dc3545;">Failed to load data.</p>';
                }
            })
            .catch(function() {
                document.getElementById('interest-who-loading').style.display = 'none';
                document.getElementById('interest-who-content').innerHTML = '<p style="color:#dc3545;">An error occurred.</p>';
                document.getElementById('interest-who-content').style.display = 'block';
            });
    };

    window.switchInterestTab = function(tab) {
        _interestWhoCurrentTab = tab;
        ['interested', 'maybe', 'not_interested'].forEach(function(t) {
            document.getElementById('interest-tab-' + t).style.display = t === tab ? 'block' : 'none';
            var btn = document.querySelector('.interest-who-tab[data-tab="' + t + '"]');
            if (btn) {
                if (t === tab) {
                    btn.style.fontWeight = '600';
                    btn.style.color = '#613704';
                    btn.style.borderBottomColor = '#613704';
                } else {
                    btn.style.fontWeight = '500';
                    btn.style.color = '#666';
                    btn.style.borderBottomColor = 'transparent';
                }
            }
        });
    };
    </script>

</body>
</html>
