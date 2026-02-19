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
                        <?php if ($hotel['address']): ?>
                            <p><strong>Address:</strong> <?php echo e($hotel['address']); ?></p>
                        <?php endif; ?>
                        
                        <?php if ($hotel['description']): ?>
                            <p><?php echo nl2br(e($hotel['description'])); ?></p>
                        <?php endif; ?>
                        
                        <?php if ($hotel['website']): ?>
                            <p><strong>Website:</strong> <a href="<?php echo e($hotel['website']); ?>" target="_blank"><?php echo e($hotel['website']); ?></a></p>
                        <?php endif; ?>

                        <?php if (!empty($hotel['rooms'])): ?>
                            <h4 class="mt-3 mb-2">Available Rooms</h4>
                            
                            <?php foreach ($hotel['rooms'] as $room): ?>
                                <div class="item" style="margin-bottom: 1rem;">
                                    <div class="item-header">
                                        <h5 class="item-title"><?php echo e($room['room_type']); ?></h5>
                                    </div>
                                    
                                    <div class="item-meta">
                                        <div class="item-meta-item">
                                            <strong>Capacity:</strong> <?php echo e($room['capacity']); ?> people
                                        </div>
                                        <div class="item-meta-item">
                                            <strong>Price:</strong> £<?php echo number_format($room['price_per_night'], 2); ?> per night
                                        </div>
                                        <div class="item-meta-item">
                                            <strong>Available:</strong> 
                                            <?php
                                            $occupancyPct = $room['total_rooms'] > 0 ? (($room['total_rooms'] - $room['available_rooms']) / $room['total_rooms']) * 100 : 100;
                                            $capacityClass = $occupancyPct >= 95 ? 'text-danger' : ($occupancyPct >= 71 ? 'text-warning' : 'text-success');
                                            ?>
                                            <span class="<?php echo $capacityClass; ?>">
                                                <?php echo e($room['available_rooms']); ?> / <?php echo e($room['total_rooms']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <?php if ($room['description']): ?>
                                        <div class="item-description">
                                            <?php echo nl2br(e($room['description'])); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($room['confirmation_deadline'])): ?>
                                        <div class="deadline-warning" style="margin: 0.5rem 0; padding: 0.5rem; background: #fff3cd; border-radius: 4px; font-size: 0.875rem;">
                                            <strong>⏰ Confirmation Deadline:</strong>
                                            <?php echo e(date('F j, Y g:i A', strtotime($room['confirmation_deadline']))); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($room['payment_deadline'])): ?>
                                        <div class="deadline-warning" style="margin: 0.5rem 0; padding: 0.5rem; background: #fff3cd; border-radius: 4px; font-size: 0.875rem;">
                                            <strong>⏰ Payment Deadline:</strong>
                                            <?php echo e(date('F j, Y g:i A', strtotime($room['payment_deadline']))); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!isGuestMode()): ?>
                                    <div class="interest-selector" data-item-type="hotel_room" data-item-id="<?php echo $room['id']; ?>" style="margin: 0.75rem 0;">
                                        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                            <small style="color: #666;">Your interest:</small>
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
                                        <?php elseif ($room['available_rooms'] > 0): ?>
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
                                                <div class="form-group">
                                                    <label class="form-label" for="check-in-<?php echo $room['id']; ?>">Check-in Date</label>
                                                    <input type="date" class="form-control" id="check-in-<?php echo $room['id']; ?>" name="check_in" min="2026-11-20" required>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label class="form-label" for="check-out-<?php echo $room['id']; ?>">Check-out Date</label>
                                                    <input type="date" class="form-control" id="check-out-<?php echo $room['id']; ?>" name="check_out" min="2026-11-20" required>
                                                </div>
                                                
                                                <p><strong>Price per night:</strong> £<?php echo number_format($room['price_per_night'], 2); ?></p>
                                                
                                                <button type="submit" class="btn btn-primary btn-block">Reserve Room</button>
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
                        <p><strong>Check-in:</strong> <?php echo formatDisplayDate($reservation['check_in']); ?></p>
                        <p><strong>Check-out:</strong> <?php echo formatDisplayDate($reservation['check_out']); ?></p>
                        <p><strong>Nights:</strong> <?php echo e($reservation['total_nights']); ?></p>
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
function submitReservation(roomId) {
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
