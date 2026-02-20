<?php
$currentPage = 'hotels';
ob_start();

// Get user's interests for hotel rooms
$db = getDbConnection();
$userInterestsMap = [];
$interestStatsMap = [];

$allRoomIds = [];
foreach ($hotels as $hotel) {
    foreach ($hotel['rooms'] as $room) {
        $allRoomIds[] = $room['id'];
    }
}

if (!empty($allRoomIds)) {
    $placeholders = implode(',', array_fill(0, count($allRoomIds), '?'));

    $stmt = $db->prepare("SELECT item_id, interest_level FROM user_interests WHERE user_id = ? AND item_type = 'hotel_room' AND item_id IN ($placeholders)");
    $stmt->execute(array_merge([$userId], $allRoomIds));
    foreach ($stmt->fetchAll() as $row) {
        $userInterestsMap[$row['item_id']] = $row['interest_level'];
    }

    $stmt = $db->prepare("SELECT item_id, interest_level, COUNT(*) as cnt FROM user_interests WHERE item_type = 'hotel_room' AND item_id IN ($placeholders) GROUP BY item_id, interest_level");
    $stmt->execute($allRoomIds);
    foreach ($stmt->fetchAll() as $row) {
        $interestStatsMap[$row['item_id']][$row['interest_level']] = $row['cnt'];
    }
}
?>

