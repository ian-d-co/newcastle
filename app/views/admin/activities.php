<?php
$currentPage = 'admin';
ob_start();
?>

<div class="section">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 class="section-title">Manage Activities</h1>
            <div>
                <button onclick="openCreateModal()" class="btn btn-primary" style="margin-right: 1rem;">Add Activity</button>
                <a href="/index.php?page=admin" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>

        <?php if (empty($activities)): ?>
            <div class="card">
                <div class="card-body text-center">
                    <p>No activities created yet.</p>
                    <button onclick="openCreateModal()" class="btn btn-primary">Create First Activity</button>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid #dee2e6;">
                                <th style="padding: 0.75rem; text-align: left;">Title</th>
                                <th style="padding: 0.75rem; text-align: left;">Day</th>
                                <th style="padding: 0.75rem; text-align: left;">Time</th>
                                <th style="padding: 0.75rem; text-align: center;">Capacity</th>
                                <th style="padding: 0.75rem; text-align: center;">Bookings</th>
                                <th style="padding: 0.75rem; text-align: center;">Price</th>
                                <th style="padding: 0.75rem; text-align: center;">Prepay</th>
                                <th style="padding: 0.75rem; text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activities as $activity): ?>
                                <tr style="border-bottom: 1px solid #dee2e6;">
                                    <td style="padding: 0.75rem;"><?php echo e($activity['title']); ?></td>
                                    <td style="padding: 0.75rem;"><?php echo e($activity['day']); ?></td>
                                    <td style="padding: 0.75rem;">
                                        <?php echo formatDisplayTime($activity['start_time']); ?> - 
                                        <?php echo formatDisplayTime($activity['end_time']); ?>
                                    </td>
                                    <td style="padding: 0.75rem; text-align: center;"><?php echo e($activity['max_capacity']); ?></td>
                                    <td style="padding: 0.75rem; text-align: center;">
                                        <?php echo e($activity['booking_count']); ?> / <?php echo e($activity['max_capacity']); ?>
                                    </td>
                                    <td style="padding: 0.75rem; text-align: center;">£<?php echo number_format($activity['price'], 2); ?></td>
                                    <td style="padding: 0.75rem; text-align: center;">
                                        <?php if ($activity['requires_prepayment']): ?>
                                            <span style="color: #28a745;">✓</span>
                                        <?php else: ?>
                                            <span style="color: #999;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 0.75rem; text-align: right;">
                                        <button onclick='editActivity(<?php echo json_encode($activity); ?>)' 
                                                class="btn btn-sm btn-primary" style="margin-right: 0.5rem;">Edit</button>
                                        <button onclick="deleteActivity(<?php echo $activity['id']; ?>)" 
                                                class="btn btn-sm btn-danger">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="activityModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <span class="modal-close">&times;</span>
        <h2 id="modalTitle">Add Activity</h2>
        
        <form id="activityForm">
            <input type="hidden" id="activity_id" name="id">
            
            <div class="form-group">
                <label for="title">Activity Title *</label>
                <input type="text" id="title" name="title" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3"></textarea>
            </div>
            
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="day">Day *</label>
                        <select id="day" name="day" class="form-control" required>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                            <option value="Sunday">Sunday</option>
                        </select>
                    </div>
                </div>
                
                <div class="col">
                    <div class="form-group">
                        <label for="start_time">Start Time *</label>
                        <input type="time" id="start_time" name="start_time" class="form-control" required>
                    </div>
                </div>
                
                <div class="col">
                    <div class="form-group">
                        <label for="end_time">End Time *</label>
                        <input type="time" id="end_time" name="end_time" class="form-control" required>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="max_capacity">Max Capacity *</label>
                        <input type="number" id="max_capacity" name="max_capacity" class="form-control" 
                               min="1" value="20" required>
                    </div>
                </div>
                
                <div class="col">
                    <div class="form-group">
                        <label for="price">Price (£) *</label>
                        <input type="number" id="price" name="price" class="form-control" 
                               min="0" step="0.01" value="0.00" required>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; cursor: pointer;">
                    <input type="checkbox" id="requires_prepayment" name="requires_prepayment" 
                           value="1" style="margin-right: 0.5rem;">
                    Requires Prepayment
                </label>
                <small class="form-text">Check this if attendees must pay before booking</small>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary">Save Activity</button>
                <button type="button" onclick="modalManager.close('activityModal')" class="btn btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
let editingId = null;

function openCreateModal() {
    editingId = null;
    document.getElementById('modalTitle').textContent = 'Add Activity';
    document.getElementById('activityForm').reset();
    document.getElementById('activity_id').value = '';
    modalManager.open('activityModal');
}

function editActivity(activity) {
    editingId = activity.id;
    document.getElementById('modalTitle').textContent = 'Edit Activity';
    document.getElementById('activity_id').value = activity.id;
    document.getElementById('title').value = activity.title;
    document.getElementById('description').value = activity.description || '';
    document.getElementById('day').value = activity.day;
    document.getElementById('start_time').value = activity.start_time;
    document.getElementById('end_time').value = activity.end_time;
    document.getElementById('max_capacity').value = activity.max_capacity;
    document.getElementById('price').value = activity.price;
    document.getElementById('requires_prepayment').checked = activity.requires_prepayment == 1;
    modalManager.open('activityModal');
}

function deleteActivity(id) {
    if (!confirm('Are you sure you want to delete this activity? All bookings will be removed.')) {
        return;
    }
    
    fetch('/index.php?page=admin_activities&action=delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Activity deleted successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert(data.message || 'Failed to delete activity', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred', 'danger');
    });
}

document.getElementById('activityForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        title: this.title.value,
        description: this.description.value,
        day: this.day.value,
        start_time: this.start_time.value,
        end_time: this.end_time.value,
        max_capacity: parseInt(this.max_capacity.value),
        price: parseFloat(this.price.value),
        requires_prepayment: this.requires_prepayment.checked ? 1 : 0
    };
    
    if (editingId) {
        formData.id = editingId;
    }
    
    const action = editingId ? 'update' : 'create';
    
    fetch('/index.php?page=admin_activities&action=' + action, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            modalManager.close('activityModal');
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert(data.message || 'Failed to save activity', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred', 'danger');
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
