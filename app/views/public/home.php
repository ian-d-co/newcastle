<?php
$currentPage = 'home';
ob_start();
?>

<!-- Hero Section -->
<div class="hero">
    <div class="container">
        <div class="hero-content">
            <h1><?php echo e($event['title']); ?></h1>
            <?php if (!empty($event['description'])): ?>
            <p><?php echo e($event['description']); ?></p>
            <?php endif; ?>
            <p><strong><?php echo formatDisplayDate($event['start_date']); ?> - <?php echo formatDisplayDate($event['end_date']); ?></strong></p>
            <?php if (!isLoggedIn()): ?>
            <div class="hero-actions" style="display: flex; gap: 1rem; justify-content: center; margin-top: 1.5rem; flex-wrap: wrap;">
                <a href="/index.php?page=login" class="btn btn-primary btn-lg">
                    Login
                </a>
                <a href="/index.php?page=register" class="btn btn-accent btn-lg">
                    Register
                </a>
            </div>
            <div style="text-align: center; margin-top: 1rem;">
                <a href="/index.php?page=home&view=guest" style="color: #FDDC62; text-decoration: underline;">
                    or continue as guest
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Event Information -->
<div class="section">
    <div class="container">
        <div class="card">
            <div class="card-body">
                <?php echo $event['content']; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
