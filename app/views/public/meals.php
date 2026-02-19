<?php
$currentPage = 'meals';
ob_start();
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
                                    <span class="<?php echo $isFull ? 'text-danger' : 'text-success'; ?>">
                                        <?php echo e($meal['current_bookings']); ?> / <?php echo e($meal['max_capacity']); ?>
                                        <?php if (!$isFull && !$isBooked): ?>
                                            (<?php echo $spotsLeft; ?> spot<?php echo $spotsLeft !== 1 ? 's' : ''; ?> left)
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <?php if ($meal['requires_prepayment']): ?>
                                <div class="item-meta-item">
                                    <span class="badge badge-warning">Prepayment Required: £<?php echo number_format($meal['price'], 2); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($meal['description']): ?>
                                <div class="item-description">
                                    <?php echo nl2br(e($meal['description'])); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="item-footer">
                                <?php if ($isBooked): ?>
                                    <span class="badge badge-success">✓ Booked</span>
                                    <?php if ($meal['requires_prepayment'] && $meal['payment_status'] === 'pending'): ?>
                                        <span class="badge badge-warning">Payment Pending</span>
                                    <?php elseif ($meal['requires_prepayment'] && $meal['payment_status'] === 'paid'): ?>
                                        <span class="badge badge-success">Paid</span>
                                    <?php endif; ?>
                                    <button class="btn btn-danger btn-sm" onclick="cancelMeal(<?php echo $meal['id']; ?>)" <?php if (isGuestMode()): ?>disabled title="Please log in to manage bookings"<?php endif; ?>>Cancel Booking</button>
                                <?php else: ?>
                                    <?php if ($isFull): ?>
                                        <span class="badge badge-danger">Full</span>
                                    <?php else: ?>
                                        <button class="btn btn-primary" onclick="bookMeal(<?php echo $meal['id']; ?>)" <?php if (isGuestMode()): ?>disabled title="Please log in to book"<?php endif; ?>>Book Meal</button>
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
