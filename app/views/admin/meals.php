<?php
$currentPage = 'admin';
ob_start();
?>

<div class="section">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 class="section-title">Manage Meals</h1>
            <div>
                <button onclick="openCreateModal()" class="btn btn-primary" style="margin-right: 1rem;">Add Meal</button>
                <a href="/index.php?page=admin" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>

        <?php if (empty($meals)): ?>
            <div class="card">
                <div class="card-body text-center">
                    <p>No meals created yet.</p>
                    <button onclick="openCreateModal()" class="btn btn-primary">Create First Meal</button>
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
                            <?php foreach ($meals as $meal): ?>
                                <tr style="border-bottom: 1px solid #dee2e6;">
                                    <td style="padding: 0.75rem;"><?php echo e($meal['title']); ?></td>
                                    <td style="padding: 0.75rem;"><?php echo e($meal['day']); ?></td>
                                    <td style="padding: 0.75rem;">
                                        <?php echo formatDisplayTime($meal['start_time']); ?> - 
                                        <?php echo formatDisplayTime($meal['end_time']); ?>
                                    </td>
                                    <td style="padding: 0.75rem; text-align: center;"><?php echo e($meal['max_capacity']); ?></td>
                                    <td style="padding: 0.75rem; text-align: center;">
                                        <?php
                                        $pct = $meal['max_capacity'] > 0 ? ($meal['booking_count'] / $meal['max_capacity']) * 100 : 100;
                                        $capClass = $pct >= 95 ? 'capacity-red' : ($pct >= 71 ? 'capacity-amber' : 'capacity-green');
                                        ?>
                                        <span class="<?php echo $capClass; ?>">
                                            <?php echo e($meal['booking_count']); ?> / <?php echo e($meal['max_capacity']); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 0.75rem; text-align: center;">£<?php echo number_format($meal['price'], 2); ?></td>
                                    <td style="padding: 0.75rem; text-align: center;">
                                        <?php if ($meal['requires_prepayment']): ?>
                                            <span style="color: #28a745;">✓</span>
                                        <?php else: ?>
                                            <span style="color: #999;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 0.75rem; text-align: right;">
                                        <button onclick='editMeal(<?php echo json_encode($meal); ?>)' 
                                                class="btn btn-sm btn-primary" style="margin-right: 0.5rem;">Edit</button>
                                        <button onclick="deleteMeal(<?php echo $meal['id']; ?>)" 
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
<div id="mealModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <span class="modal-close">&times;</span>
        <h2 id="modalTitle">Add Meal</h2>
        
        <form id="mealForm">
            <input type="hidden" id="meal_id" name="id">
            
            <div class="form-group">
                <label for="title">Meal Title *</label>
                <input type="text" id="title" name="title" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label for="link">Link (Optional)</label>
                <input type="url" id="link" name="link" class="form-control" placeholder="https://example.com">
                <small class="form-text">External link for more information</small>
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

            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="total_price">Total Price (£)</label>
                        <input type="number" id="total_price" name="total_price" class="form-control"
                               min="0" step="0.01" value="0.00">
                        <small class="form-text">Full price of the meal</small>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label for="deposit_amount">Deposit Amount (£)</label>
                        <input type="number" id="deposit_amount" name="deposit_amount" class="form-control"
                               min="0" step="0.01" value="0.00">
                        <small class="form-text">Deposit required (if any)</small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label style="display: flex; align-items: center; cursor: pointer;">
                            <input type="checkbox" id="prepayment_required" name="prepayment_required"
                                   value="1" style="margin-right: 0.5rem;">
                            Prepayment Required
                        </label>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label style="display: flex; align-items: center; cursor: pointer;">
                            <input type="checkbox" id="pay_on_arrival" name="pay_on_arrival"
                                   value="1" style="margin-right: 0.5rem;">
                            Can Pay on Arrival
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="confirmation_deadline">Confirmation Deadline (Optional)</label>
                <input type="datetime-local" id="confirmation_deadline" name="confirmation_deadline" class="form-control">
                <small class="form-text">Last date to confirm booking</small>
            </div>

            <div class="form-group">
                <label for="payment_deadline">Payment Deadline (Optional)</label>
                <input type="datetime-local" id="payment_deadline" name="payment_deadline" class="form-control">
                <small class="form-text">Last date to complete payment</small>
            </div>

            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" id="no_booking_required" name="no_booking_required" style="margin-right: 0.5rem;">
                            No booking required (attendees can mark attendance)
                        </label>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" id="booking_open" name="booking_open" checked style="margin-right: 0.5rem;">
                            Booking Open (uncheck to close bookings)
                        </label>
                    </div>
                </div>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary">Save Meal</button>
                <button type="button" onclick="modalManager.close('mealModal')" class="btn btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
