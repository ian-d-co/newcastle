<?php
$currentPage = 'dashboard';
ob_start();
?>

<div class="section">
    <div class="container">
        <h1 class="section-title">Your Dashboard</h1>

        <!-- Attendance -->
        <div class="card mb-3">
            <div class="card-header">Attendance</div>
            <div class="card-body">
                <?php if ($attendance): ?>
                    <p><strong>Days attending:</strong> <?php echo implode(', ', $attendance['days_attending']); ?></p>
                    <p><strong>Travel method:</strong> <?php echo implode(', ', $attendance['travel_method']); ?></p>
                <?php else: ?>
                    <p>You haven't registered your attendance yet.</p>
                    <a href="/index.php?page=home" class="btn btn-primary">Register Now</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Activity Bookings -->
        <?php if (!empty($activityBookings)): ?>
            <h2 class="text-primary mt-4 mb-3">Activity Bookings</h2>
            <?php foreach ($activityBookings as $booking): ?>
                <div class="card mb-3">
                    <div class="card-header"><?php echo e($booking['title']); ?></div>
                    <div class="card-body">
                        <p><strong>Day:</strong> <?php echo e($booking['day']); ?></p>
                        <p><strong>Time:</strong> <?php echo e(formatDisplayTime($booking['start_time'])); ?> - <?php echo e(formatDisplayTime($booking['end_time'])); ?></p>
                        <?php if ($booking['requires_prepayment']): ?>
                            <p><strong>Price:</strong> £<?php echo number_format($booking['price'], 2); ?></p>
                            <p><strong>Payment:</strong> 
                                <?php if ($booking['payment_status'] === 'paid'): ?>
                                    <span class="badge badge-success">Paid</span>
                                <?php elseif ($booking['payment_status'] === 'pending'): ?>
                                    <span class="badge badge-warning">Pending</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Not Required</span>
                                <?php endif; ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Meal Bookings -->
        <?php if (!empty($mealBookings)): ?>
            <h2 class="text-primary mt-4 mb-3">Meal Bookings</h2>
            <?php foreach ($mealBookings as $booking): ?>
                <div class="card mb-3">
                    <div class="card-header"><?php echo e($booking['title']); ?></div>
                    <div class="card-body">
                        <p><strong>Day:</strong> <?php echo e($booking['day']); ?></p>
                        <p><strong>Time:</strong> <?php echo e(formatDisplayTime($booking['start_time'])); ?> - <?php echo e(formatDisplayTime($booking['end_time'])); ?></p>
                        <?php if ($booking['requires_prepayment']): ?>
                            <p><strong>Price:</strong> £<?php echo number_format($booking['price'], 2); ?></p>
                            <p><strong>Payment:</strong> 
                                <?php if ($booking['payment_status'] === 'paid'): ?>
                                    <span class="badge badge-success">Paid</span>
                                <?php elseif ($booking['payment_status'] === 'pending'): ?>
                                    <span class="badge badge-warning">Pending</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Not Required</span>
                                <?php endif; ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Carshare -->
        <?php if ($carshareOffer): ?>
            <h2 class="text-primary mt-4 mb-3">Carshare Offer</h2>
            <div class="card mb-3">
                <div class="card-header">You're offering a carshare</div>
                <div class="card-body">
                    <p><strong>From:</strong> <?php echo e($carshareOffer['origin']); ?></p>
                    <p><strong>Capacity:</strong> <?php echo e($carshareOffer['passenger_capacity']); ?></p>
                    <p><strong>Available spaces:</strong> <?php echo e($carshareOffer['available_spaces']); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($carshareBooking): ?>
            <h2 class="text-primary mt-4 mb-3">Carshare Booking</h2>
            <div class="card mb-3">
                <div class="card-header">Your ride</div>
                <div class="card-body">
                    <p><strong>Driver:</strong> <?php echo e($carshareBooking['driver_name']); ?></p>
                    <p><strong>From:</strong> <?php echo e($carshareBooking['origin']); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Hosting -->
        <?php if ($hostingOffer): ?>
            <h2 class="text-primary mt-4 mb-3">Hosting Offer</h2>
            <div class="card mb-3">
                <div class="card-header">You're offering hosting</div>
                <div class="card-body">
                    <p><strong>Capacity:</strong> <?php echo e($hostingOffer['capacity']); ?> people</p>
                    <p><strong>Available spaces:</strong> <?php echo e($hostingOffer['available_spaces']); ?></p>
                    <?php if ($hostingOffer['notes']): ?>
                        <p><strong>Notes:</strong> <?php echo nl2br(e($hostingOffer['notes'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($hostingBooking): ?>
            <h2 class="text-primary mt-4 mb-3">Hosting Booking</h2>
            <div class="card mb-3">
                <div class="card-header">Your accommodation</div>
                <div class="card-body">
                    <p><strong>Host:</strong> <?php echo e($hostingBooking['host_name']); ?></p>
                    <?php if ($hostingBooking['notes']): ?>
                        <p><strong>Notes:</strong> <?php echo nl2br(e($hostingBooking['notes'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Hotel Reservations -->
        <?php if (!empty($hotelReservations)): ?>
            <h2 class="text-primary mt-4 mb-3">Hotel Reservations</h2>
            <?php foreach ($hotelReservations as $reservation): ?>
                <div class="card mb-3">
                    <div class="card-header"><?php echo e($reservation['hotel_name']); ?> - <?php echo e($reservation['room_type']); ?></div>
                    <div class="card-body">
                        <p><strong>Check-in:</strong> <?php echo formatDisplayDate($reservation['check_in']); ?></p>
                        <p><strong>Check-out:</strong> <?php echo formatDisplayDate($reservation['check_out']); ?></p>
                        <p><strong>Total price:</strong> £<?php echo number_format($reservation['total_price'], 2); ?></p>
                        <p><strong>Payment:</strong> 
                            <?php if ($reservation['payment_status'] === 'paid'): ?>
                                <span class="badge badge-success">Paid</span>
                            <?php elseif ($reservation['payment_status'] === 'pending'): ?>
                                <span class="badge badge-warning">Pending</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Polls Voted -->
        <?php if (!empty($pollsVoted)): ?>
            <h2 class="text-primary mt-4 mb-3">Polls You Voted On</h2>
            <?php foreach ($pollsVoted as $poll): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <p><strong><?php echo e($poll['question']); ?></strong></p>
                        <p class="text-muted">Voted on <?php echo formatDisplayDate($poll['voted_at']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (empty($activityBookings) && empty($mealBookings) && empty($carshareOffer) && empty($carshareBooking) && empty($hostingOffer) && empty($hostingBooking) && empty($hotelReservations) && empty($pollsVoted)): ?>
            <div class="card">
                <div class="card-body text-center">
                    <p>You haven't made any bookings yet. Start exploring!</p>
                    <a href="/index.php?page=activities" class="btn btn-primary">View Activities</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
