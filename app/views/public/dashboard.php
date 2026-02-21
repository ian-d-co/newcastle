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
                <?php
                $activityTotal = 0;
                foreach ($activityBookings as $b) {
                    if ($b['requires_prepayment']) { $activityTotal += (float)$b['price']; }
                }
                ?>
                <?php if ($activityTotal > 0): ?>
                    <div class="section-action"><span class="badge badge-secondary">£<?php echo number_format($activityTotal, 2); ?> total</span></div>
                <?php endif; ?>
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
                <?php
                $mealTotal = 0;
                foreach ($mealBookings as $b) {
                    if ($b['requires_prepayment']) { $mealTotal += (float)$b['price']; }
                }
                ?>
                <?php if ($mealTotal > 0): ?>
                    <div class="section-action"><span class="badge badge-secondary">£<?php echo number_format($mealTotal, 2); ?> total</span></div>
                <?php endif; ?>
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
                <?php
                $accommodationTotal = 0;
                foreach ($hotelReservations as $r) { $accommodationTotal += (float)$r['total_price']; }
                ?>
                <?php if ($accommodationTotal > 0): ?>
                    <div class="section-action"><span class="badge badge-secondary">£<?php echo number_format($accommodationTotal, 2); ?> total</span></div>
                <?php endif; ?>
            </div>
            <div class="dashboard-section-content" style="display: none;">
                <?php if ($isAttending): ?>
                    <p>🚗 <strong>Travel Method:</strong> <?php echo implode(', ', $attendance['travel_method']); ?></p>
                <?php endif; ?>

                <?php if ($carshareOffer): ?>
                    <hr>
                    <p><strong>Car Share — Offering a ride</strong></p>
                    <p>From: <?php echo e($carshareOffer['origin']); ?></p>
                    <p>Capacity: <?php echo (int)$carshareOffer['passenger_capacity']; ?> passengers &bull; Available: <?php echo (int)$carshareOffer['available_spaces']; ?> spaces</p>
                <?php endif; ?>

                <?php if ($carshareBooking): ?>
                    <hr>
                    <p><strong>Car Share with:</strong> <?php echo e($carshareBooking['driver_name']); ?> (from <?php echo e($carshareBooking['origin']); ?>)</p>
                <?php endif; ?>

                <?php if ($hostingOffer): ?>
                    <hr>
                    <p>🏠 <strong>Hosting — Offering <?php echo e($hostingOffer['capacity']); ?> spaces</strong></p>
                    <p>Available spaces: <?php echo e($hostingOffer['available_spaces']); ?></p>
                    <?php if ($hostingOffer['notes']): ?>
                        <p>Notes: <?php echo nl2br(e($hostingOffer['notes'])); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($hostingPendingRequests)): ?>
                        <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; padding: 0.75rem; margin-top: 0.5rem;">
                            <strong>⚠ <?php echo count($hostingPendingRequests); ?> pending request<?php echo count($hostingPendingRequests) !== 1 ? 's' : ''; ?> to stay</strong>
                            <a href="/index.php?page=hosting" class="btn btn-sm btn-warning" style="margin-left: 0.75rem;">View & Respond</a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($hostingBooking): ?>
                    <hr>
                    <p>🏠 <strong>Staying with:</strong> <?php echo e($hostingBooking['host_name']); ?></p>
                    <?php if ($hostingBooking['notes']): ?>
                        <p>Notes: <?php echo nl2br(e($hostingBooking['notes'])); ?></p>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (!empty($userHostingRequest) && !$hostingBooking): ?>
                    <hr>
                    <p>🏠 <strong>Hosting request:</strong>
                        <?php if ($userHostingRequest['status'] === 'pending'): ?>
                            <span class="badge badge-warning">Pending response from host</span>
                        <?php elseif ($userHostingRequest['status'] === 'accepted'): ?>
                            <span class="badge badge-success">✓ Accepted</span>
                        <?php elseif ($userHostingRequest['status'] === 'declined'): ?>
                            <span class="badge badge-danger">Declined</span>
                        <?php endif; ?>
                    </p>
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
                    <a href="/index.php?page=carshare" class="btn btn-secondary btn-sm">View Car Share</a>
                <?php endif; ?>

                <?php if ($isAttending): ?>
                    <hr>
                    <button class="btn btn-secondary btn-sm" onclick="modalManager.open('update-attendance-modal')">Update Travel &amp; Hosting Details</button>
                <?php endif; ?>
            </div>
        </div>

        <!-- My Interests Section -->
        <?php
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT ui.*,
                   CASE ui.item_type
                       WHEN 'activity' THEN a.title
                       WHEN 'meal' THEN m.title
                       WHEN 'hotel_room' THEN CONCAT(h.name, ' - ', hr.room_type)
                   END as item_name,
                   CASE ui.item_type
                       WHEN 'activity' THEN a.day
                       WHEN 'meal' THEN m.day
                       ELSE NULL
                   END as item_day,
                   CASE ui.item_type
                       WHEN 'activity' THEN a.start_time
                       WHEN 'meal' THEN m.start_time
                       ELSE NULL
                   END as item_time,
                   CASE ui.item_type
                       WHEN 'activity' THEN a.price
                       WHEN 'meal' THEN m.price
                       WHEN 'hotel_room' THEN hr.price
                   END as item_price
            FROM user_interests ui
            LEFT JOIN activities a ON ui.item_type = 'activity' AND ui.item_id = a.id
            LEFT JOIN meals m ON ui.item_type = 'meal' AND ui.item_id = m.id
            LEFT JOIN hotel_rooms hr ON ui.item_type = 'hotel_room' AND ui.item_id = hr.id
            LEFT JOIN hotels h ON hr.hotel_id = h.id
            WHERE ui.user_id = :user_id AND ui.interest_level = 'interested'
            ORDER BY ui.updated_at DESC
        ");
        $stmt->execute(['user_id' => $userId]);
        $myInterests = $stmt->fetchAll();
        ?>
        <div class="dashboard-section">
            <div class="dashboard-section-header" onclick="toggleSection(this)">
                <h3><span class="toggle-icon">▶</span>My Interests<?php if (!empty($myInterests)): ?> (<?php echo count($myInterests); ?>)<?php endif; ?></h3>
            </div>
            <div class="dashboard-section-content" style="display: none;">
                <?php if (!empty($myInterests)): ?>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <?php foreach ($myInterests as $interest): ?>
                            <li style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px solid #eee;">
                                <div>
                                    <strong><?php echo e($interest['item_name']); ?></strong>
                                    <?php if ($interest['item_day']): ?>
                                        <span class="badge badge-secondary" style="margin-left: 0.5rem;"><?php echo e($interest['item_day']); ?></span>
                                    <?php endif; ?>
                                    <?php if ($interest['item_time']): ?>
                                        <span style="color: #666; font-size: 0.875rem; margin-left: 0.5rem;"><?php echo date('H:i', strtotime($interest['item_time'])); ?></span>
                                    <?php endif; ?>
                                    <?php if ($interest['item_price'] > 0): ?>
                                        <span style="color: #666; font-size: 0.875rem; margin-left: 0.5rem;">£<?php echo number_format($interest['item_price'], 2); ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php
                                $pageMap = ['activity' => 'activities', 'meal' => 'meals', 'hotel_room' => 'hotels'];
                                $page = $pageMap[$interest['item_type']] ?? e($interest['item_type']);
                                ?>
                                <a href="/index.php?page=<?php echo $page; ?>" class="btn btn-sm btn-primary" style="flex-shrink: 0; margin-left: 1rem;">View</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No interests marked yet. Browse <a href="/index.php?page=activities">activities</a>, <a href="/index.php?page=meals">meals</a>, or <a href="/index.php?page=hotels">hotels</a> to mark your interest.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- My Payments Section -->
        <?php
        $db2 = getDbConnection();
        $paymentStmt = $db2->prepare("
            SELECT 'activity' as type, a.title, a.day, a.price, ab.payment_status, ab.amount_due, ab.amount_paid
            FROM activity_bookings ab
            JOIN activities a ON ab.activity_id = a.id
            WHERE ab.user_id = :user_id1 AND a.event_id = :event_id1 AND a.price > 0
            UNION ALL
            SELECT 'meal' as type, m.title, m.day, m.price, mb.payment_status, mb.amount_due, mb.amount_paid
            FROM meal_bookings mb
            JOIN meals m ON mb.meal_id = m.id
            WHERE mb.user_id = :user_id2 AND m.event_id = :event_id2 AND m.price > 0
            UNION ALL
            SELECT 'hotel' as type, CONCAT(h.name, ' - ', hr.room_type) as title, NULL as day,
                   rr.total_price as price, rr.payment_status, rr.total_price as amount_due,
                   COALESCE(rr.amount_paid, 0) as amount_paid
            FROM room_reservations rr
            JOIN hotel_rooms hr ON rr.hotel_room_id = hr.id
            JOIN hotels h ON hr.hotel_id = h.id
            WHERE rr.user_id = :user_id3 AND h.event_id = :event_id3 AND rr.payment_status != 'cancelled'
            ORDER BY COALESCE(day, 'zzz'), type
        ");
        $paymentStmt->execute([
            'user_id1' => $userId,
            'event_id1' => $event['id'],
            'user_id2' => $userId,
            'event_id2' => $event['id'],
            'user_id3' => $userId,
            'event_id3' => $event['id']
        ]);
        $myPayments = $paymentStmt->fetchAll();
        $totalDue = array_sum(array_column($myPayments, 'amount_due')) ?: array_sum(array_column($myPayments, 'price'));
        $totalPaid = array_sum(array_column($myPayments, 'amount_paid') ?? []);
        ?>
        <?php if (!empty($myPayments)): ?>
        <div class="dashboard-section">
            <div class="dashboard-section-header" onclick="toggleSection(this)">
                <h3><span class="toggle-icon">▶</span>My Payments</h3>
                <div class="section-action">
                    <?php
                    $outstanding = 0;
                    foreach ($myPayments as $p) {
                        $due = (float)($p['amount_due'] ?: $p['price']);
                        $paid = (float)($p['amount_paid'] ?? 0);
                        $outstanding += $due - $paid;
                    }
                    ?>
                    <?php if ($outstanding > 0): ?>
                        <span class="badge badge-warning">£<?php echo number_format($outstanding, 2); ?> outstanding</span>
                    <?php else: ?>
                        <span class="badge badge-success">All paid</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="dashboard-section-content" style="display: none;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #dee2e6;">
                            <th style="padding: 0.5rem; text-align: left;">Item</th>
                            <th style="padding: 0.5rem; text-align: left;">Day</th>
                            <th style="padding: 0.5rem; text-align: center;">Amount Due</th>
                            <th style="padding: 0.5rem; text-align: center;">Amount Paid</th>
                            <th style="padding: 0.5rem; text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($myPayments as $p): ?>
                            <tr style="border-bottom: 1px solid #dee2e6;">
                                <td style="padding: 0.5rem;"><?php echo e($p['title']); ?> <small style="color: #666;">(<?php echo ucfirst($p['type']); ?>)</small></td>
                                <td style="padding: 0.5rem;"><?php echo e($p['day']); ?></td>
                                <td style="padding: 0.5rem; text-align: center;">£<?php echo number_format($p['amount_due'] ?: $p['price'], 2); ?></td>
                                <td style="padding: 0.5rem; text-align: center;">£<?php echo number_format($p['amount_paid'] ?? 0, 2); ?></td>
                                <td style="padding: 0.5rem; text-align: center;">
                                    <?php if ($p['payment_status'] === 'paid'): ?>
                                        <span class="badge badge-success">✓ Paid</span>
                                    <?php elseif ($p['payment_status'] === 'not_required'): ?>
                                        <span class="badge badge-secondary">N/A</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Pending</span>
                                    <?php endif; ?>
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
                        <label class="form-label">Can you offer a car share/lift?</label>
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

<!-- Update Attendance Modal (for users already attending) -->
<?php if ($isAttending): ?>
<?php
$updateDays = is_array($attendance['days_attending']) ? $attendance['days_attending'] : explode(',', $attendance['days_attending']);
$updateTravel = is_array($attendance['travel_method']) ? $attendance['travel_method'] : explode(',', $attendance['travel_method']);
$updateDays = array_map('trim', $updateDays);
$updateTravel = array_map('trim', $updateTravel);
?>
<div class="modal" id="update-attendance-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Update Travel &amp; Hosting Details</h3>
            <button class="modal-close" onclick="modalManager.close('update-attendance-modal')" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="update-attendance-form">
                <?php echo CSRF::field(); ?>

                <div class="form-group">
                    <label class="form-label">Days Attending *</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="upd-day-friday" name="days_attending[]" value="Friday"
                               <?php echo in_array('Friday', $updateDays) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="upd-day-friday">Friday (Nov 20)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="upd-day-saturday" name="days_attending[]" value="Saturday"
                               <?php echo in_array('Saturday', $updateDays) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="upd-day-saturday">Saturday (Nov 21)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="upd-day-sunday" name="days_attending[]" value="Sunday"
                               <?php echo in_array('Sunday', $updateDays) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="upd-day-sunday">Sunday (Nov 22)</label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Travel Method *</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="upd-travel-train" name="travel_method[]" value="Train"
                               <?php echo in_array('Train', $updateTravel) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="upd-travel-train">Train</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="upd-travel-plane" name="travel_method[]" value="Plane"
                               <?php echo in_array('Plane', $updateTravel) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="upd-travel-plane">Plane</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="upd-travel-car" name="travel_method[]" value="Car"
                               <?php echo in_array('Car', $updateTravel) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="upd-travel-car">Car</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="upd-travel-coach" name="travel_method[]" value="Coach"
                               <?php echo in_array('Coach', $updateTravel) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="upd-travel-coach">Coach</label>
                    </div>
                </div>

                <div id="upd-carshare-section" style="display: <?php echo in_array('Car', $updateTravel) ? 'block' : 'none'; ?>;">
                    <div class="form-group">
                        <label class="form-label">Can you offer a car share/lift?</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="upd-can-carshare-yes" name="can_carshare" value="yes"
                                   <?php echo $carshareOffer ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="upd-can-carshare-yes">Yes</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="upd-can-carshare-no" name="can_carshare" value="no"
                                   <?php echo (!$carshareOffer && in_array('Car', $updateTravel)) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="upd-can-carshare-no">No</label>
                        </div>
                    </div>
                    <div id="upd-carshare-details" style="display: <?php echo $carshareOffer ? 'block' : 'none'; ?>;">
                        <div class="form-group">
                            <label class="form-label" for="upd-carshare-origin">Where are you travelling from?</label>
                            <input type="text" class="form-control" id="upd-carshare-origin" name="carshare_origin"
                                   value="<?php echo e($carshareOffer['origin'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="upd-carshare-capacity">How many passengers can you take?</label>
                            <input type="number" class="form-control" id="upd-carshare-capacity" name="carshare_capacity"
                                   min="1" max="8" value="<?php echo (int)($carshareOffer['passenger_capacity'] ?? 0) ?: ''; ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Can you host people?</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" id="upd-can-host-yes" name="can_host" value="yes"
                               <?php echo $hostingOffer ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="upd-can-host-yes">Yes</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" id="upd-can-host-no" name="can_host" value="no"
                               <?php echo !$hostingOffer ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="upd-can-host-no">No</label>
                    </div>
                </div>

                <div id="upd-hosting-details" style="display: <?php echo $hostingOffer ? 'block' : 'none'; ?>;">
                    <div class="form-group">
                        <label class="form-label" for="upd-hosting-capacity">How many people can you host?</label>
                        <input type="number" class="form-control" id="upd-hosting-capacity" name="hosting_capacity"
                               min="1" max="20" value="<?php echo (int)($hostingOffer['capacity'] ?? 0) ?: ''; ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="upd-hosting-notes">Additional notes (optional)</label>
                        <textarea class="form-control" id="upd-hosting-notes" name="hosting_notes" rows="3"><?php echo e($hostingOffer['notes'] ?? ''); ?></textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg">Save Changes</button>
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

document.addEventListener('DOMContentLoaded', function() {
    var attendanceForm = document.getElementById('attendance-form');
    if (!attendanceForm) return;

    var travelCar = document.getElementById('travel-car');
    if (travelCar) {
        travelCar.addEventListener('change', function() {
            document.getElementById('carshare-section').style.display = this.checked ? 'block' : 'none';
        });
    }

    var canCarshareYes = document.getElementById('can-carshare-yes');
    var canCarshareNo = document.getElementById('can-carshare-no');
    if (canCarshareYes) {
        canCarshareYes.addEventListener('change', function() {
            document.getElementById('carshare-details').style.display = this.checked ? 'block' : 'none';
        });
    }
    if (canCarshareNo) {
        canCarshareNo.addEventListener('change', function() {
            document.getElementById('carshare-details').style.display = 'none';
        });
    }

    var canHostYes = document.getElementById('can-host-yes');
    var canHostNo = document.getElementById('can-host-no');
    if (canHostYes) {
        canHostYes.addEventListener('change', function() {
            document.getElementById('hosting-details').style.display = this.checked ? 'block' : 'none';
        });
    }
    if (canHostNo) {
        canHostNo.addEventListener('change', function() {
            document.getElementById('hosting-details').style.display = 'none';
        });
    }

    attendanceForm.addEventListener('submit', function(e) {
    e.preventDefault();

    var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    var form = this;
    var days = Array.from(form.querySelectorAll('input[name="days_attending[]"]:checked')).map(function(el) { return el.value; });
    var travel = Array.from(form.querySelectorAll('input[name="travel_method[]"]:checked')).map(function(el) { return el.value; });

    if (days.length === 0) {
        showAlert('Please select at least one day', 'danger');
        return;
    }

    if (travel.length === 0) {
        showAlert('Please select at least one travel method', 'danger');
        return;
    }

    var canCarshare = form.querySelector('input[name="can_carshare"]:checked');
    var canHost = form.querySelector('input[name="can_host"]:checked');

    var data = {
        csrf_token: csrfToken,
        days_attending: days,
        travel_method: travel
    };

    if (canCarshare && canCarshare.value === 'yes') {
        data.carshare_origin = form.carshare_origin.value;
        data.carshare_capacity = form.carshare_capacity.value;
    }

    if (canHost && canHost.value === 'yes') {
        data.hosting_capacity = form.hosting_capacity.value;
        data.hosting_notes = form.hosting_notes.value;
    }

    apiCall('/api/attendance-update.php', 'POST', data, function(err, response) {
        if (err) {
            showAlert(err.message || 'Failed to register attendance', 'danger');
        } else {
            showAlert('Attendance registered successfully!', 'success');
            setTimeout(function() { location.reload(); }, 1000);
        }
    });
});
}); // end DOMContentLoaded

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

// Update attendance modal JS (for users already attending)
document.addEventListener('DOMContentLoaded', function() {
    var updTravelCar = document.getElementById('upd-travel-car');
    if (updTravelCar) {
        updTravelCar.addEventListener('change', function() {
            document.getElementById('upd-carshare-section').style.display = this.checked ? 'block' : 'none';
        });
    }
    var updCanCarshareYes = document.getElementById('upd-can-carshare-yes');
    var updCanCarshareNo = document.getElementById('upd-can-carshare-no');
    if (updCanCarshareYes) {
        updCanCarshareYes.addEventListener('change', function() {
            document.getElementById('upd-carshare-details').style.display = this.checked ? 'block' : 'none';
        });
    }
    if (updCanCarshareNo) {
        updCanCarshareNo.addEventListener('change', function() {
            document.getElementById('upd-carshare-details').style.display = 'none';
        });
    }
    var updCanHostYes = document.getElementById('upd-can-host-yes');
    var updCanHostNo = document.getElementById('upd-can-host-no');
    if (updCanHostYes) {
        updCanHostYes.addEventListener('change', function() {
            document.getElementById('upd-hosting-details').style.display = this.checked ? 'block' : 'none';
        });
    }
    if (updCanHostNo) {
        updCanHostNo.addEventListener('change', function() {
            document.getElementById('upd-hosting-details').style.display = 'none';
        });
    }

    var updateForm = document.getElementById('update-attendance-form');
    if (updateForm) {
        updateForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            var form = this;
            var days = Array.from(form.querySelectorAll('input[name="days_attending[]"]:checked')).map(function(el) { return el.value; });
            var travel = Array.from(form.querySelectorAll('input[name="travel_method[]"]:checked')).map(function(el) { return el.value; });
            if (days.length === 0) { showAlert('Please select at least one day', 'danger'); return; }
            if (travel.length === 0) { showAlert('Please select at least one travel method', 'danger'); return; }
            var canCarshare = form.querySelector('input[name="can_carshare"]:checked');
            var canHost = form.querySelector('input[name="can_host"]:checked');
            var data = { csrf_token: csrfToken, days_attending: days, travel_method: travel };
            if (canCarshare && canCarshare.value === 'yes') {
                data.carshare_origin = form.carshare_origin.value;
                data.carshare_capacity = form.carshare_capacity.value;
            }
            if (canHost && canHost.value === 'yes') {
                data.hosting_capacity = form.hosting_capacity.value;
                data.hosting_notes = form.hosting_notes.value;
            }
            apiCall('/api/attendance-update.php', 'POST', data, function(err, response) {
                if (err) {
                    showAlert(err.message || 'Failed to update attendance', 'danger');
                } else {
                    showAlert('Attendance updated successfully!', 'success');
                    setTimeout(function() { location.reload(); }, 1000);
                }
            });
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>

