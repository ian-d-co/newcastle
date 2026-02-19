<?php
$currentPage = 'dashboard';
ob_start();
?>

<div class="section">
    <div class="container">
        <h1 class="section-title">My Plans</h1>

        <!-- Section 1: Attendance -->
        <div class="dashboard-section">
            <div class="dashboard-section-header" onclick="toggleSection(this)">
                <h3><span class="toggle-icon">▶</span>Attendance</h3>
                <div class="section-action">
                    <?php if ($isAttending): ?>
                        <span class="badge badge-success">✓ Attending</span>
                    <?php else: ?>
                        <button class="btn btn-primary btn-sm" onclick="event.stopPropagation(); modalManager.open('attendance-modal')">Register Now</button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="dashboard-section-content" style="display: none;">
                <?php if ($isAttending): ?>
                    <p><strong>Days Attending:</strong> <?php echo implode(', ', $attendance['days_attending']); ?></p>
                    <p><strong>Travel Method:</strong> <?php echo implode(', ', $attendance['travel_method']); ?></p>
                    <button class="btn btn-danger btn-sm" onclick="cancelAttendance()">Cancel My Attendance</button>
                <?php else: ?>
                    <p>You haven't registered your attendance yet.</p>
                    <button class="btn btn-primary" onclick="modalManager.open('attendance-modal')">Register Now</button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Section 2: Activities -->
        <div class="dashboard-section">
            <div class="dashboard-section-header" onclick="toggleSection(this)">
                <h3><span class="toggle-icon">▶</span>Activities<?php if (!empty($activityBookings)): ?> (<?php echo count($activityBookings); ?>)<?php endif; ?></h3>
            </div>
            <div class="dashboard-section-content" style="display: none;">
                <?php if (!empty($activityBookings)): ?>
                    <?php foreach ($activityBookings as $booking): ?>
                        <div class="dashboard-booking-card">
                            <p><strong><?php echo e($booking['title']); ?></strong></p>
                            <p><?php echo e($booking['day']); ?> &bull; <?php echo e(formatDisplayTime($booking['start_time'])); ?> - <?php echo e(formatDisplayTime($booking['end_time'])); ?></p>
                            <?php if ($booking['requires_prepayment']): ?>
                                <p>Price: £<?php echo number_format($booking['price'], 2); ?> &bull; Payment:
                                    <?php if ($booking['payment_status'] === 'paid'): ?>
                                        <span class="badge badge-success">✓ Paid</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">⚠ Pending</span>
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>
                            <button class="btn btn-danger btn-sm" onclick="cancelActivity(<?php echo (int)$booking['activity_id']; ?>)">Cancel Booking</button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No activity bookings yet.</p>
                    <a href="/index.php?page=activities" class="btn btn-primary">Browse Activities</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Section 3: Meals -->
        <div class="dashboard-section">
            <div class="dashboard-section-header" onclick="toggleSection(this)">
                <h3><span class="toggle-icon">▶</span>Meals<?php if (!empty($mealBookings)): ?> (<?php echo count($mealBookings); ?>)<?php endif; ?></h3>
            </div>
            <div class="dashboard-section-content" style="display: none;">
                <?php if (!empty($mealBookings)): ?>
                    <?php foreach ($mealBookings as $booking): ?>
                        <div class="dashboard-booking-card">
                            <p><strong><?php echo e($booking['title']); ?></strong></p>
                            <p><?php echo e($booking['day']); ?> &bull; <?php echo e(formatDisplayTime($booking['start_time'])); ?> - <?php echo e(formatDisplayTime($booking['end_time'])); ?></p>
                            <?php if ($booking['requires_prepayment']): ?>
                                <p>Price: £<?php echo number_format($booking['price'], 2); ?> &bull; Payment:
                                    <?php if ($booking['payment_status'] === 'paid'): ?>
                                        <span class="badge badge-success">✓ Paid</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">⚠ Pending</span>
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>
                            <button class="btn btn-danger btn-sm" onclick="cancelMeal(<?php echo (int)$booking['meal_id']; ?>)">Cancel Booking</button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No meal bookings yet.</p>
                    <a href="/index.php?page=meals" class="btn btn-primary">Browse Meals</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Section 4: Travel & Accommodation -->
        <div class="dashboard-section">
            <div class="dashboard-section-header" onclick="toggleSection(this)">
                <h3><span class="toggle-icon">▶</span>Travel &amp; Accommodation</h3>
            </div>
            <div class="dashboard-section-content" style="display: none;">
                <?php if ($isAttending): ?>
                    <p>🚗 <strong>Travel Method:</strong> <?php echo implode(', ', $attendance['travel_method']); ?></p>
                <?php endif; ?>

                <?php if ($carshareOffer): ?>
                    <hr>
                    <p><strong>Carshare — Offering a ride</strong></p>
                    <p>From: <?php echo e($carshareOffer['origin']); ?></p>
                    <p>Available seats: <?php echo e($carshareOffer['available_spaces']); ?></p>
                <?php endif; ?>

                <?php if ($carshareBooking): ?>
                    <hr>
                    <p><strong>Carpooling with:</strong> <?php echo e($carshareBooking['driver_name']); ?> (from <?php echo e($carshareBooking['origin']); ?>)</p>
                <?php endif; ?>

                <?php if ($hostingOffer): ?>
                    <hr>
                    <p>🏠 <strong>Hosting — Offering <?php echo e($hostingOffer['capacity']); ?> spaces</strong></p>
                    <p>Available spaces: <?php echo e($hostingOffer['available_spaces']); ?></p>
                    <?php if ($hostingOffer['notes']): ?>
                        <p>Notes: <?php echo nl2br(e($hostingOffer['notes'])); ?></p>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($hostingBooking): ?>
                    <hr>
                    <p>🏠 <strong>Staying with:</strong> <?php echo e($hostingBooking['host_name']); ?></p>
                    <?php if ($hostingBooking['notes']): ?>
                        <p>Notes: <?php echo nl2br(e($hostingBooking['notes'])); ?></p>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (!empty($hotelReservations)): ?>
                    <hr>
                    <?php foreach ($hotelReservations as $reservation): ?>
                        <div class="dashboard-booking-card">
                            <p><strong><?php echo e($reservation['hotel_name']); ?></strong> — <?php echo e($reservation['room_type']); ?></p>
                            <p><?php echo formatDisplayDate($reservation['check_in']); ?> – <?php echo formatDisplayDate($reservation['check_out']); ?></p>
                            <p>Total: £<?php echo number_format($reservation['total_price'], 2); ?> &bull; Payment:
                                <?php if ($reservation['payment_status'] === 'paid'): ?>
                                    <span class="badge badge-success">✓ Paid</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">⚠ Pending</span>
                                <?php endif; ?>
                            </p>
                            <button class="btn btn-danger btn-sm" onclick="cancelReservation(<?php echo (int)$reservation['id']; ?>)">Cancel Reservation</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if (!$carshareOffer && !$carshareBooking && !$hostingOffer && !$hostingBooking && empty($hotelReservations)): ?>
                    <p>No accommodation arranged yet.</p>
                    <a href="/index.php?page=hotels" class="btn btn-primary btn-sm">Browse Hotels</a>
                    <a href="/index.php?page=hosting" class="btn btn-secondary btn-sm">View Hosting Offers</a>
                    <a href="/index.php?page=carshare" class="btn btn-secondary btn-sm">View Carshare</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Modal -->
