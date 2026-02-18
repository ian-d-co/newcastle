<?php
$currentPage = 'admin-payments';
ob_start();
?>

<div class="section">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 class="section-title">Payment Tracking</h1>
            <a href="/index.php?page=admin" class="btn btn-secondary">← Back to Admin</a>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo e($success); ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo e($error); ?></div>
        <?php endif; ?>

        <!-- Summary Statistics -->
        <div class="dashboard-grid" style="margin-bottom: 2rem;">
            <div class="dashboard-stat">
                <div class="dashboard-stat-value">£<?php echo number_format($stats['total_due'], 2); ?></div>
                <div class="dashboard-stat-label">Total Due</div>
            </div>

            <div class="dashboard-stat">
                <div class="dashboard-stat-value">£<?php echo number_format($stats['total_received'], 2); ?></div>
                <div class="dashboard-stat-label">Total Received</div>
            </div>

            <div class="dashboard-stat">
                <div class="dashboard-stat-value">£<?php echo number_format($stats['total_pending'], 2); ?></div>
                <div class="dashboard-stat-label">Pending</div>
            </div>

            <div class="dashboard-stat">
                <div class="dashboard-stat-value"><?php echo $stats['pending_count']; ?></div>
                <div class="dashboard-stat-label">Pending Payments</div>
            </div>
        </div>

        <!-- Filter -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-body">
                <form method="GET" action="/index.php" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: end;">
                    <input type="hidden" name="page" value="admin-payments">
                    
                    <div style="flex: 1; min-width: 200px;">
                        <label class="form-label">Filter by Status</label>
                        <select class="form-control" name="status">
                            <option value="all" <?php echo ($_GET['status'] ?? 'all') === 'all' ? 'selected' : ''; ?>>All</option>
                            <option value="pending" <?php echo ($_GET['status'] ?? 'all') === 'pending' ? 'selected' : ''; ?>>Pending Only</option>
                            <option value="received" <?php echo ($_GET['status'] ?? 'all') === 'received' ? 'selected' : ''; ?>>Received Only</option>
                        </select>
                    </div>

                    <div style="flex: 1; min-width: 200px;">
                        <label class="form-label">Filter by Type</label>
                        <select class="form-control" name="type">
                            <option value="all" <?php echo ($_GET['type'] ?? 'all') === 'all' ? 'selected' : ''; ?>>All Types</option>
                            <option value="activity" <?php echo ($_GET['type'] ?? 'all') === 'activity' ? 'selected' : ''; ?>>Activities</option>
                            <option value="meal" <?php echo ($_GET['type'] ?? 'all') === 'meal' ? 'selected' : ''; ?>>Meals</option>
                            <option value="hotel" <?php echo ($_GET['type'] ?? 'all') === 'hotel' ? 'selected' : ''; ?>>Hotels</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Apply Filter</button>
                    <?php if (isset($_GET['status']) || isset($_GET['type'])): ?>
                        <a href="/index.php?page=admin-payments" class="btn btn-secondary">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Activity Bookings -->
        <?php if (!empty($activityBookings)): ?>
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-header">Activity Bookings (<?php echo count($activityBookings); ?>)</div>
                <div class="card-body">
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                            <thead>
                                <tr style="border-bottom: 2px solid #dee2e6;">
                                    <th style="padding: 0.75rem; text-align: left;">User</th>
                                    <th style="padding: 0.75rem; text-align: left;">Activity</th>
                                    <th style="padding: 0.75rem; text-align: left;">Date</th>
                                    <th style="padding: 0.75rem; text-align: right;">Amount</th>
                                    <th style="padding: 0.75rem; text-align: center;">Status</th>
                                    <th style="padding: 0.75rem; text-align: center;">Mark Paid</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($activityBookings as $booking): ?>
                                    <tr style="border-bottom: 1px solid #dee2e6;">
                                        <td style="padding: 0.75rem;"><?php echo e($booking['discord_name']); ?></td>
                                        <td style="padding: 0.75rem;"><?php echo e($booking['activity_title']); ?></td>
                                        <td style="padding: 0.75rem;"><?php echo date('M j, Y', strtotime($booking['activity_date'])); ?></td>
                                        <td style="padding: 0.75rem; text-align: right;">£<?php echo number_format($booking['payment_amount'], 2); ?></td>
                                        <td style="padding: 0.75rem; text-align: center;">
                                            <?php if ($booking['payment_status'] === 'received'): ?>
                                                <span class="badge badge-success">Received</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 0.75rem; text-align: center;">
                                            <form method="POST" action="/index.php?page=admin-payments&action=update" style="display: inline;">
                                                <?php echo CSRF::field(); ?>
                                                <input type="hidden" name="type" value="activity">
                                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                <input type="hidden" name="status" value="<?php echo $booking['payment_status'] === 'received' ? 'pending' : 'received'; ?>">
                                                <label>
                                                    <input type="checkbox" <?php echo $booking['payment_status'] === 'received' ? 'checked' : ''; ?> onchange="this.form.submit()">
                                                </label>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Meal Bookings -->
        <?php if (!empty($mealBookings)): ?>
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-header">Meal Bookings (<?php echo count($mealBookings); ?>)</div>
                <div class="card-body">
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                            <thead>
                                <tr style="border-bottom: 2px solid #dee2e6;">
                                    <th style="padding: 0.75rem; text-align: left;">User</th>
                                    <th style="padding: 0.75rem; text-align: left;">Meal</th>
                                    <th style="padding: 0.75rem; text-align: left;">Date</th>
                                    <th style="padding: 0.75rem; text-align: right;">Amount</th>
                                    <th style="padding: 0.75rem; text-align: center;">Status</th>
                                    <th style="padding: 0.75rem; text-align: center;">Mark Paid</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mealBookings as $booking): ?>
                                    <tr style="border-bottom: 1px solid #dee2e6;">
                                        <td style="padding: 0.75rem;"><?php echo e($booking['discord_name']); ?></td>
                                        <td style="padding: 0.75rem;"><?php echo e($booking['meal_title']); ?></td>
                                        <td style="padding: 0.75rem;"><?php echo date('M j, Y', strtotime($booking['meal_date'])); ?></td>
                                        <td style="padding: 0.75rem; text-align: right;">£<?php echo number_format($booking['payment_amount'], 2); ?></td>
                                        <td style="padding: 0.75rem; text-align: center;">
                                            <?php if ($booking['payment_status'] === 'received'): ?>
                                                <span class="badge badge-success">Received</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 0.75rem; text-align: center;">
                                            <form method="POST" action="/index.php?page=admin-payments&action=update" style="display: inline;">
                                                <?php echo CSRF::field(); ?>
                                                <input type="hidden" name="type" value="meal">
                                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                <input type="hidden" name="status" value="<?php echo $booking['payment_status'] === 'received' ? 'pending' : 'received'; ?>">
                                                <label>
                                                    <input type="checkbox" <?php echo $booking['payment_status'] === 'received' ? 'checked' : ''; ?> onchange="this.form.submit()">
                                                </label>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Hotel Reservations -->
        <?php if (!empty($hotelReservations)): ?>
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-header">Hotel Reservations (<?php echo count($hotelReservations); ?>)</div>
                <div class="card-body">
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                            <thead>
                                <tr style="border-bottom: 2px solid #dee2e6;">
                                    <th style="padding: 0.75rem; text-align: left;">User</th>
                                    <th style="padding: 0.75rem; text-align: left;">Hotel</th>
                                    <th style="padding: 0.75rem; text-align: left;">Room Type</th>
                                    <th style="padding: 0.75rem; text-align: left;">Check-in</th>
                                    <th style="padding: 0.75rem; text-align: left;">Check-out</th>
                                    <th style="padding: 0.75rem; text-align: right;">Amount</th>
                                    <th style="padding: 0.75rem; text-align: center;">Status</th>
                                    <th style="padding: 0.75rem; text-align: center;">Mark Paid</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($hotelReservations as $reservation): ?>
                                    <tr style="border-bottom: 1px solid #dee2e6;">
                                        <td style="padding: 0.75rem;"><?php echo e($reservation['discord_name']); ?></td>
                                        <td style="padding: 0.75rem;"><?php echo e($reservation['hotel_name']); ?></td>
                                        <td style="padding: 0.75rem;"><?php echo e($reservation['room_type']); ?></td>
                                        <td style="padding: 0.75rem;"><?php echo date('M j, Y', strtotime($reservation['check_in_date'])); ?></td>
                                        <td style="padding: 0.75rem;"><?php echo date('M j, Y', strtotime($reservation['check_out_date'])); ?></td>
                                        <td style="padding: 0.75rem; text-align: right;">£<?php echo number_format($reservation['total_price'], 2); ?></td>
                                        <td style="padding: 0.75rem; text-align: center;">
                                            <?php if ($reservation['payment_status'] === 'received'): ?>
                                                <span class="badge badge-success">Received</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 0.75rem; text-align: center;">
                                            <form method="POST" action="/index.php?page=admin-payments&action=update" style="display: inline;">
                                                <?php echo CSRF::field(); ?>
                                                <input type="hidden" name="type" value="hotel">
                                                <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                                <input type="hidden" name="status" value="<?php echo $reservation['payment_status'] === 'received' ? 'pending' : 'received'; ?>">
                                                <label>
                                                    <input type="checkbox" <?php echo $reservation['payment_status'] === 'received' ? 'checked' : ''; ?> onchange="this.form.submit()">
                                                </label>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (empty($activityBookings) && empty($mealBookings) && empty($hotelReservations)): ?>
            <div class="card">
                <div class="card-body text-center" style="padding: 3rem;">
                    <p>No payment records found matching the current filter.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
