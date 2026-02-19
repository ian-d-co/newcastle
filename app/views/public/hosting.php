<?php
$currentPage = 'hosting';
ob_start();
?>

<div class="section">
    <div class="container">
        <h1 class="section-title">Hosting</h1>

        <?php if ($userOffer): ?>
            <div class="card mb-3">
                <div class="card-header">Your Hosting Offer</div>
                <div class="card-body">
                    <p><strong>Capacity:</strong> <?php echo e($userOffer['capacity']); ?> people</p>
                    <p><strong>Available spaces:</strong> <?php echo e($userOffer['available_spaces']); ?></p>
                    <?php if ($userOffer['notes']): ?>
                        <p><strong>Notes:</strong> <?php echo nl2br(e($userOffer['notes'])); ?></p>
                    <?php endif; ?>
                    <?php if ($offerBookings): ?>
                        <p><strong>Guests:</strong></p>
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
                <div class="card-header bg-success text-white">Your Hosting Booking</div>
                <div class="card-body">
                    <p><strong>Host:</strong> <?php echo displayName($userBooking['host_name']); ?></p>
                    <?php if ($userBooking['notes']): ?>
                        <p><strong>Notes:</strong> <?php echo nl2br(e($userBooking['notes'])); ?></p>
                    <?php endif; ?>
                    <button class="btn btn-danger btn-sm" onclick="cancelHosting(<?php echo $userBooking['hosting_offer_id']; ?>)">Cancel Booking</button>
                </div>
            </div>
        <?php endif; ?>

        <h2 class="text-primary mt-4 mb-3">Available Hosting</h2>

        <?php if (empty($availableOffers)): ?>
            <div class="card">
                <div class="card-body text-center">
                    <p>No hosting offers available yet.</p>
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
                            <strong>Available spaces:</strong> 
                            <span class="<?php echo $offer['available_spaces'] > 0 ? 'text-success' : 'text-danger'; ?>">
                                <?php echo e($offer['available_spaces']); ?> / <?php echo e($offer['capacity']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <?php if ($offer['notes']): ?>
                        <div class="item-description">
                            <?php echo nl2br(e($offer['notes'])); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="item-footer">
                        <?php if ($offer['available_spaces'] > 0 && !$userBooking && $offer['user_id'] != getCurrentUserId()): ?>
                            <button class="btn btn-primary" onclick="bookHosting(<?php echo $offer['id']; ?>)">Book Accommodation</button>
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
