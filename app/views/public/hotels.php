<?php
$currentPage = 'hotels';
ob_start();
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
                                            <span class="<?php echo $room['available_rooms'] > 0 ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo e($room['available_rooms']); ?> / <?php echo e($room['total_rooms']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <?php if ($room['description']): ?>
                                        <div class="item-description">
                                            <?php echo nl2br(e($room['description'])); ?>
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
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
