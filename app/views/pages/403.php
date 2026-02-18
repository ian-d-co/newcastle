<?php
$pageTitle = '403 - Access Forbidden';
ob_start();
?>

<div class="section">
    <div class="container">
        <div class="card text-center" style="max-width: 600px; margin: 3rem auto;">
            <div class="card-body">
                <h1 style="font-size: 6rem; color: #D46300; margin: 0;">403</h1>
                <h2 style="color: #613704; margin-top: 1rem;">Access Forbidden</h2>
                <p style="color: #545454; margin-top: 1rem; font-size: 1.125rem;">
                    You don't have permission to access this page.
                </p>
                <div style="margin-top: 2rem;">
                    <a href="/index.php" class="btn btn-primary btn-lg">Go to Homepage</a>
                    <?php if (isLoggedIn()): ?>
                    <a href="/index.php?page=dashboard" class="btn btn-secondary btn-lg">Go to Dashboard</a>
                    <?php else: ?>
                    <a href="/index.php?page=login" class="btn btn-secondary btn-lg">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include BASE_PATH . '/app/views/layouts/main.php';
?>
