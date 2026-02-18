<?php
$currentPage = '403';
$pageTitle = 'Access Denied';
ob_start();
?>

<div class="section">
    <div class="container">
        <div class="card text-center">
            <div class="card-body">
                <h1 class="text-primary" style="font-size: 5rem; margin-bottom: 1rem;">403</h1>
                <h2 style="margin-bottom: 1.5rem;">Access Denied</h2>
                <p style="margin-bottom: 2rem; font-size: 1.1rem;">
                    Sorry, you don't have permission to access this page.
                </p>
                <a href="/index.php?page=home" class="btn btn-primary btn-lg">
                    Go to Homepage
                </a>
                <?php if (isLoggedIn()): ?>
                <a href="/index.php?page=dashboard" class="btn btn-secondary btn-lg">
                    Go to Dashboard
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
