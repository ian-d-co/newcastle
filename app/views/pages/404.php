<?php
$currentPage = '404';
$pageTitle = '404 - Page Not Found';
ob_start();
?>

<div class="section">
    <div class="container" style="max-width: 600px; text-align: center; padding: 4rem 1rem;">
        <h1 style="font-size: 6rem; margin: 0; color: #D46300;">404</h1>
        <h2 style="color: #613704; margin-top: 1rem;">Page Not Found</h2>
        <p style="font-size: 1.125rem; margin: 2rem 0; color: #545454;">
            Sorry, the page you're looking for doesn't exist or has been moved.
        </p>
        
        <div style="margin-top: 3rem;">
            <a href="/index.php?page=home" class="btn btn-primary btn-lg" style="margin: 0.5rem;">
                Go to Homepage
            </a>
            <?php if (isLoggedIn()): ?>
            <a href="/index.php?page=dashboard" class="btn btn-secondary btn-lg" style="margin: 0.5rem;">
                Go to Dashboard
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
