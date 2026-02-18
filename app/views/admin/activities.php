<?php
$currentPage = 'admin-activities';
ob_start();
?>

<div class="section">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 class="section-title">Activity Management</h1>
            <a href="/index.php?page=admin" class="btn btn-secondary">← Back to Admin</a>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo e($success); ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo e($error); ?></div>
        <?php endif; ?>

        <!-- Add New Activity -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">Add New Activity</div>
            <div class="card-body">
                <form method="POST" action="/index.php?page=admin-activities&action=create">
                    <?php echo CSRF::field(); ?>
                    
                    <div class="form-group">
                        <label class="form-label" for="title">Activity Title *</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="description">Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label class="form-label" for="activity_date">Date *</label>
                                <input type="date" class="form-control" id="activity_date" name="activity_date" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label class="form-label" for="start_time">Start Time *</label>
                                <input type="time" class="form-control" id="start_time" name="start_time" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label class="form-label" for="end_time">End Time</label>
                                <input type="time" class="form-control" id="end_time" name="end_time">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label class="form-label" for="max_capacity">Max Capacity *</label>
                                <input type="number" class="form-control" id="max_capacity" name="max_capacity" min="1" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label class="form-label" for="payment_required">Payment Required</label>
                                <select class="form-control" id="payment_required" name="payment_required" onchange="togglePaymentAmount(this)">
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group" id="payment-amount-group" style="display: none;">
                                <label class="form-label" for="payment_amount">Payment Amount (£)</label>
                                <input type="number" class="form-control" id="payment_amount" name="payment_amount" min="0" step="0.01">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg">Add Activity</button>
                </form>
            </div>
        </div>

        <!-- Activities List -->
        <div class="card">
            <div class="card-header">
                Existing Activities (<?php echo count($activities); ?>)
            </div>
            <div class="card-body">
                <?php if (empty($activities)): ?>
                    <p class="text-center" style="padding: 2rem;">No activities created yet.</p>
                <?php else: ?>
                    <?php foreach ($activities as $activity): ?>
                        <div class="card" style="margin-bottom: 1rem;">
                            <div class="card-body">
                                <div style="display: flex; justify-content: space-between; align-items: start;">
                                    <div style="flex: 1;">
                                        <h4 style="margin: 0 0 0.5rem 0;"><?php echo e($activity['title']); ?></h4>
                                        <p style="margin: 0 0 0.5rem 0; color: #545454;"><?php echo e($activity['description']); ?></p>
                                        <div style="display: flex; flex-wrap: wrap; gap: 1rem; font-size: 0.875rem; color: #545454;">
                                            <span>📅 <?php echo date('D, M j, Y', strtotime($activity['activity_date'])); ?></span>
                                            <span>🕐 <?php echo date('g:i A', strtotime($activity['start_time'])); ?><?php echo $activity['end_time'] ? ' - ' . date('g:i A', strtotime($activity['end_time'])) : ''; ?></span>
                                            <span>👥 <?php echo $activity['current_bookings']; ?> / <?php echo $activity['max_capacity']; ?></span>
                                            <?php if ($activity['payment_required']): ?>
                                                <span>💰 £<?php echo number_format($activity['payment_amount'], 2); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                        <button class="btn btn-sm btn-secondary" onclick="viewBookings(<?php echo $activity['id']; ?>)">Bookings</button>
                                        <button class="btn btn-sm btn-primary" onclick="editActivity(<?php echo $activity['id']; ?>)">Edit</button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteActivity(<?php echo $activity['id']; ?>, '<?php echo e($activity['title']); ?>')">Delete</button>
                                    </div>
                                </div>

                                <!-- Bookings for this activity -->
                                <div id="bookings-<?php echo $activity['id']; ?>" style="display: none; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #dee2e6;">
                                    <?php 
                                    $bookings = $activityModel->getBookings($activity['id']);
                                    if (empty($bookings)): 
                                    ?>
                                        <p style="margin: 0;">No bookings yet.</p>
                                    <?php else: ?>
                                        <table style="width: 100%; font-size: 0.875rem;">
                                            <thead>
                                                <tr style="border-bottom: 1px solid #dee2e6;">
                                                    <th style="padding: 0.5rem; text-align: left;">User</th>
                                                    <th style="padding: 0.5rem; text-align: left;">Booked</th>
                                                    <?php if ($activity['payment_required']): ?>
                                                        <th style="padding: 0.5rem; text-align: center;">Payment</th>
                                                    <?php endif; ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($bookings as $booking): ?>
                                                    <tr style="border-bottom: 1px solid #eee;">
                                                        <td style="padding: 0.5rem;"><?php echo e($booking['discord_name']); ?></td>
                                                        <td style="padding: 0.5rem;"><?php echo date('M j, Y', strtotime($booking['created_at'])); ?></td>
                                                        <?php if ($activity['payment_required']): ?>
                                                            <td style="padding: 0.5rem; text-align: center;">
                                                                <form method="POST" action="/index.php?page=admin-activities&action=payment" style="display: inline;">
                                                                    <?php echo CSRF::field(); ?>
                                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                                    <input type="hidden" name="status" value="<?php echo $booking['payment_status'] === 'received' ? 'pending' : 'received'; ?>">
                                                                    <label>
                                                                        <input type="checkbox" <?php echo $booking['payment_status'] === 'received' ? 'checked' : ''; ?> onchange="this.form.submit()">
                                                                        <?php echo $booking['payment_status'] === 'received' ? 'Received' : 'Pending'; ?>
                                                                    </label>
                                                                </form>
                                                            </td>
                                                        <?php endif; ?>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Edit Activity Modal -->