<div class="section">
    <div class="container">
        <h1 class="section-title">Hotels & Accommodation</h1>

        <?php if (empty($hotels)): ?>
            <div class="card">
                <div class="card-body text-center">
                    <p>No hotels listed yet. Check back later!</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($hotels as $hotel): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title"><?php echo e($hotel['name']); ?></h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($hotel['location'])): ?>
                            <p><strong>Location:</strong> <?php echo e($hotel['location']); ?></p>
                        <?php endif; ?>

                        <?php if (!empty($hotel['contact_phone'])): ?>
                            <p><strong>Phone:</strong> <?php echo e($hotel['contact_phone']); ?></p>
                        <?php endif; ?>

                        <?php if (!empty($hotel['contact_email'])): ?>
                            <p><strong>Email:</strong> <?php echo e($hotel['contact_email']); ?></p>
                        <?php endif; ?>

                        <?php if (!empty($hotel['description'])): ?>
                            <p><?php echo nl2br(e($hotel['description'])); ?></p>
                        <?php endif; ?>

                        <?php if (!empty($hotel['link'])): ?>
                            <p><strong>Link:</strong> <a href="<?php echo e($hotel['link']); ?>" target="_blank" rel="noopener noreferrer"><?php echo e($hotel['link']); ?></a></p>
                        <?php endif; ?>

                        <?php if (!empty($hotel['rooms'])): ?>
                            <h4 class="mt-3 mb-2">Available Rooms</h4>
                            
                            <?php foreach ($hotel['rooms'] as $room): ?>
                                <div class="item" style="margin-bottom: 1rem;">
                                    <div class="item-header">
                                        <h5 class="item-title">Room Type: <?php echo e($room['room_type']); ?></h5>
                                    </div>

                                    <p style="margin: 0.25rem 0;">
                                        <strong>Capacity:</strong> <?php echo e($room['capacity']); ?> people
                                        &nbsp;|&nbsp;
                                        <?php
                                        $totalRooms = $room['quantity_available'] + ($room['quantity_reserved'] ?? 0);
                                        $occupancyPct = $totalRooms > 0 ? (($room['quantity_reserved'] ?? 0) / $totalRooms) * 100 : 0;
                                        $capacityClass = $occupancyPct >= 95 ? 'capacity-red' : ($occupancyPct >= 71 ? 'capacity-amber' : 'capacity-green');
                                        ?>
                                        <span class="<?php echo $capacityClass; ?>">
                                            <?php echo e($room['quantity_available']); ?> available
                                            <?php if (isset($room['quantity_reserved']) && $room['quantity_reserved'] > 0): ?>
                                                (<?php echo e($room['quantity_reserved']); ?> reserved)
                                            <?php endif; ?>
                                        </span>
                                    </p>

                                    <?php
                                    $hasSingleFri = !empty($room['single_price_friday']) && $room['single_price_friday'] > 0;
                                    $hasSingleSat = !empty($room['single_price_saturday']) && $room['single_price_saturday'] > 0;
                                    $hasDoubleFri = !empty($room['double_price_friday']) && $room['double_price_friday'] > 0;
                                    $hasDoubleSat = !empty($room['double_price_saturday']) && $room['double_price_saturday'] > 0;
                                    $hasTripleFri = !empty($room['triple_price_friday']) && $room['triple_price_friday'] > 0;
                                    $hasTripleSat = !empty($room['triple_price_saturday']) && $room['triple_price_saturday'] > 0;
                                    $hasPricing = $hasSingleFri || $hasSingleSat || $hasDoubleFri || $hasDoubleSat || $hasTripleFri || $hasTripleSat;
                                    ?>

                                    <div style="margin: 0.75rem 0;">
                                        <strong>Pricing:</strong>
                                        <?php if ($hasPricing): ?>
                                        <table style="border-collapse: collapse; margin-top: 0.25rem; font-size: 0.875rem;">
                                            <thead>
                                                <tr>
                                                    <th style="padding: 0.25rem 0.5rem; border: 1px solid #dee2e6;"></th>
                                                    <th style="padding: 0.25rem 0.5rem; border: 1px solid #dee2e6;">Friday</th>
                                                    <th style="padding: 0.25rem 0.5rem; border: 1px solid #dee2e6;">Saturday</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if ($hasSingleFri || $hasSingleSat): ?>
                                                <tr>
                                                    <td style="padding: 0.25rem 0.5rem; border: 1px solid #dee2e6;">Single</td>
                                                    <td style="padding: 0.25rem 0.5rem; border: 1px solid #dee2e6;"><?php echo $hasSingleFri ? '£' . number_format($room['single_price_friday'], 2) : '-'; ?></td>
                                                    <td style="padding: 0.25rem 0.5rem; border: 1px solid #dee2e6;"><?php echo $hasSingleSat ? '£' . number_format($room['single_price_saturday'], 2) : '-'; ?></td>
                                                </tr>
                                                <?php endif; ?>
                                                <?php if ($hasDoubleFri || $hasDoubleSat): ?>
                                                <tr>
                                                    <td style="padding: 0.25rem 0.5rem; border: 1px solid #dee2e6;">Double</td>
                                                    <td style="padding: 0.25rem 0.5rem; border: 1px solid #dee2e6;"><?php echo $hasDoubleFri ? '£' . number_format($room['double_price_friday'], 2) : '-'; ?></td>
                                                    <td style="padding: 0.25rem 0.5rem; border: 1px solid #dee2e6;"><?php echo $hasDoubleSat ? '£' . number_format($room['double_price_saturday'], 2) : '-'; ?></td>
                                                </tr>
                                                <?php endif; ?>
                                                <?php if ($hasTripleFri || $hasTripleSat): ?>
                                                <tr>
                                                    <td style="padding: 0.25rem 0.5rem; border: 1px solid #dee2e6;">Triple</td>
                                                    <td style="padding: 0.25rem 0.5rem; border: 1px solid #dee2e6;"><?php echo $hasTripleFri ? '£' . number_format($room['triple_price_friday'], 2) : '-'; ?></td>
                                                    <td style="padding: 0.25rem 0.5rem; border: 1px solid #dee2e6;"><?php echo $hasTripleSat ? '£' . number_format($room['triple_price_saturday'], 2) : '-'; ?></td>
                                                </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                        <?php else: ?>
                                        <span style="margin-left: 0.25rem;">£<?php echo number_format($room['price'], 2); ?> per night</span>
                                        <?php endif; ?>
                                    </div>

                                    <div style="margin: 0.75rem 0;">
                                        <strong>Booking information:</strong>
                                        <div style="margin-top: 0.35rem;">
                                            <?php if (!empty($room['book_with_group'])): ?>
                                            <div>&#9745; Book with group
                                                <?php if (!empty($room['group_payment_due'])): ?>
                                                    <small style="margin-left: 0.5rem;">(Payment due by: <?php echo e(formatDisplayDate($room['group_payment_due'])); ?>)</small>
                                                <?php endif; ?>
                                            </div>
                                            <?php else: ?>
                                            <div style="color: #999;">&#9744; Book with group</div>
                                            <?php endif; ?>
                                            <?php if (!empty($room['book_direct_with_hotel'])): ?>
                                            <div>&#9745; Book direct with hotel (no payment tracking)</div>
                                            <?php else: ?>
                                            <div style="color: #999;">&#9744; Book direct with hotel (no payment tracking)</div>
                                            <?php endif; ?>
                                            <?php if ($room['breakfast_included']): ?>
                                            <div>&#9745; Breakfast included</div>
                                            <?php else: ?>
                                            <div style="color: #999;">&#9744; Breakfast included</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <?php if (!isGuestMode()): ?>
                                    <div class="interest-selector" data-item-type="hotel_room" data-item-id="<?php echo $room['id']; ?>" style="margin: 0.75rem 0;">
                                        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                            <small style="color: #666;">Interested?</small>
                                            <?php
                                            $userLevel = $userInterestsMap[$room['id']] ?? null;
                                            $stats = $interestStatsMap[$room['id']] ?? [];
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
                                        <?php if (isGuestMode()): ?>
                                            <button class="btn btn-primary" disabled>Reserve (Login Required)</button>
                                        <?php elseif (!hasRegisteredAttendance()): ?>
                                            <button class="btn btn-warning" onclick="showAttendanceRequired()">Register Attendance First</button>
                                        <?php elseif ($room['quantity_available'] > 0): ?>
                                            <button class="btn btn-primary" onclick="modalManager.open('reserve-modal-<?php echo $room['id']; ?>')">
                                                Reserve Room
                                            </button>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Fully Booked</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Reservation Modal -->
                                <div class="modal" id="reserve-modal-<?php echo $room['id']; ?>">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h3 class="modal-title">Reserve <?php echo e($room['room_type']); ?></h3>
                                            <button class="modal-close" onclick="modalManager.close('reserve-modal-<?php echo $room['id']; ?>')">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <form id="reserve-form-<?php echo $room['id']; ?>" onsubmit="event.preventDefault(); submitReservation(<?php echo $room['id']; ?>);">
                                                <?php
                                                $hasSingle = (!empty($room['single_price_friday']) && $room['single_price_friday'] > 0) || (!empty($room['single_price_saturday']) && $room['single_price_saturday'] > 0);
                                                $hasDouble = (!empty($room['double_price_friday']) && $room['double_price_friday'] > 0) || (!empty($room['double_price_saturday']) && $room['double_price_saturday'] > 0);
                                                $hasTriple = (!empty($room['triple_price_friday']) && $room['triple_price_friday'] > 0) || (!empty($room['triple_price_saturday']) && $room['triple_price_saturday'] > 0);
                                                $hasOccupancyPricing = $hasSingle || $hasDouble || $hasTriple;
                                                ?>

                                                <?php if ($hasOccupancyPricing): ?>
                                                <div class="form-group">
                                                    <label class="form-label">Occupancy Type *</label>
                                                    <select class="form-control" id="occupancy-<?php echo $room['id']; ?>" onchange="calculatePrice(<?php echo $room['id']; ?>)" required>
                                                        <option value="">Select...</option>
                                                        <?php if ($hasSingle): ?><option value="single">Single</option><?php endif; ?>
                                                        <?php if ($hasDouble): ?><option value="double">Double</option><?php endif; ?>
                                                        <?php if ($hasTriple): ?><option value="triple">Triple</option><?php endif; ?>
                                                    </select>
                                                </div>

                                                <div class="form-group">
                                                    <label class="form-label">Which nights?</label>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="friday-<?php echo $room['id']; ?>" value="friday" onchange="calculatePrice(<?php echo $room['id']; ?>)">
                                                        <label class="form-check-label" for="friday-<?php echo $room['id']; ?>">Friday night</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="saturday-<?php echo $room['id']; ?>" value="saturday" onchange="calculatePrice(<?php echo $room['id']; ?>)">
                                                        <label class="form-check-label" for="saturday-<?php echo $room['id']; ?>">Saturday night</label>
                                                    </div>
                                                </div>

                                                <div id="price-display-<?php echo $room['id']; ?>" style="padding: 0.5rem; background: #f8f9fa; border-radius: 4px; margin: 0.5rem 0; display: none;">
                                                    <strong>Estimated Total: <span id="price-amount-<?php echo $room['id']; ?>">£0.00</span></strong>
                                                </div>

                                                <?php if (!empty($room['book_direct_with_hotel'])): ?>
                                                <div class="form-group" style="margin-top: 0.5rem;">
                                                    <label style="display: flex; align-items: center; cursor: pointer;">
                                                        <input type="checkbox" id="book-direct-<?php echo $room['id']; ?>" style="margin-right: 0.5rem;">
                                                        Book Direct with Hotel (no payment tracking)
                                                    </label>
                                                </div>
                                                <?php endif; ?>

                                                <?php if (!empty($room['book_with_group'])): ?>
                                                <div class="form-group" style="margin-top: 0.5rem;">
                                                    <label style="display: flex; align-items: center; cursor: pointer;">
                                                        <input type="checkbox" id="book-with-group-<?php echo $room['id']; ?>" style="margin-right: 0.5rem;" onchange="toggleGroupPaymentInfo(<?php echo $room['id']; ?>)">
                                                        Book with the Group
                                                    </label>
                                                    <?php if (!empty($room['group_payment_due'])): ?>
                                                    <div id="group-payment-info-<?php echo $room['id']; ?>" style="display: none; margin-top: 0.25rem; padding: 0.4rem 0.75rem; background: #d4edda; border-radius: 4px; font-size: 0.875rem;">
                                                        <strong>Payment due by:</strong> <?php echo e(formatDisplayDate($room['group_payment_due'])); ?>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                                <?php endif; ?>

                                                <script>
                                                var roomPrices_<?php echo $room['id']; ?> = {
                                                    single_friday: <?php echo (float)($room['single_price_friday'] ?? 0); ?>,
                                                    single_saturday: <?php echo (float)($room['single_price_saturday'] ?? 0); ?>,
                                                    double_friday: <?php echo (float)($room['double_price_friday'] ?? 0); ?>,
                                                    double_saturday: <?php echo (float)($room['double_price_saturday'] ?? 0); ?>,
                                                    triple_friday: <?php echo (float)($room['triple_price_friday'] ?? 0); ?>,
                                                    triple_saturday: <?php echo (float)($room['triple_price_saturday'] ?? 0); ?>
                                                };
                                                function calculatePrice(roomId) {
                                                    var prices = window['roomPrices_' + roomId];
                                                    var occ = document.getElementById('occupancy-' + roomId).value;
                                                    var fri = document.getElementById('friday-' + roomId).checked;
                                                    var sat = document.getElementById('saturday-' + roomId).checked;
                                                    var total = 0;
                                                    if (occ && prices) {
                                                        if (fri) total += prices[occ + '_friday'] || 0;
                                                        if (sat) total += prices[occ + '_saturday'] || 0;
                                                    }
                                                    var display = document.getElementById('price-display-' + roomId);
                                                    var amount = document.getElementById('price-amount-' + roomId);
                                                    if (total > 0) {
                                                        display.style.display = 'block';
                                                        amount.textContent = '£' + total.toFixed(2);
                                                    } else {
                                                        display.style.display = 'none';
                                                    }
                                                }
                                                </script>

                                                <button type="submit" class="btn btn-primary btn-block">Reserve Room</button>

                                                <?php else: ?>
                                                <div class="form-group">
                                                    <label class="form-label" for="check-in-<?php echo $room['id']; ?>">Check-in Date</label>
                                                    <input type="date" class="form-control" id="check-in-<?php echo $room['id']; ?>" name="check_in" min="2026-11-20" required>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label class="form-label" for="check-out-<?php echo $room['id']; ?>">Check-out Date</label>
                                                    <input type="date" class="form-control" id="check-out-<?php echo $room['id']; ?>" name="check_out" min="2026-11-20" required>
                                                </div>
                                                
                                                <p><strong>Price per night:</strong> £<?php echo number_format($room['price'], 2); ?></p>
                                                
                                                <button type="submit" class="btn btn-primary btn-block">Reserve Room</button>
                                                <?php endif; ?>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($userReservations)): ?>
            <h2 class="text-primary mt-4 mb-3">Your Reservations</h2>
            
            <?php foreach ($userReservations as $reservation): ?>
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        <?php echo e($reservation['hotel_name']); ?> - <?php echo e($reservation['room_type']); ?>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($reservation['check_in'])): ?>
                        <p><strong>Check-in:</strong> <?php echo formatDisplayDate($reservation['check_in']); ?></p>
                        <p><strong>Check-out:</strong> <?php echo formatDisplayDate($reservation['check_out']); ?></p>
                        <?php else: ?>
                        <p><strong>Nights:</strong>
                            <?php
                            $nightLabels = [];
                            if ($reservation['friday_night']) $nightLabels[] = 'Friday';
                            if ($reservation['saturday_night']) $nightLabels[] = 'Saturday';
                            echo e(implode(', ', $nightLabels) ?: '-');
                            ?>
                        </p>
                        <?php if ($reservation['occupancy_type']): ?>
                        <p><strong>Occupancy:</strong> <?php echo e(ucfirst($reservation['occupancy_type'])); ?></p>
                        <?php endif; ?>
                        <?php endif; ?>
                        <p><strong>Total nights:</strong> <?php echo e($reservation['total_nights']); ?></p>
                        <p><strong>Total price:</strong> £<?php echo number_format($reservation['total_price'], 2); ?></p>
                        <p><strong>Payment status:</strong> 
                            <?php if ($reservation['payment_status'] === 'paid'): ?>
                                <span class="badge badge-success">Paid</span>
                            <?php elseif ($reservation['payment_status'] === 'pending'): ?>
                                <span class="badge badge-warning">Pending</span>
                            <?php endif; ?>
                        </p>
                        <button class="btn btn-danger btn-sm" onclick="cancelReservation(<?php echo $reservation['id']; ?>)">Cancel Reservation</button>
                    </div>
                </div>
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

