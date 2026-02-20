<?php
$currentPage = 'meals';
ob_start();

// Get user's interests for meals
$db = getDbConnection();
$userInterestsMap = [];
$interestStatsMap = [];

if (!empty($meals)) {
    $mealIds = array_column($meals, 'id');
    $placeholders = implode(',', array_fill(0, count($mealIds), '?'));

    $stmt = $db->prepare("SELECT item_id, interest_level FROM user_interests WHERE user_id = ? AND item_type = 'meal' AND item_id IN ($placeholders)");
    $stmt->execute(array_merge([$userId], $mealIds));
    foreach ($stmt->fetchAll() as $row) {
        $userInterestsMap[$row['item_id']] = $row['interest_level'];
    }

    $stmt = $db->prepare("SELECT item_id, interest_level, COUNT(*) as cnt FROM user_interests WHERE item_type = 'meal' AND item_id IN ($placeholders) GROUP BY item_id, interest_level");
    $stmt->execute($mealIds);
    foreach ($stmt->fetchAll() as $row) {
        $interestStatsMap[$row['item_id']][$row['interest_level']] = $row['cnt'];
    }
}
?>

<div class="section">
    <div class="container">
        <h1 class="section-title">Meals</h1>

        <?php if (empty($meals)): ?>
            <div class="card">
                <div class="card-body text-center">
                    <p>No meals available yet. Check back later!</p>
                </div>
            </div>
        <?php else: ?>
            <?php
            $days = ['Friday' => [], 'Saturday' => [], 'Sunday' => []];
            foreach ($meals as $meal) {
                $days[$meal['day']][] = $meal;
            }
            ?>

            <?php foreach ($days as $day => $dayMeals): ?>
                <?php if (!empty($dayMeals)): ?>
                    <h2 class="text-primary mt-4 mb-3"><?php echo e($day); ?></h2>
                    
                    <?php foreach ($dayMeals as $meal): ?>
                        <?php
                        $isFull = $meal['current_bookings'] >= $meal['max_capacity'];
                        $isBooked = $meal['is_booked'] ?? false;
                        $spotsLeft = $meal['max_capacity'] - $meal['current_bookings'];
                        $occupancyPct = $meal['max_capacity'] > 0 ? ($meal['current_bookings'] / $meal['max_capacity']) * 100 : 100;
                        $capacityClass = $occupancyPct >= 95 ? 'capacity-red' : ($occupancyPct >= 71 ? 'capacity-amber' : 'capacity-green');
                        ?>
                        
                        <div class="item">
                            <div class="item-header">
                                <h3 class="item-title"><?php echo e($meal['title']); ?></h3>
                            </div>
                            
                            <div class="item-meta">
                                <div class="item-meta-item">
                                    <strong>Time:</strong> <?php echo e(formatDisplayTime($meal['start_time'])); ?> - <?php echo e(formatDisplayTime($meal['end_time'])); ?>
                                </div>
                                <div class="item-meta-item">
                                    <strong>Capacity:</strong> 
                                    <span class="<?php echo $capacityClass; ?>">
                                        <?php echo e($meal['current_bookings']); ?> / <?php echo e($meal['max_capacity']); ?>
                                        <?php if (!$isFull && !$isBooked): ?>
                                            (<?php echo $spotsLeft; ?> spot<?php echo $spotsLeft !== 1 ? 's' : ''; ?> left)
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <?php if (!empty($meal['total_price']) && $meal['total_price'] > 0): ?>
                                <div class="item-meta-item">
                                    <strong>Total Price:</strong> £<?php echo number_format($meal['total_price'], 2); ?>
                                    <?php if (!empty($meal['deposit_amount']) && $meal['deposit_amount'] > 0): ?>
                                        &bull; <strong>Deposit:</strong> £<?php echo number_format($meal['deposit_amount'], 2); ?>
                                    <?php endif; ?>
                                    <?php if (!empty($meal['prepayment_required'])): ?>
                                        <span class="badge badge-warning" style="margin-left: 0.5rem;">Prepayment Required</span>
                                    <?php endif; ?>
                                    <?php if (!empty($meal['pay_on_arrival'])): ?>
                                        <span class="badge badge-success" style="margin-left: 0.5rem;">Pay on Arrival OK</span>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($meal['description']): ?>
                                <div class="item-description">
                                    <?php echo nl2br(e($meal['description'])); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($meal['link'])): ?>
                                <div style="margin: 0.5rem 0;">
                                    <a href="<?php echo e($meal['link']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-secondary">More Info 🔗</a>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($meal['confirmation_deadline'])): ?>
                                <div class="deadline-warning" style="margin: 0.5rem 0; padding: 0.5rem; background: #fff3cd; border-radius: 4px; font-size: 0.875rem;">
                                    <strong>⏰ Confirmation Deadline:</strong>
                                    <?php echo e(formatDisplayDate($meal['confirmation_deadline']) . ' ' . formatDisplayTime($meal['confirmation_deadline'])); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($meal['payment_deadline'])): ?>
                                <div class="deadline-warning" style="margin: 0.5rem 0; padding: 0.5rem; background: #fff3cd; border-radius: 4px; font-size: 0.875rem;">
                                    <strong>⏰ Payment Deadline:</strong>
                                    <?php echo e(formatDisplayDate($meal['payment_deadline']) . ' ' . formatDisplayTime($meal['payment_deadline'])); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!isGuestMode()): ?>
                            <div class="interest-selector" data-item-type="meal" data-item-id="<?php echo $meal['id']; ?>" style="margin: 0.75rem 0;">
                                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                    <small style="color: #666;">Your interest:</small>
                                    <?php
                                    $userLevel = $userInterestsMap[$meal['id']] ?? null;
                                    $stats = $interestStatsMap[$meal['id']] ?? [];
                                    ?>
                                    <button class="btn-interest btn-sm <?php echo $userLevel === 'interested' ? 'btn-success' : 'btn-outline-secondary'; ?>"
                                            data-level="interested" style="font-size:0.75rem; padding: 0.2rem 0.5rem;">
                                        👍 <?php echo $stats['interested'] ?? 0; ?>
                                    </button>
                                    <button class="btn-interest btn-sm <?php echo $userLevel === 'maybe' ? 'btn-warning' : 'btn-outline-secondary'; ?>"
                                            data-level="maybe" style="font-size:0.75rem; padding: 0.2rem 0.5rem;">
                                        🤔 <?php echo $stats['maybe'] ?? 0; ?>
                                    </button>
                                    <button class="btn-interest btn-sm <?php echo $userLevel === 'not_interested' ? 'btn-danger' : 'btn-outline-secondary'; ?>"
                                            data-level="not_interested" style="font-size:0.75rem; padding: 0.2rem 0.5rem;">
                                        👎 <?php echo $stats['not_interested'] ?? 0; ?>
                                    </button>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="item-footer">
                                <?php if ($isBooked): ?>
                                    <?php if (!empty($meal['no_booking_required'])): ?>
                                        <span class="badge badge-success">✓ Marked as Attending</span>
                                    <?php else: ?>
                                    <span class="badge badge-success">✓ Booked</span>
                                    <?php if ($meal['requires_prepayment'] && $meal['payment_status'] === 'pending'): ?>
                                        <span class="badge badge-warning">Payment Pending</span>
                                    <?php elseif ($meal['requires_prepayment'] && $meal['payment_status'] === 'paid'): ?>
                                        <span class="badge badge-success">Paid</span>
                                    <?php endif; ?>
                                    <button class="btn btn-danger btn-sm" onclick="cancelMeal(<?php echo $meal['id']; ?>)">Cancel Booking</button>
                                    <?php endif; ?>
                                <?php elseif (!empty($meal['no_booking_required'])): ?>
                                    <?php if (isGuestMode()): ?>
                                        <button class="btn btn-primary" disabled>Mark Attending (Login Required)</button>
                                    <?php else: ?>
                                        <button class="btn btn-primary" onclick="markAttending('meal', <?php echo $meal['id']; ?>)">
                                            Mark as Attending
                                        </button>
                                    <?php endif; ?>
                                <?php elseif (empty($meal['booking_open'])): ?>
                                    <button class="btn btn-secondary" disabled>Booking Closed</button>
                                <?php else: ?>
                                    <?php if ($isFull): ?>
                                        <span class="badge badge-danger">Full</span>
                                    <?php elseif (isGuestMode()): ?>
                                        <button class="btn btn-primary" disabled>Book (Login Required)</button>
                                    <?php elseif (!hasRegisteredAttendance()): ?>
                                        <button class="btn btn-warning" onclick="showAttendanceRequired()">Register Attendance First</button>
                                    <?php else: ?>
                                        <button class="btn btn-primary" onclick="bookMeal(<?php echo $meal['id']; ?>)">Book Meal</button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function showAttendanceRequired() {
    showAlert('Please register your event attendance first from your Dashboard or My Plans page.', 'warning');
    setTimeout(function() {
        window.location.href = '/index.php?page=dashboard';
    }, 2000);
}

document.querySelectorAll('.interest-selector').forEach(function(container) {
    container.querySelectorAll('.btn-interest').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var itemType = container.dataset.itemType;
            var itemId = container.dataset.itemId;
            var level = this.dataset.level;

            fetch('/api/interest.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ item_type: itemType, item_id: itemId, interest_level: level })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    showAlert('Interest updated!', 'success');
                    var levels = { interested: '👍', maybe: '🤔', not_interested: '👎' };
                    var classes = { interested: 'btn-success', maybe: 'btn-warning', not_interested: 'btn-danger' };
                    container.querySelectorAll('.btn-interest').forEach(function(b) {
                        var l = b.dataset.level;
                        b.className = 'btn-interest btn-sm ' + (l === data.user_level ? classes[l] : 'btn-outline-secondary');
                        b.textContent = levels[l] + ' ' + (data.counts[l] || 0);
                        b.style.fontSize = '0.75rem';
                        b.style.padding = '0.2rem 0.5rem';
                    });
                } else {
                    showAlert(data.message || 'Failed to update interest', 'danger');
                }
            })
            .catch(function() { showAlert('An error occurred', 'danger'); });
        });
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
