<?php
$currentPage = 'admin-meals';
ob_start();
?>

<div class="section">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 class="section-title">Meal Management</h1>
            <a href="/index.php?page=admin" class="btn btn-secondary">← Back to Admin</a>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo e($success); ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo e($error); ?></div>
        <?php endif; ?>

        <!-- Add New Meal -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">Add New Meal</div>
            <div class="card-body">
                <form method="POST" action="/index.php?page=admin-meals&action=create">
                    <?php echo CSRF::field(); ?>
                    
                    <div class="form-group">
                        <label class="form-label" for="title">Meal Title *</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="description">Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label class="form-label" for="day">Day *</label>
                                <select class="form-control" id="day" name="day" required>
                                    <option value="">Select a day...</option>
                                    <option value="Friday">Friday (Nov 20)</option>
                                    <option value="Saturday">Saturday (Nov 21)</option>
                                    <option value="Sunday">Sunday (Nov 22)</option>
                                </select>
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
                                <label class="form-label" for="end_time">End Time *</label>
                                <input type="time" class="form-control" id="end_time" name="end_time" required>
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
                                <label class="form-label" for="requires_prepayment">Payment Required</label>
                                <select class="form-control" id="requires_prepayment" name="requires_prepayment" onchange="togglePaymentAmount(this)">
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group" id="payment-amount-group" style="display: none;">
                                <label class="form-label" for="price">Payment Amount (£)</label>
                                <input type="number" class="form-control" id="price" name="price" min="0" step="0.01">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg">Add Meal</button>
                </form>
            </div>
        </div>

        <!-- Meals List -->
        <div class="card">
            <div class="card-header">
                Existing Meals (<?php echo count($meals); ?>)
            </div>
            <div class="card-body">
                <?php if (empty($meals)): ?>
                    <p class="text-center" style="padding: 2rem;">No meals created yet.</p>
                <?php else: ?>
                    <?php foreach ($meals as $meal): ?>
                        <div class="card" style="margin-bottom: 1rem;">
                            <div class="card-body">
                                <div style="display: flex; justify-content: space-between; align-items: start;">
                                    <div style="flex: 1;">
                                        <h4 style="margin: 0 0 0.5rem 0;"><?php echo e($meal['title']); ?></h4>
                                        <p style="margin: 0 0 0.5rem 0; color: #545454;"><?php echo e($meal['description']); ?></p>
                                        <div style="display: flex; flex-wrap: wrap; gap: 1rem; font-size: 0.875rem; color: #545454;">
                                            <span>📅 <?php echo e($meal['day']); ?> (<?php echo date('M j', strtotime($event['start_date'] . ' + ' . (($meal['day'] === 'Saturday') ? '1' : (($meal['day'] === 'Sunday') ? '2' : '0')) . ' days')); ?>)</span>
                                            <span>🕐 <?php echo date('g:i A', strtotime($meal['start_time'])); ?> - <?php echo date('g:i A', strtotime($meal['end_time'])); ?></span>
                                            <span>👥 <?php echo $meal['current_bookings']; ?> / <?php echo $meal['max_capacity']; ?></span>
                                            <?php if ($meal['requires_prepayment']): ?>
                                                <span>💰 £<?php echo number_format($meal['price'], 2); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                        <button class="btn btn-sm btn-secondary" onclick="viewBookings(<?php echo $meal['id']; ?>)">Bookings</button>
                                        <button class="btn btn-sm btn-primary" onclick="editMeal(<?php echo $meal['id']; ?>)">Edit</button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteMeal(<?php echo $meal['id']; ?>, '<?php echo e($meal['title']); ?>')">Delete</button>
                                    </div>
                                </div>

                                <!-- Bookings for this meal -->
                                <div id="bookings-<?php echo $meal['id']; ?>" style="display: none; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #dee2e6;">
                                    <?php 
                                    $bookings = $meal['bookings'] ?? [];
                                    if (empty($bookings)): 
                                    ?>
                                        <p style="margin: 0;">No bookings yet.</p>
                                    <?php else: ?>
                                        <table style="width: 100%; font-size: 0.875rem;">
                                            <thead>
                                                <tr style="border-bottom: 1px solid #dee2e6;">
                                                    <th style="padding: 0.5rem; text-align: left;">User</th>
                                                    <th style="padding: 0.5rem; text-align: left;">Dietary Requirements</th>
                                                    <th style="padding: 0.5rem; text-align: left;">Booked</th>
                                                    <?php if ($meal['requires_prepayment']): ?>
                                                        <th style="padding: 0.5rem; text-align: center;">Payment</th>
                                                    <?php endif; ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($bookings as $booking): ?>
                                                    <tr style="border-bottom: 1px solid #eee;">
                                                        <td style="padding: 0.5rem;"><?php echo e($booking['discord_name']); ?></td>
                                                        <td style="padding: 0.5rem;"><?php echo e($booking['dietary_requirements'] ?: 'None'); ?></td>
                                                        <td style="padding: 0.5rem;"><?php echo date('M j, Y', strtotime($booking['created_at'])); ?></td>
                                                        <?php if ($meal['requires_prepayment']): ?>
                                                            <td style="padding: 0.5rem; text-align: center;">
                                                                <form method="POST" action="/index.php?page=admin-meals&action=payment" style="display: inline;">
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