<div class="modal" id="edit-activity-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Edit Activity</h3>
            <button class="modal-close" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="edit-activity-form" method="POST" action="/index.php?page=admin-activities&action=edit">
                <?php echo CSRF::field(); ?>
                <input type="hidden" id="edit-activity-id" name="activity_id">
                
                <div class="form-group">
                    <label class="form-label" for="edit-title">Activity Title *</label>
                    <input type="text" class="form-control" id="edit-title" name="title" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="edit-description">Description *</label>
                    <textarea class="form-control" id="edit-description" name="description" rows="3" required></textarea>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label class="form-label" for="edit-activity-date">Date *</label>
                            <input type="date" class="form-control" id="edit-activity-date" name="activity_date" required>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label class="form-label" for="edit-start-time">Start Time *</label>
                            <input type="time" class="form-control" id="edit-start-time" name="start_time" required>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label class="form-label" for="edit-end-time">End Time</label>
                            <input type="time" class="form-control" id="edit-end-time" name="end_time">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label class="form-label" for="edit-max-capacity">Max Capacity *</label>
                            <input type="number" class="form-control" id="edit-max-capacity" name="max_capacity" min="1" required>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label class="form-label" for="edit-payment-required">Payment Required</label>
                            <select class="form-control" id="edit-payment-required" name="payment_required" onchange="toggleEditPaymentAmount(this)">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group" id="edit-payment-amount-group">
                            <label class="form-label" for="edit-payment-amount">Payment Amount (£)</label>
                            <input type="number" class="form-control" id="edit-payment-amount" name="payment_amount" min="0" step="0.01">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg">Save Changes</button>
            </form>
        </div>
    </div>
</div>

<script>
const activitiesData = <?php echo json_encode($activities); ?>;

function togglePaymentAmount(select) {
    const group = document.getElementById('payment-amount-group');
    group.style.display = select.value === '1' ? 'block' : 'none';
}

function toggleEditPaymentAmount(select) {
    const group = document.getElementById('edit-payment-amount-group');
    group.style.display = select.value === '1' ? 'block' : 'none';
}

function viewBookings(activityId) {
    const bookingsDiv = document.getElementById('bookings-' + activityId);
    bookingsDiv.style.display = bookingsDiv.style.display === 'none' ? 'block' : 'none';
}

function editActivity(activityId) {
    const activity = activitiesData.find(a => a.id == activityId);
    if (!activity) return;
    
    document.getElementById('edit-activity-id').value = activity.id;
    document.getElementById('edit-title').value = activity.title;
    document.getElementById('edit-description').value = activity.description;
    document.getElementById('edit-activity-date').value = activity.activity_date;
    document.getElementById('edit-start-time').value = activity.start_time;
    document.getElementById('edit-end-time').value = activity.end_time || '';
    document.getElementById('edit-max-capacity').value = activity.max_capacity;
    document.getElementById('edit-payment-required').value = activity.payment_required;
    document.getElementById('edit-payment-amount').value = activity.payment_amount || '';
    
    toggleEditPaymentAmount(document.getElementById('edit-payment-required'));
    
    modalManager.open('edit-activity-modal');
}

function deleteActivity(activityId, title) {
    if (!confirm(`Are you sure you want to delete "${title}"? This will also delete all bookings for this activity.`)) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/index.php?page=admin-activities&action=delete';
    
    const csrfField = document.createElement('input');
    csrfField.type = 'hidden';
    csrfField.name = '_csrf_token';
    csrfField.value = document.querySelector('meta[name="csrf-token"]').content;
    form.appendChild(csrfField);
    
    const activityIdField = document.createElement('input');
    activityIdField.type = 'hidden';
    activityIdField.name = 'activity_id';
    activityIdField.value = activityId;
    form.appendChild(activityIdField);
    
    document.body.appendChild(form);
    form.submit();
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