function toggleGroupPaymentInfo(roomId) {
    var cb = document.getElementById('book-with-group-' + roomId);
    var info = document.getElementById('group-payment-info-' + roomId);
    if (info) {
        info.style.display = cb.checked ? 'block' : 'none';
    }
}

function submitReservation(roomId) {
    const occupancyEl = document.getElementById('occupancy-' + roomId);
    if (occupancyEl) {
        // Occupancy-based pricing mode
        const occupancyType = occupancyEl.value;
        const fridayNight = document.getElementById('friday-' + roomId).checked;
        const saturdayNight = document.getElementById('saturday-' + roomId).checked;

        if (!occupancyType) {
            showAlert('Please select an occupancy type', 'warning');
            return;
        }
        if (!fridayNight && !saturdayNight) {
            showAlert('Please select at least one night', 'warning');
            return;
        }

        const nights = [];
        if (fridayNight) nights.push('friday');
        if (saturdayNight) nights.push('saturday');

        const bookDirectEl = document.getElementById('book-direct-' + roomId);
        const bookWithGroupEl = document.getElementById('book-with-group-' + roomId);

        reserveRoom(roomId, null, null, {
            occupancy_type: occupancyType,
            nights: nights,
            book_direct: bookDirectEl ? bookDirectEl.checked : false,
            book_with_group: bookWithGroupEl ? bookWithGroupEl.checked : false
        });
    } else {
        // Date-based pricing mode
        const checkIn = document.getElementById('check-in-' + roomId).value;
        const checkOut = document.getElementById('check-out-' + roomId).value;

        if (!checkIn || !checkOut) {
            showAlert('Please select check-in and check-out dates', 'warning');
            return;
        }

        if (checkOut <= checkIn) {
            showAlert('Check-out date must be after check-in date', 'warning');
            return;
        }

        reserveRoom(roomId, checkIn, checkOut);
    }
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
                    setTimeout(function() { location.reload(); }, 800);
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
