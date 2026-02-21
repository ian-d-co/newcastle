<?php
$currentPage = 'activities';
ob_start();

// Get user's interests for activities
$db = getDbConnection();
$userInterestsMap = [];
$interestStatsMap = [];

if (!empty($activities)) {
    $activityIds = array_column($activities, 'id');
    $placeholders = implode(',', array_fill(0, count($activityIds), '?'));

    // User's own interest levels
    $stmt = $db->prepare("SELECT item_id, interest_level FROM user_interests WHERE user_id = ? AND item_type = 'activity' AND item_id IN ($placeholders)");
    $stmt->execute(array_merge([$userId], $activityIds));
    foreach ($stmt->fetchAll() as $row) {
        $userInterestsMap[$row['item_id']] = $row['interest_level'];
    }

    // Aggregate interest counts
    $stmt = $db->prepare("SELECT item_id, interest_level, COUNT(*) as cnt FROM user_interests WHERE item_type = 'activity' AND item_id IN ($placeholders) GROUP BY item_id, interest_level");
    $stmt->execute($activityIds);
    foreach ($stmt->fetchAll() as $row) {
        $interestStatsMap[$row['item_id']][$row['interest_level']] = $row['cnt'];
    }
}
?>

<div class="section">
    <div class="container">
        <h1 class="section-title">Activities</h1>

        <?php if (empty($activities)): ?>
            <div class="card">
                <div class="card-body text-center">
                    <p>No activities available yet. Check back later!</p>
                </div>
            </div>
        <?php else: ?>
            <?php
            $days = ['Friday' => [], 'Saturday' => [], 'Sunday' => []];
            $seenActivityIds = [];
            foreach ($activities as $activity) {
                // Only add each activity once based on its ID
                if (!isset($seenActivityIds[$activity['id']])) {
                    $seenActivityIds[$activity['id']] = true;
                    $dayKey = ucfirst(strtolower(trim($activity['day'])));
                    if (isset($days[$dayKey])) {
                        $days[$dayKey][] = $activity;
                    }
                }
            }
            ?>

            <?php foreach ($days as $day => $dayActivities): ?>
                <?php if (!empty($dayActivities)): ?>
                    <div class="expander" style="margin-bottom: 1.5rem;">
                        <div class="expander-header" onclick="toggleExpander(this)" style="background: linear-gradient(135deg, #6d4c1f 0%, #8b6331 100%); color: #f5f5dc;">
                            <h2 style="margin: 0; font-weight: 400;"><?php echo e($day); ?> (<?php echo count($dayActivities); ?>)</h2>
                            <span class="expander-icon">▶</span>
                        </div>
                        <div class="expander-content">
                    <?php foreach ($dayActivities as $activity): ?>
                        <?php
                        $isFull = $activity['current_bookings'] >= $activity['max_capacity'];
                        $isBooked = $activity['is_booked'] ?? false;
                        $spotsLeft = $activity['max_capacity'] - $activity['current_bookings'];
                        $occupancyPct = $activity['max_capacity'] > 0 ? ($activity['current_bookings'] / $activity['max_capacity']) * 100 : 100;
                        $capacityClass = $occupancyPct >= 95 ? 'capacity-red' : ($occupancyPct >= 71 ? 'capacity-amber' : 'capacity-green');
                        ?>
                        
                        <div class="item">
                            <div class="item-header">
                                <h3 class="item-title"><?php echo e($activity['title']); ?></h3>
                            </div>
                            
                            <div class="item-meta">
                                <div class="item-meta-item">
                                    <strong>Time:</strong> <?php echo e(formatDisplayTime($activity['start_time'])); ?> - <?php echo e(formatDisplayTime($activity['end_time'])); ?>
                                </div>
                                <div class="item-meta-item">
                                    <strong>Capacity:</strong> 
                                    <span class="<?php echo $capacityClass; ?>">
                                        <?php echo e($activity['current_bookings']); ?> / <?php echo e($activity['max_capacity']); ?>
                                        <?php if (!$isFull && !$isBooked): ?>
                                            (<?php echo $spotsLeft; ?> spot<?php echo $spotsLeft !== 1 ? 's' : ''; ?> left)
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="item-meta-item">
                                    <strong>Price:</strong>
                                    <?php if (!isset($activity['price']) || $activity['price'] == 0): ?>
                                        <span style="color: #28a745; font-weight: 600;">Free</span>
                                    <?php else: ?>
                                        <span style="font-weight: 600;">£<?php echo number_format($activity['price'], 2); ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($activity['total_price']) && $activity['total_price'] > 0): ?>
                                <div class="item-meta-item">
                                    <strong>Total Price:</strong> £<?php echo number_format($activity['total_price'], 2); ?>
                                    <?php if (!empty($activity['deposit_amount']) && $activity['deposit_amount'] > 0): ?>
                                        &bull; <strong>Deposit:</strong> £<?php echo number_format($activity['deposit_amount'], 2); ?>
                                    <?php endif; ?>
                                    <?php if (!empty($activity['prepayment_required'])): ?>
                                        <span class="badge badge-warning" style="margin-left: 0.5rem;">Prepayment Required</span>
                                    <?php endif; ?>
                                    <?php if (!empty($activity['pay_on_arrival'])): ?>
                                        <span class="badge badge-success" style="margin-left: 0.5rem;">Pay on Arrival OK</span>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($activity['description']): ?>
                                <div class="item-description">
                                    <?php echo nl2br(e($activity['description'])); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($activity['link'])): ?>
                                <div style="margin: 0.5rem 0;">
                                    <a href="<?php echo e($activity['link']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-secondary">More Info 🔗</a>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($activity['confirmation_deadline'])): ?>
                                <div class="deadline-warning" style="margin: 0.5rem 0; padding: 0.5rem; background: #fff3cd; border-radius: 4px; font-size: 0.875rem;">
                                    <strong>⏰ Confirmation Deadline:</strong>
                                    <?php echo e(formatDisplayDate($activity['confirmation_deadline']) . ' ' . formatDisplayTime($activity['confirmation_deadline'])); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($activity['payment_deadline'])): ?>
                                <div class="deadline-warning" style="margin: 0.5rem 0; padding: 0.5rem; background: #fff3cd; border-radius: 4px; font-size: 0.875rem;">
                                    <strong>⏰ Payment Deadline:</strong>
                                    <?php echo e(formatDisplayDate($activity['payment_deadline']) . ' ' . formatDisplayTime($activity['payment_deadline'])); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!isGuestMode()): ?>
                            <div class="interest-selector" data-item-type="activity" data-item-id="<?php echo $activity['id']; ?>" style="margin: 0.75rem 0;">
                                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                    <small style="color: #666;">Your interest:</small>
                                    <?php
                                    $userLevel = $userInterestsMap[$activity['id']] ?? null;
                                    $stats = $interestStatsMap[$activity['id']] ?? [];
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
                                    <button class="btn-interest btn-sm btn-outline-secondary"
                                            data-item-type="activity"
                                            data-item-id="<?php echo $activity['id']; ?>"
                                            data-item-name="<?php echo e($activity['title']); ?>"
                                            onclick="openInterestWhoModal(this.dataset.itemType, this.dataset.itemId, this.dataset.itemName)"
                                            style="font-size:0.75rem; padding: 0.2rem 0.5rem;">
                                        👥 Who?
                                    </button>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="item-footer">
                                <?php if ($isBooked): ?>
                                    <?php if (!empty($activity['no_booking_required'])): ?>
                                        <span class="badge badge-success">✓ Marked as Attending</span>
                                    <?php else: ?>
                                    <span class="badge badge-success">✓ Booked</span>
                                    <?php if ($activity['requires_prepayment'] && $activity['payment_status'] === 'pending'): ?>
                                        <span class="badge badge-warning">Payment Pending</span>
                                    <?php elseif ($activity['requires_prepayment'] && $activity['payment_status'] === 'paid'): ?>
                                        <span class="badge badge-success">Paid</span>
                                    <?php endif; ?>
                                    <button class="btn btn-danger btn-sm" onclick="cancelActivity(<?php echo $activity['id']; ?>)">Cancel Booking</button>
                                    <?php endif; ?>
                                <?php elseif (!empty($activity['no_booking_required'])): ?>
                                    <?php if (isGuestMode()): ?>
                                        <button class="btn btn-primary" disabled>Mark Attending (Login Required)</button>
                                    <?php else: ?>
                                        <button class="btn btn-primary" onclick="markAttending('activity', <?php echo $activity['id']; ?>)">
                                            Mark as Attending
                                        </button>
                                    <?php endif; ?>
                                <?php elseif (empty($activity['booking_open'])): ?>
                                    <button class="btn btn-secondary" disabled>Booking Closed</button>
                                <?php else: ?>
                                    <?php if ($isFull): ?>
                                        <span class="badge badge-danger">Full</span>
                                    <?php elseif (isGuestMode()): ?>
                                        <button class="btn btn-primary" disabled>Book (Login Required)</button>
                                    <?php elseif (!hasRegisteredAttendance()): ?>
                                        <button class="btn btn-warning" onclick="showAttendanceRequired()">Register Attendance First</button>
                                    <?php else: ?>
                                        <button class="btn btn-primary" onclick="bookActivity(<?php echo $activity['id']; ?>)">Book Activity</button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                        </div><!-- /.expander-content -->
                    </div><!-- /.expander -->
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleExpander(header) {
    header.classList.toggle('active');
    const content = header.nextElementSibling;
    content.classList.toggle('active');
    
    const icon = header.querySelector('.expander-icon');
    if (header.classList.contains('active')) {
        icon.textContent = '▼';
    } else {
        icon.textContent = '▶';
    }
}

function showAttendanceRequired() {
    showAlert('Please register your event attendance first from your Dashboard or My Plans page.', 'warning');
    setTimeout(function() {
        window.location.href = '/index.php?page=dashboard';
    }, 2000);
}

document.querySelectorAll('.interest-selector').forEach(function(container) {
    container.querySelectorAll('.btn-interest[data-level]').forEach(function(btn) {
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
                    container.querySelectorAll('.btn-interest[data-level]').forEach(function(b) {
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
