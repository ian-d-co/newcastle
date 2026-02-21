<?php
$currentPage = 'carshare';
ob_start();
?>

<div class="section">
    <div class="container">
        <h1 class="section-title">Car Share</h1>

        <?php if ($userOffer): ?>
            <div class="card mb-3">
                <div class="card-header">Your Car Share Offer</div>
                <div class="card-body">
                    <p><strong>Travelling from:</strong> <?php echo e($userOffer['origin']); ?></p>
                    <p><strong>Passenger capacity:</strong> <?php echo e($userOffer['passenger_capacity']); ?></p>
                    <p><strong>Available spaces:</strong> <span style="color: #333; font-weight: 600;"><?php echo e($userOffer['available_spaces']); ?></span></p>
                    <?php if ($offerBookings): ?>
                        <p><strong>Passengers:</strong></p>
                        <ul>
                            <?php foreach ($offerBookings as $booking): ?>
                                <li style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                                    <?php echo displayName($booking['discord_name']); ?> (<?php echo displayName($booking['name']); ?>)
                                    <button class="btn btn-danger btn-sm" onclick="cancelPassengerBooking(<?php echo (int)$userOffer['id']; ?>, <?php echo (int)$booking['user_id']; ?>)">Cancel</button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <?php if (!empty($pendingRequests)): ?>
                        <hr>
                        <h4 style="margin: 0.5rem 0;">Pending Requests (<?php echo count($pendingRequests); ?>)</h4>
                        <?php foreach ($pendingRequests as $req): ?>
                            <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; padding: 0.75rem; margin: 0.5rem 0;">
                                <p style="margin: 0 0 0.5rem;"><strong><?php echo displayName($req['discord_name']); ?></strong> wants to join</p>
                                <?php if (!empty($req['message'])): ?>
                                    <p style="margin: 0 0 0.5rem; font-style: italic;">"<?php echo nl2br(e($req['message'])); ?>"</p>
                                <?php endif; ?>
                                <button class="btn btn-success btn-sm" onclick="respondRequest(<?php echo (int)$req['id']; ?>, 'accept')" style="margin-right: 0.5rem;">Accept</button>
                                <button class="btn btn-danger btn-sm" onclick="respondRequest(<?php echo (int)$req['id']; ?>, 'decline')">Decline</button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($userRequest): ?>
            <div class="card mb-3">
                <div class="card-header <?php echo $userRequest['status'] === 'accepted' ? 'bg-success text-white' : 'bg-warning'; ?>">
                    Your Car Share Request
                    <?php if ($userRequest['status'] === 'accepted'): ?>
                        <span class="badge badge-success" style="margin-left: 0.5rem;">✓ Accepted</span>
                    <?php elseif ($userRequest['status'] === 'pending'): ?>
                        <span class="badge badge-warning" style="margin-left: 0.5rem;">Pending</span>
                    <?php elseif ($userRequest['status'] === 'declined'): ?>
                        <span class="badge badge-danger" style="margin-left: 0.5rem;">Declined</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <p><strong>Driver:</strong> <?php echo displayName($userRequest['driver_discord_name']); ?></p>
                    <p><strong>Travelling from:</strong> <?php echo e($userRequest['origin']); ?></p>
                    <?php if ($userRequest['status'] !== 'declined' && $userRequest['status'] !== 'cancelled'): ?>
                        <button class="btn btn-danger btn-sm" onclick="cancelRequest(<?php echo (int)$userRequest['id']; ?>)">Cancel Request</button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($userBooking && !$userRequest): ?>
            <div class="card mb-3">
                <div class="card-header bg-success text-white">Your Car Share Booking</div>
                <div class="card-body">
                    <p><strong>Driver:</strong> <?php echo displayName($userBooking['driver_name']); ?></p>
                    <p><strong>Travelling from:</strong> <?php echo e($userBooking['origin']); ?></p>
                    <button class="btn btn-danger btn-sm" onclick="cancelCarshare(<?php echo $userBooking['carshare_offer_id']; ?>)">Cancel Booking</button>
                </div>
            </div>
        <?php endif; ?>

        <h2 class="text-primary mt-4 mb-3">Available Car Shares</h2>

        <?php if (empty($availableOffers)): ?>
            <div class="card">
                <div class="card-body text-center">
                    <p>No car share offers available yet. Be the first to offer!</p>
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
                            <span style="color: #333; font-weight: 600;">
                                <?php echo e($offer['available_spaces']); ?> / <?php echo e($offer['passenger_capacity']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="item-footer">
                        <?php if (isGuestMode()): ?>
                            <button class="btn btn-primary" disabled>Request to Join (Login Required)</button>
                        <?php elseif ($offer['user_id'] == getCurrentUserId()): ?>
                            <span class="badge badge-secondary">Your offer</span>
                        <?php elseif ($userRequest && $userRequest['status'] !== 'declined' && $userRequest['status'] !== 'cancelled'): ?>
                            <span class="badge badge-info">Request sent</span>
                        <?php elseif ($userBooking): ?>
                            <span class="badge badge-success">Already booked</span>
                        <?php elseif ($offer['available_spaces'] > 0): ?>
                            <button class="btn btn-primary btn-request-join" data-offer-id="<?php echo (int)$offer['id']; ?>" data-driver-name="<?php echo e($offer['discord_name'] ?? 'Unknown'); ?>">Request to Join</button>
                        <?php else: ?>
                            <span class="badge badge-danger">Full</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Request Modal -->
<div id="requestModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <span class="modal-close" onclick="modalManager.close('requestModal')">&times;</span>
        <h2>Request to Join</h2>
        <p id="requestDriverName" style="color: #555;"></p>
        <div class="form-group">
            <label for="requestMessage">Message (optional)</label>
            <textarea id="requestMessage" class="form-control" rows="3" placeholder="Any message for the driver..."></textarea>
        </div>
        <div style="display: flex; gap: 1rem; margin-top: 1rem;">
            <button class="btn btn-primary" onclick="submitRequest()">Send Request</button>
            <button class="btn btn-secondary" onclick="modalManager.close('requestModal')">Cancel</button>
        </div>
    </div>
</div>

<script>
var _currentOfferId = null;

function openRequestModal(offerId, driverName) {
    _currentOfferId = offerId;
    document.getElementById('requestDriverName').textContent = 'Requesting to join: ' + driverName;
    document.getElementById('requestMessage').value = '';
    modalManager.open('requestModal');
}

function submitRequest() {
    if (!_currentOfferId) return;
    var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    var message = document.getElementById('requestMessage').value;

    apiCall('/api/carshare-request.php', 'POST', {
        offer_id: _currentOfferId,
        message: message,
        csrf_token: csrfToken
    }, function(err, response) {
        if (err) {
            showAlert(err.message || 'Failed to send request', 'danger');
        } else {
            showAlert('Request sent successfully!', 'success');
            modalManager.close('requestModal');
            setTimeout(function() { location.reload(); }, 1000);
        }
    });
}

function respondRequest(requestId, action) {
    var label = action === 'accept' ? 'accept' : 'decline';
    if (!confirm('Are you sure you want to ' + label + ' this request?')) return;

    var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    apiCall('/api/carshare-respond.php', 'POST', {
        request_id: requestId,
        action: action,
        csrf_token: csrfToken
    }, function(err, response) {
        if (err) {
            showAlert(err.message || 'Failed to respond', 'danger');
        } else {
            showAlert(response.message || 'Done!', 'success');
            setTimeout(function() { location.reload(); }, 1000);
        }
    });
}

function cancelRequest(requestId) {
    if (!confirm('Are you sure you want to cancel your request?')) return;

    var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    apiCall('/api/carshare-respond.php', 'POST', {
        request_id: requestId,
        action: 'cancel',
        csrf_token: csrfToken
    }, function(err, response) {
        if (err) {
            showAlert(err.message || 'Failed to cancel request', 'danger');
        } else {
            showAlert('Request cancelled', 'success');
            setTimeout(function() { location.reload(); }, 1000);
        }
    });
}

function cancelCarshare(offerId) {
    if (!confirm('Are you sure you want to cancel your car share booking?')) return;

    var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    apiCall('/api/carshare-cancel.php', 'POST', {
        offer_id: offerId,
        csrf_token: csrfToken
    }, function(err, response) {
        if (err) {
            showAlert(err.message || 'Failed to cancel booking', 'danger');
        } else {
            showAlert('Booking cancelled successfully!', 'success');
            setTimeout(function() { location.reload(); }, 1500);
        }
    });
}

function cancelPassengerBooking(offerId, passengerId) {
    if (!confirm('Are you sure you want to cancel this passenger\'s booking?')) return;

    var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    apiCall('/api/carshare-cancel.php', 'POST', {
        offer_id: offerId,
        passenger_id: passengerId,
        csrf_token: csrfToken
    }, function(err, response) {
        if (err) {
            showAlert(err.message || 'Failed to cancel booking', 'danger');
        } else {
            showAlert('Booking cancelled successfully!', 'success');
            setTimeout(function() { location.reload(); }, 1500);
        }
    });
}

// Bind request-to-join buttons using data attributes
document.addEventListener('DOMContentLoaded', function() {
    var buttons = document.querySelectorAll('.btn-request-join');
    buttons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var offerId = parseInt(this.getAttribute('data-offer-id'), 10);
            var driverName = this.getAttribute('data-driver-name') || 'Unknown';
            openRequestModal(offerId, driverName);
        });
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
