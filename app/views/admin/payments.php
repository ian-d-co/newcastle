<?php
$currentPage = 'admin';
ob_start();
?>

<div class="section">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 class="section-title">Payment Management</h1>
            <a href="/index.php?page=admin" class="btn btn-secondary">Back to Dashboard</a>
        </div>

        <?php if (empty($userPayments)): ?>
            <div class="card">
                <div class="card-body text-center">
                    <p>No bookings with payment information yet.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($userPayments as $index => $user): ?>
                <?php $outstanding = $user['total_due'] - $user['total_paid']; ?>
                <div class="card" style="margin-bottom: 1rem; border: 1px solid #dee2e6;">
                    <div class="card-header"
                         style="cursor: pointer; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa;"
                         onclick="togglePaymentUser(<?php echo $index; ?>)"
                         tabindex="0" role="button"
                         aria-expanded="false" id="payment-header-<?php echo $index; ?>"
                         aria-controls="payment-user-<?php echo $index; ?>"
                         onkeydown="if(event.key==='Enter'||event.key===' '){togglePaymentUser(<?php echo $index; ?>);}">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <span class="toggle-icon" id="toggle-icon-<?php echo $index; ?>" style="font-size: 0.8rem; transition: transform 0.3s;">▶</span>
                            <div>
                                <strong><?php echo e($user['discord_name']); ?></strong>
                                <?php if ($user['name'] !== $user['discord_name']): ?>
                                    <span style="color: #666; font-size: 0.9rem;"> (<?php echo e($user['name']); ?>)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div style="display: flex; gap: 1rem; align-items: center;">
                            <span style="font-size: 0.9rem;">Due: <strong>£<?php echo number_format($user['total_due'], 2); ?></strong></span>
                            <span style="font-size: 0.9rem;">Paid: <strong>£<?php echo number_format($user['total_paid'], 2); ?></strong></span>
                            <?php if ($outstanding > 0): ?>
                                <span class="badge badge-warning">Outstanding: £<?php echo number_format($outstanding, 2); ?></span>
                            <?php elseif ($outstanding == 0 && $user['total_due'] > 0): ?>
                                <span class="badge badge-success">✓ Paid</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div id="payment-user-<?php echo $index; ?>" style="display: none;">
                    <div class="card-body">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 2px solid #dee2e6;">
                                    <th style="padding: 0.5rem; text-align: left;">Item</th>
                                    <th style="padding: 0.5rem; text-align: left;">Type</th>
                                    <th style="padding: 0.5rem; text-align: left;">Day</th>
                                    <th style="padding: 0.5rem; text-align: center;">Price</th>
                                    <th style="padding: 0.5rem; text-align: center;">Amount Due</th>
                                    <th style="padding: 0.5rem; text-align: center;">Amount Paid</th>
                                    <th style="padding: 0.5rem; text-align: center;">Status</th>
                                    <th style="padding: 0.5rem; text-align: right;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($user['bookings'] as $booking): ?>
                                    <tr style="border-bottom: 1px solid #dee2e6;" id="booking-row-<?php echo e($booking['booking_type']); ?>-<?php echo (int)$booking['booking_id']; ?>">
                                        <td style="padding: 0.5rem;"><?php echo e($booking['item_title']); ?></td>
                                        <td style="padding: 0.5rem;"><?php echo e(ucfirst($booking['booking_type'])); ?></td>
                                        <td style="padding: 0.5rem;"><?php echo e($booking['day']); ?></td>
                                        <td style="padding: 0.5rem; text-align: center;">£<?php echo number_format($booking['item_price'], 2); ?></td>
                                        <td style="padding: 0.5rem; text-align: center;">
                                            <input type="number" step="0.01" min="0"
                                                   class="form-control form-control-sm"
                                                   style="width: 80px; display: inline-block;"
                                                   value="<?php echo number_format($booking['amount_due'] ?: $booking['item_price'], 2); ?>"
                                                   id="amount_due-<?php echo e($booking['booking_type']); ?>-<?php echo (int)$booking['booking_id']; ?>">
                                        </td>
                                        <td style="padding: 0.5rem; text-align: center;">
                                            <input type="number" step="0.01" min="0"
                                                   class="form-control form-control-sm"
                                                   style="width: 80px; display: inline-block;"
                                                   value="<?php echo number_format($booking['amount_paid'] ?? 0, 2); ?>"
                                                   id="amount_paid-<?php echo e($booking['booking_type']); ?>-<?php echo (int)$booking['booking_id']; ?>">
                                        </td>
                                        <td style="padding: 0.5rem; text-align: center;">
                                            <select class="form-control form-control-sm"
                                                    style="width: 120px; display: inline-block;"
                                                    id="payment_status-<?php echo e($booking['booking_type']); ?>-<?php echo (int)$booking['booking_id']; ?>">
                                                <?php foreach (['pending', 'paid', 'not_required'] as $status): ?>
                                                    <option value="<?php echo $status; ?>" <?php echo ($booking['payment_status'] ?? 'pending') === $status ? 'selected' : ''; ?>>
                                                        <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td style="padding: 0.5rem; text-align: right;">
                                            <button class="btn btn-sm btn-primary"
                                                    onclick="savePayment('<?php echo e($booking['booking_type']); ?>', <?php echo (int)$booking['booking_id']; ?>)">
                                                Save
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function togglePaymentUser(index) {
    const content = document.getElementById('payment-user-' + index);
    const icon = document.getElementById('toggle-icon-' + index);
    const header = document.getElementById('payment-header-' + index);

    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(90deg)';
        header.setAttribute('aria-expanded', 'true');
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
        header.setAttribute('aria-expanded', 'false');
    }
}

function savePayment(bookingType, bookingId) {
    var key = bookingType + '-' + bookingId;
    var amountDue = document.getElementById('amount_due-' + key).value;
    var amountPaid = document.getElementById('amount_paid-' + key).value;
    var paymentStatus = document.getElementById('payment_status-' + key).value;

    fetch('/index.php?page=admin_payments&action=update_payment', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            booking_id: bookingId,
            booking_type: bookingType,
            amount_due: parseFloat(amountDue),
            amount_paid: parseFloat(amountPaid),
            payment_status: paymentStatus
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            showAlert('Payment updated successfully!', 'success');
        } else {
            showAlert(data.message || 'Failed to update payment', 'danger');
        }
    })
    .catch(function() { showAlert('An error occurred', 'danger'); });
}
</script>

<style>
.form-control-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    border-radius: 0.2rem;
    border: 1px solid #ced4da;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
