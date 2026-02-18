<?php
$currentPage = 'activities';
ob_start();
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
            foreach ($activities as $activity) {
                $days[$activity['day']][] = $activity;
            }
            ?>

            <?php foreach ($days as $day => $dayActivities): ?>
                <?php if (!empty($dayActivities)): ?>
                    <h2 class="text-primary mt-4 mb-3"><?php echo e($day); ?></h2>
                    
                    <?php foreach ($dayActivities as $activity): ?>
                        <?php
                        $isFull = $activity['current_bookings'] >= $activity['max_capacity'];
                        $isBooked = $activity['is_booked'] ?? false;
                        $spotsLeft = $activity['max_capacity'] - $activity['current_bookings'];
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
                                    <span class="<?php echo $isFull ? 'text-danger' : 'text-success'; ?>">
                                        <?php echo e($activity['current_bookings']); ?> / <?php echo e($activity['max_capacity']); ?>
                                        <?php if (!$isFull && !$isBooked): ?>
                                            (<?php echo $spotsLeft; ?> spot<?php echo $spotsLeft !== 1 ? 's' : ''; ?> left)
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <?php if ($activity['requires_prepayment']): ?>
                                <div class="item-meta-item">
                                    <span class="badge badge-warning">Prepayment Required: £<?php echo number_format($activity['price'], 2); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($activity['description']): ?>
                                <div class="item-description">
                                    <?php echo nl2br(e($activity['description'])); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="item-footer">
                                <?php if ($isBooked): ?>
                                    <span class="badge badge-success">✓ Booked</span>
                                    <?php if ($activity['requires_prepayment'] && $activity['payment_status'] === 'pending'): ?>
                                        <span class="badge badge-warning">Payment Pending</span>
                                    <?php elseif ($activity['requires_prepayment'] && $activity['payment_status'] === 'paid'): ?>
                                        <span class="badge badge-success">Paid</span>
                                    <?php endif; ?>
                                    <button class="btn btn-danger btn-sm" onclick="cancelActivity(<?php echo $activity['id']; ?>)">Cancel Booking</button>
                                <?php else: ?>
                                    <?php if ($isFull): ?>
                                        <span class="badge badge-danger">Full</span>
                                    <?php else: ?>
                                        <button class="btn btn-primary" onclick="bookActivity(<?php echo $activity['id']; ?>)">Book Activity</button>
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

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