let editingId = null;

function openCreateModal() {
    editingId = null;
    document.getElementById('modalTitle').textContent = 'Add Meal';
    document.getElementById('mealForm').reset();
    document.getElementById('meal_id').value = '';
    modalManager.open('mealModal');
}

function editMeal(meal) {
    editingId = meal.id;
    document.getElementById('modalTitle').textContent = 'Edit Meal';
    document.getElementById('meal_id').value = meal.id;
    document.getElementById('title').value = meal.title;
    document.getElementById('description').value = meal.description || '';
    document.getElementById('link').value = meal.link || '';
    document.getElementById('day').value = meal.day;
    document.getElementById('start_time').value = meal.start_time;
    document.getElementById('end_time').value = meal.end_time;
    document.getElementById('max_capacity').value = meal.max_capacity;
    document.getElementById('price').value = meal.price;
    document.getElementById('requires_prepayment').checked = meal.requires_prepayment == 1;
    document.getElementById('total_price').value = meal.total_price || '0.00';
    document.getElementById('deposit_amount').value = meal.deposit_amount || '0.00';
    document.getElementById('prepayment_required').checked = meal.prepayment_required == 1;
    document.getElementById('pay_on_arrival').checked = meal.pay_on_arrival == 1;
    document.getElementById('confirmation_deadline').value = meal.confirmation_deadline ? meal.confirmation_deadline.replace(' ', 'T').substring(0, 16) : '';
    document.getElementById('payment_deadline').value = meal.payment_deadline ? meal.payment_deadline.replace(' ', 'T').substring(0, 16) : '';
    document.getElementById('no_booking_required').checked = meal.no_booking_required == 1;
    document.getElementById('booking_open').checked = meal.booking_open != 0;
    modalManager.open('mealModal');
}

function deleteMeal(id) {
    if (!confirm('Are you sure you want to delete this meal? All bookings will be removed.')) {
        return;
    }
    
    fetch('/index.php?page=admin_meals&action=delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Meal deleted successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert(data.message || 'Failed to delete meal', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred', 'danger');
    });
}

document.getElementById('mealForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Disable submit button to prevent double submission
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';
    
    const formData = {
        title: this.title.value,
        description: this.description.value,
        link: this.link.value || null,
        day: this.day.value,
        start_time: this.start_time.value,
        end_time: this.end_time.value,
        max_capacity: parseInt(this.max_capacity.value),
        price: parseFloat(this.price.value),
        requires_prepayment: this.requires_prepayment.checked ? 1 : 0,
        total_price: parseFloat(this.total_price.value) || 0,
        deposit_amount: parseFloat(this.deposit_amount.value) || 0,
        prepayment_required: this.prepayment_required.checked ? 1 : 0,
        pay_on_arrival: this.pay_on_arrival.checked ? 1 : 0,
        confirmation_deadline: this.confirmation_deadline.value || null,
        payment_deadline: this.payment_deadline.value || null,
        no_booking_required: this.no_booking_required.checked ? 1 : 0,
        booking_open: this.booking_open.checked ? 1 : 0
    };
    
    if (editingId) {
        formData.id = editingId;
    }
    
    const action = editingId ? 'update' : 'create';
    
    fetch('/index.php?page=admin_meals&action=' + action, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
        
        if (data.success) {
            showAlert(data.message, 'success');
            modalManager.close('mealModal');
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert(data.message || 'Failed to save meal', 'danger');
        }
    })
    .catch(error => {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
        
        console.error('Error:', error);
        showAlert('An error occurred', 'danger');
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
