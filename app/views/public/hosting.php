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
                    <p><strong>Available spaces:</strong> <span style="color: #333; font-weight: 600;"><?php echo e($userOffer['available_spaces']); ?></span></p>
                    <?php if ($userOffer['notes']): ?>
                        <p><strong>Notes:</strong> <?php echo nl2br(e($userOffer['notes'])); ?></p>
                    <?php endif; ?>
                    <?php if ($offerBookings): ?>
                        <p><strong>Guests:</strong></p>
                        <ul>
                            <?php foreach ($offerBookings as $booking): ?>
                                <li style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                                    <?php echo displayName($booking['discord_name']); ?> (<?php echo displayName($booking['name']); ?>)
                                    <button class="btn btn-danger btn-sm" onclick="cancelGuestBooking(<?php echo (int)$userOffer['id']; ?>, <?php echo (int)$booking['user_id']; ?>)">Cancel</button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <?php if (!empty($pendingRequests)): ?>
                        <hr>
                        <h4 style="margin: 0.5rem 0;">Pending Requests (<?php echo count($pendingRequests); ?>)</h4>
                        <?php foreach ($pendingRequests as $req): ?>
                            <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; padding: 0.75rem; margin: 0.5rem 0;">
                                <p style="margin: 0 0 0.5rem;"><strong><?php echo displayName($req['discord_name']); ?></strong> wants to stay</p>
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
                    Your Hosting Request
                    <?php if ($userRequest['status'] === 'accepted'): ?>
                        <span class="badge badge-success" style="margin-left: 0.5rem;">✓ Accepted</span>
                    <?php elseif ($userRequest['status'] === 'pending'): ?>
                        <span class="badge badge-warning" style="margin-left: 0.5rem;">Pending</span>
                    <?php elseif ($userRequest['status'] === 'declined'): ?>
                        <span class="badge badge-danger" style="margin-left: 0.5rem;">Declined</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <p><strong>Host:</strong> <?php echo displayName($userRequest['host_discord_name']); ?></p>
                    <?php if (!empty($userRequest['offer_notes'])): ?>
                        <p><strong>Host notes:</strong> <?php echo nl2br(e($userRequest['offer_notes'])); ?></p>
                    <?php endif; ?>
                    <?php if ($userRequest['status'] !== 'declined'): ?>
                        <button class="btn btn-danger btn-sm" onclick="cancelRequest(<?php echo (int)$userRequest['id']; ?>)">Cancel Request</button>
                    <?php endif; ?>
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
                            <span style="color: #333; font-weight: 600;">
                                <?php echo e($offer['available_spaces']); ?> / <?php echo e($offer['capacity']); ?>
                            </span>
                        </div>
                    </div>

                    <?php if ($offer['notes']): ?>
                        <div class="item-description" style="font-size: 1rem; color: #222; margin: 0.5rem 0;">
                            <strong>Notes:</strong> <?php echo nl2br(e($offer['notes'])); ?>
                        </div>
                    <?php endif; ?>

                    <div class="item-footer">
                        <?php if (isGuestMode()): ?>
                            <button class="btn btn-primary" disabled>Request to Stay (Login Required)</button>
                        <?php elseif ($offer['user_id'] == getCurrentUserId()): ?>
                            <span class="badge badge-secondary">Your offer</span>
                        <?php elseif ($userRequest && $userRequest['status'] !== 'declined'): ?>
                            <span class="badge badge-info">Request sent</span>
                        <?php elseif ($offer['available_spaces'] > 0): ?>
                            <button class="btn btn-primary" onclick="openRequestModal(<?php echo (int)$offer['id']; ?>, '<?php echo e(addslashes(displayName($offer['discord_name']))); ?>')">Request to Stay</button>
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
        <span class="modal-close">&times;</span>
        <h2>Request to Stay</h2>
        <p id="requestHostName" style="color: #555;"></p>
        <div class="form-group">
            <label for="requestMessage">Message (optional)</label>
            <textarea id="requestMessage" class="form-control" rows="3" placeholder="Any message for the host..."></textarea>
        </div>
        <div style="display: flex; gap: 1rem; margin-top: 1rem;">
            <button class="btn btn-primary" onclick="submitRequest()">Send Request</button>
            <button class="btn btn-secondary" onclick="modalManager.close('requestModal')">Cancel</button>
        </div>
    </div>
</div>

<script>
var _currentOfferId = null;

function openRequestModal(offerId, hostName) {
    _currentOfferId = offerId;
    document.getElementById('requestHostName').textContent = 'Requesting to stay with: ' + hostName;
    document.getElementById('requestMessage').value = '';
    modalManager.open('requestModal');
}

function submitRequest() {
    if (!_currentOfferId) return;
    var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    var message = document.getElementById('requestMessage').value;

    apiCall('/api/hosting-request.php', 'POST', {
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
    apiCall('/api/hosting-respond.php', 'POST', {
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
    apiCall('/api/hosting-respond.php', 'POST', {
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

function cancelGuestBooking(offerId, guestId) {
    if (!confirm('Are you sure you want to cancel this booking?')) return;

    var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    apiCall('/api/hosting-cancel.php', 'POST', {
        offer_id: offerId,
        guest_id: guestId,
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
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
