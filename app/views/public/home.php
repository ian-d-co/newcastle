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
