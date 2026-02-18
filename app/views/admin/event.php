<?php
$currentPage = 'admin';
ob_start();
?>

<div class="section">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 class="section-title">Edit Event</h1>
            <a href="/index.php?page=admin" class="btn btn-secondary">Back to Dashboard</a>
        </div>

        <div class="card">
            <div class="card-body">
                <form id="eventForm">
                    <input type="hidden" name="id" value="<?php echo e($event['id']); ?>">
                    
                    <div class="form-group">
                        <label for="title">Event Title *</label>
                        <input type="text" id="title" name="title" class="form-control" 
                               value="<?php echo e($event['title']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Short Description</label>
                        <textarea id="description" name="description" class="form-control" 
                                  rows="3"><?php echo e($event['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="start_date">Start Date *</label>
                                <input type="date" id="start_date" name="start_date" class="form-control" 
                                       value="<?php echo e($event['start_date']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="col">
                            <div class="form-group">
                                <label for="end_date">End Date *</label>
                                <input type="date" id="end_date" name="end_date" class="form-control" 
                                       value="<?php echo e($event['end_date']); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="location">Location *</label>
                        <input type="text" id="location" name="location" class="form-control" 
                               value="<?php echo e($event['location']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="content">Full Event Content (HTML allowed)</label>
                        <textarea id="content" name="content" class="form-control" 
                                  rows="10"><?php echo e($event['content'] ?? ''); ?></textarea>
                        <small class="form-text">You can use HTML tags for formatting.</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('eventForm');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            id: form.id.value,
            title: form.title.value,
            description: form.description.value,
            start_date: form.start_date.value,
            end_date: form.end_date.value,
            location: form.location.value,
            content: form.content.value
        };
        
        fetch('/index.php?page=admin_event&action=update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Event updated successfully!', 'success');
            } else {
                showAlert(data.message || 'Failed to update event', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred while updating the event', 'danger');
        });
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
