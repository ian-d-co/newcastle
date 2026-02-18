<?php
$currentPage = 'admin-event';
ob_start();
?>

<div class="section">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 class="section-title">Edit Event Information</h1>
            <a href="/index.php?page=admin" class="btn btn-secondary">← Back to Admin</a>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo e($success); ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo e($error); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">Event Details</div>
            <div class="card-body">
                <form method="POST" action="/index.php?page=admin-event&action=save" id="event-form">
                    <?php echo CSRF::field(); ?>
                    
                    <div class="form-group">
                        <label class="form-label" for="title">Event Title *</label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo e($event['title']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="description">Short Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required><?php echo e($event['description']); ?></textarea>
                        <small>This appears on the home page hero section</small>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label class="form-label" for="start_date">Start Date *</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo e($event['start_date']); ?>" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label class="form-label" for="end_date">End Date *</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo e($event['end_date']); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="location">Location *</label>
                        <input type="text" class="form-control" id="location" name="location" value="<?php echo e($event['location']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="content">Event Content (HTML allowed)</label>
                        <textarea class="form-control" id="content" name="content" rows="12"><?php echo e($event['content']); ?></textarea>
                        <small>This content appears on the home page. You can use HTML for formatting.</small>
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?php echo $event['is_active'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_active">
                                Event is Active
                            </label>
                        </div>
                        <small>Only active events are shown to users</small>
                    </div>

                    <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                        <button type="submit" class="btn btn-primary btn-lg">Save Changes</button>
                        <a href="/index.php?page=admin" class="btn btn-secondary btn-lg">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">Preview</div>
            <div class="card-body">
                <h3><?php echo e($event['title']); ?></h3>
                <p><strong><?php echo date('F j, Y', strtotime($event['start_date'])); ?> - <?php echo date('F j, Y', strtotime($event['end_date'])); ?></strong></p>
                <p><?php echo e($event['description']); ?></p>
                <div style="margin-top: 1rem; padding: 1rem; background: #FDE5B7; border-radius: 4px;">
                    <?php echo $event['content']; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
