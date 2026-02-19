<?php
$currentPage = 'carshare';
ob_start();
?>

<div class="section">
    <div class="container">
        <h1 class="section-title">Carshare</h1>

        <?php if ($userOffer): ?>
            <div class="card mb-3">
                <div class="card-header">Your Carshare Offer</div>
                <div class="card-body">
                    <p><strong>Travelling from:</strong> <?php echo e($userOffer['origin']); ?></p>
                    <p><strong>Passenger capacity:</strong> <?php echo e($userOffer['passenger_capacity']); ?></p>
                    <p><strong>Available spaces:</strong> <?php echo e($userOffer['available_spaces']); ?></p>
                    <?php if ($offerBookings): ?>
                        <p><strong>Passengers:</strong></p>
                        <ul>
                            <?php foreach ($offerBookings as $booking): ?>
                                <li><?php echo displayName($booking['discord_name']); ?> (<?php echo displayName($booking['name']); ?>)</li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($userBooking): ?>
            <div class="card mb-3">
                <div class="card-header bg-success text-white">Your Carshare Booking</div>
                <div class="card-body">
                    <p><strong>Driver:</strong> <?php echo displayName($userBooking['driver_name']); ?></p>
                    <p><strong>Travelling from:</strong> <?php echo e($userBooking['origin']); ?></p>
                    <button class="btn btn-danger btn-sm" onclick="cancelCarshare(<?php echo $userBooking['carshare_offer_id']; ?>)" <?php if (isGuestMode()): ?>disabled title="Please log in to manage bookings"<?php endif; ?>>Cancel Booking</button>
                </div>
            </div>
        <?php endif; ?>

        <h2 class="text-primary mt-4 mb-3">Available Carshares</h2>

        <?php if (empty($availableOffers)): ?>
            <div class="card">
                <div class="card-body text-center">
                    <p>No carshare offers available yet. Be the first to offer!</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($availableOffers as $offer): ?>
                <div class="item">
                    <div class="item-header">
                        <h3 class="item-title">Offered by: <?php echo displayName($offer['discord_name']); ?></h3>
                    </div>
                    
                    <div class="item-meta">
                        <div class="item-meta-item">
                            <strong>From:</strong> <?php echo e($offer['origin']); ?>
                        </div>
                        <div class="item-meta-item">
                            <strong>Available spaces:</strong> 
                            <span class="<?php echo $offer['available_spaces'] > 0 ? 'text-success' : 'text-danger'; ?>">
                                <?php echo e($offer['available_spaces']); ?> / <?php echo e($offer['passenger_capacity']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="item-footer">
                        <?php if ($offer['available_spaces'] > 0 && !$userBooking && $offer['user_id'] != getCurrentUserId()): ?>
                            <button class="btn btn-primary" onclick="bookCarshare(<?php echo $offer['id']; ?>)" <?php if (isGuestMode()): ?>disabled title="Please log in to book"<?php endif; ?>>Book Carshare</button>
                        <?php elseif ($offer['available_spaces'] <= 0): ?>
                            <span class="badge badge-danger">Full</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