<?php if (!$isAttending): ?>
<div class="modal" id="attendance-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Register Your Attendance</h3>
            <button class="modal-close" onclick="modalManager.close('attendance-modal')" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="attendance-form">
                <?php echo CSRF::field(); ?>

                <div class="form-group">
                    <label class="form-label" for="discord_name">Discord Name *</label>
                    <input type="text" class="form-control" id="discord_name" name="discord_name" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="name">Name *</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="pin">Create a PIN (for future logins) *</label>
                    <input type="password" class="form-control" id="pin" name="pin" minlength="4" required>
                    <small>Minimum 4 digits</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Days Attending *</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="day-friday" name="days_attending[]" value="Friday">
                        <label class="form-check-label" for="day-friday">Friday (Nov 20)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="day-saturday" name="days_attending[]" value="Saturday">
                        <label class="form-check-label" for="day-saturday">Saturday (Nov 21)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="day-sunday" name="days_attending[]" value="Sunday">
                        <label class="form-check-label" for="day-sunday">Sunday (Nov 22)</label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Travel Method *</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="travel-train" name="travel_method[]" value="Train">
                        <label class="form-check-label" for="travel-train">Train</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="travel-plane" name="travel_method[]" value="Plane">
                        <label class="form-check-label" for="travel-plane">Plane</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="travel-car" name="travel_method[]" value="Car">
                        <label class="form-check-label" for="travel-car">Car</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="travel-coach" name="travel_method[]" value="Coach">
                        <label class="form-check-label" for="travel-coach">Coach</label>
                    </div>
                </div>

                <!-- Carshare Section (Conditional) -->
                <div id="carshare-section" style="display: none;">
                    <div class="form-group">
                        <label class="form-label">Can you offer a carshare/lift?</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="can-carshare-yes" name="can_carshare" value="yes">
                            <label class="form-check-label" for="can-carshare-yes">Yes</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="can-carshare-no" name="can_carshare" value="no">
                            <label class="form-check-label" for="can-carshare-no">No</label>
                        </div>
                    </div>

                    <div id="carshare-details" style="display: none;">
                        <div class="form-group">
                            <label class="form-label" for="carshare_origin">Where are you travelling from?</label>
                            <input type="text" class="form-control" id="carshare_origin" name="carshare_origin">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="carshare_capacity">How many passengers can you take?</label>
                            <input type="number" class="form-control" id="carshare_capacity" name="carshare_capacity" min="1" max="8">
                        </div>
                    </div>
                </div>

                <!-- Hosting Section -->
                <div class="form-group">
                    <label class="form-label">Can you host people?</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" id="can-host-yes" name="can_host" value="yes">
                        <label class="form-check-label" for="can-host-yes">Yes</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" id="can-host-no" name="can_host" value="no">
                        <label class="form-check-label" for="can-host-no">No</label>
                    </div>
                </div>

                <div id="hosting-details" style="display: none;">
                    <div class="form-group">
                        <label class="form-label" for="hosting_capacity">How many people can you host?</label>
                        <input type="number" class="form-control" id="hosting_capacity" name="hosting_capacity" min="1" max="20">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="hosting_notes">Additional notes (optional)</label>
                        <textarea class="form-control" id="hosting_notes" name="hosting_notes" rows="3"></textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg">Register Attendance</button>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function toggleSection(header) {
    var content = header.nextElementSibling;
    var icon = header.querySelector('.toggle-icon');
    if (content.style.display === 'none' || !content.style.display) {
        content.style.display = 'block';
        icon.textContent = '▼';
    } else {
        content.style.display = 'none';
        icon.textContent = '▶';
    }
}

function cancelAttendance() {
    confirmAction('Are you sure you want to cancel your attendance? This will also cancel all your bookings for activities, meals, and accommodations.', function() {
        var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        apiCall('/api/attendance-cancel.php', 'POST', {
            csrf_token: csrfToken
        }, function(err, response) {
            if (err) {
                showAlert(err.message || 'Failed to cancel attendance', 'danger');
            } else {
                showAlert('Attendance cancelled successfully', 'success');
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
            }
        });
    });
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>

