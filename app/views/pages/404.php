<?php
$pageTitle = '404 - Page Not Found';
ob_start();
?>

<div class="section">
    <div class="container">
        <div class="card text-center" style="max-width: 600px; margin: 3rem auto;">
            <div class="card-body">
                <h1 style="font-size: 6rem; color: #D46300; margin: 0;">404</h1>
                <h2 style="color: #613704; margin-top: 1rem;">Page Not Found</h2>
                <p style="color: #545454; margin-top: 1rem; font-size: 1.125rem;">
                    Sorry, the page you're looking for doesn't exist or has been moved.
                </p>
                <div style="margin-top: 2rem;">
                    <a href="/index.php" class="btn btn-primary btn-lg">Go to Homepage</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include BASE_PATH . '/app/views/layouts/main.php';
?>