<!-- Edit Meal Modal -->
<div class="modal" id="edit-meal-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Edit Meal</h3>
            <button class="modal-close" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="edit-meal-form" method="POST" action="/index.php?page=admin-meals&action=edit">
                <?php echo CSRF::field(); ?>
                <input type="hidden" id="edit-meal-id" name="meal_id">
                
                <div class="form-group">
                    <label class="form-label" for="edit-title">Meal Title *</label>
                    <input type="text" class="form-control" id="edit-title" name="title" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="edit-description">Description *</label>
                    <textarea class="form-control" id="edit-description" name="description" rows="3" required></textarea>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label class="form-label" for="edit-day">Day *</label>
                            <select class="form-control" id="edit-day" name="day" required>
                                <option value="Friday">Friday (Nov 20)</option>
                                <option value="Saturday">Saturday (Nov 21)</option>
                                <option value="Sunday">Sunday (Nov 22)</option>
                            </select>
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
                            <label class="form-label" for="edit-end-time">End Time *</label>
                            <input type="time" class="form-control" id="edit-end-time" name="end_time" required>
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
                            <label class="form-label" for="edit-requires-prepayment">Payment Required</label>
                            <select class="form-control" id="edit-requires-prepayment" name="requires_prepayment" onchange="toggleEditPaymentAmount(this)">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group" id="edit-payment-amount-group">
                            <label class="form-label" for="edit-price">Payment Amount (£)</label>
                            <input type="number" class="form-control" id="edit-price" name="price" min="0" step="0.01">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg">Save Changes</button>
            </form>
        </div>
    </div>
</div>

<script>
const mealsData = <?php echo json_encode($meals); ?>;

function togglePaymentAmount(select) {
    const group = document.getElementById('payment-amount-group');
    group.style.display = select.value === '1' ? 'block' : 'none';
}

function toggleEditPaymentAmount(select) {
    const group = document.getElementById('edit-payment-amount-group');
    group.style.display = select.value === '1' ? 'block' : 'none';
}

function viewBookings(mealId) {
    const bookingsDiv = document.getElementById('bookings-' + mealId);
    bookingsDiv.style.display = bookingsDiv.style.display === 'none' ? 'block' : 'none';
}

function editMeal(mealId) {
    const meal = mealsData.find(m => m.id == mealId);
    if (!meal) return;
    
    document.getElementById('edit-meal-id').value = meal.id;
    document.getElementById('edit-title').value = meal.title;
    document.getElementById('edit-description').value = meal.description;
    document.getElementById('edit-day').value = meal.day;
    document.getElementById('edit-start-time').value = meal.start_time;
    document.getElementById('edit-end-time').value = meal.end_time || '';
    document.getElementById('edit-max-capacity').value = meal.max_capacity;
    document.getElementById('edit-requires-prepayment').value = meal.requires_prepayment;
    document.getElementById('edit-price').value = meal.price || '';
    
    toggleEditPaymentAmount(document.getElementById('edit-requires-prepayment'));
    
    modalManager.open('edit-meal-modal');
}

function deleteMeal(mealId, title) {
    if (!confirm(`Are you sure you want to delete "${title}"? This will also delete all bookings for this meal.`)) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/index.php?page=admin-meals&action=delete';
    
    const csrfField = document.createElement('input');
    csrfField.type = 'hidden';
    csrfField.name = '_csrf_token';
    csrfField.value = document.querySelector('meta[name="csrf-token"]').content;
    form.appendChild(csrfField);
    
    const mealIdField = document.createElement('input');
    mealIdField.type = 'hidden';
    mealIdField.name = 'meal_id';
    mealIdField.value = mealId;
    form.appendChild(mealIdField);
    
    document.body.appendChild(form);
    form.submit();
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
