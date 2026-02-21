<?php
$currentPage = 'hotel_sharing';
ob_start();

require_once BASE_PATH . '/app/models/HotelSharing.php';
$sharingModel = new HotelSharing();

$openUsers       = $sharingModel->getOpenUsers();
$pendingRequests = $sharingModel->getPendingRequestsForUser($userId);
$sentRequests    = $sharingModel->getSentRequests($userId);
$matches         = $sharingModel->getAcceptedMatches($userId);

// Current user's open_to_hotel_sharing status
$db   = getDbConnection();
$stmt = $db->prepare("SELECT open_to_hotel_sharing FROM users WHERE id = :id");
$stmt->execute(['id' => $userId]);
$me   = $stmt->fetch();
$isOpenToSharing = !empty($me['open_to_hotel_sharing']);

// Index sent requests by target for easy lookup
$sentByTarget = [];
foreach ($sentRequests as $req) {
    $sentByTarget[$req['target_user_id']] = $req;
}
?>

<div class="section">
    <div class="container">
        <h1 class="section-title">Hotel Sharing</h1>

        <!-- Your sharing status -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">Your Sharing Status</div>
            <div class="card-body">
                <p>Indicate whether you are open to sharing a hotel room with someone else.</p>
                <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; font-size: 1rem;">
                    <input type="checkbox" id="open-to-sharing-toggle"
                           <?php echo $isOpenToSharing ? 'checked' : ''; ?>
                           style="width: 20px; height: 20px; cursor: pointer;">
                    <span>I am open to sharing a hotel room</span>
                </label>
            </div>
        </div>

        <?php if (!empty($matches)): ?>
        <!-- Accepted matches -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header" style="background: linear-gradient(135deg, #28a745, #20c997);">Confirmed Sharing Partners</div>
            <div class="card-body">
                <?php foreach ($matches as $match): ?>
                <div style="padding: 0.5rem 0; border-bottom: 1px solid #f0f0f0;">
                    <?php
                    $partnerName = $match['requester_id'] == $userId
                        ? $match['target_discord_name']
                        : $match['requester_discord_name'];
                    ?>
                    <span class="badge badge-success">✓ Matched</span>
                    <strong style="margin-left: 0.5rem;"><?php echo displayName($partnerName); ?></strong>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($pendingRequests)): ?>
        <!-- Incoming requests -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">Sharing Requests Received</div>
            <div class="card-body">
                <?php foreach ($pendingRequests as $req): ?>
                <div style="padding: 0.75rem 0; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.5rem;">
                    <div>
                        <strong><?php echo displayName($req['requester_discord_name']); ?></strong>
                        <?php if (!empty($req['message'])): ?>
                        <p style="margin: 0.25rem 0 0; font-size: 0.875rem; color: #666;">"<?php echo e($req['message']); ?>"</p>
                        <?php endif; ?>
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <button class="btn btn-sm btn-success"
                                data-id="<?php echo $req['id']; ?>"
                                onclick="respondSharing(this.dataset.id, 'accept')">Accept</button>
                        <button class="btn btn-sm btn-danger"
                                data-id="<?php echo $req['id']; ?>"
                                onclick="respondSharing(this.dataset.id, 'decline')">Decline</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- People open to sharing -->
        <div class="card">
            <div class="card-header">People Open to Sharing</div>
            <div class="card-body">
                <?php
                $others = array_filter($openUsers, function($u) use ($userId) {
                    return $u['id'] != $userId;
                });
                ?>
                <?php if (empty($others)): ?>
                    <p style="color: #999;">Nobody else is currently open to hotel sharing. Check back later!</p>
                <?php else: ?>
                    <?php foreach ($others as $u): ?>
                    <?php
                    $sent = $sentByTarget[$u['id']] ?? null;
                    $sentStatus = $sent ? $sent['status'] : null;
                    ?>
                    <div style="padding: 0.75rem 0; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                        <span><strong><?php echo displayName($u['discord_name']); ?></strong></span>
                        <div>
                            <?php if ($sentStatus === 'pending'): ?>
                                <span class="badge badge-warning">Request Pending</span>
                                <button class="btn btn-sm btn-danger" style="margin-left: 0.5rem;"
                                        data-id="<?php echo $sent['id']; ?>"
                                        onclick="cancelSharingRequest(this.dataset.id)">Cancel</button>
                            <?php elseif ($sentStatus === 'accepted'): ?>
                                <span class="badge badge-success">Matched!</span>
                            <?php elseif ($sentStatus === 'declined'): ?>
                                <span class="badge badge-danger">Declined</span>
                                <button class="btn btn-sm btn-primary" style="margin-left: 0.5rem;"
                                        data-uid="<?php echo $u['id']; ?>"
                                        data-name="<?php echo e($u['discord_name']); ?>"
                                        onclick="openSharingRequest(this.dataset.uid, this.dataset.name)">Request Again</button>
                            <?php else: ?>
                                <button class="btn btn-sm btn-primary"
                                        data-uid="<?php echo $u['id']; ?>"
                                        data-name="<?php echo e($u['discord_name']); ?>"
                                        onclick="openSharingRequest(this.dataset.uid, this.dataset.name)">Request to Share</button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Request to Share Modal -->
<div class="modal" id="sharing-request-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Request to Share</h3>
            <button class="modal-close" onclick="modalManager.close('sharing-request-modal')">&times;</button>
        </div>
        <div class="modal-body">
            <p>Send a hotel sharing request to <strong id="sharing-target-name"></strong>.</p>
            <form id="sharing-request-form">
                <input type="hidden" id="sharing-target-id">
                <div class="form-group">
                    <label class="form-label">Message (optional)</label>
                    <textarea class="form-control" id="sharing-message" rows="3" placeholder="Add a message..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Send Request</button>
            </form>
        </div>
    </div>
</div>

<script>
var hsCsrf = document.querySelector('meta[name="csrf-token"]').content;

// Toggle open to sharing
document.getElementById('open-to-sharing-toggle').addEventListener('change', function() {
    var val = this.checked ? 1 : 0;
    apiCall('/api/profile-update.php', 'POST', {
        csrf_token: hsCsrf,
        action: 'update_hotel_sharing',
        open_to_hotel_sharing: val
    }, function(err, res) {
        if (err) {
            showAlert(err.message || 'Failed to update', 'danger');
        } else {
            showAlert(res.message || 'Updated!', 'success');
        }
    });
});

function openSharingRequest(uid, name) {
    document.getElementById('sharing-target-id').value = uid;
    document.getElementById('sharing-target-name').textContent = name;
    document.getElementById('sharing-message').value = '';
    modalManager.open('sharing-request-modal');
}

document.getElementById('sharing-request-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = this.querySelector('[type="submit"]');
    btn.disabled = true;
    apiCall('/api/hotel-sharing.php', 'POST', {
        csrf_token: hsCsrf,
        action: 'request',
        target_user_id: document.getElementById('sharing-target-id').value,
        message: document.getElementById('sharing-message').value
    }, function(err, res) {
        btn.disabled = false;
        if (err) { showAlert(err.message || 'Failed to send request', 'danger'); return; }
        showAlert('Request sent!', 'success');
        modalManager.close('sharing-request-modal');
        setTimeout(function() { location.reload(); }, 800);
    });
});

function respondSharing(requestId, action) {
    apiCall('/api/hotel-sharing.php', 'POST', {
        csrf_token: hsCsrf,
        action: action,
        request_id: requestId
    }, function(err, res) {
        if (err) { showAlert(err.message || 'Failed', 'danger'); return; }
        showAlert(res.message || 'Done!', 'success');
        setTimeout(function() { location.reload(); }, 600);
    });
}

function cancelSharingRequest(requestId) {
    if (!confirm('Cancel this request?')) return;
    apiCall('/api/hotel-sharing.php', 'POST', {
        csrf_token: hsCsrf,
        action: 'cancel',
        request_id: requestId
    }, function(err, res) {
        if (err) { showAlert(err.message || 'Failed', 'danger'); return; }
        showAlert('Request cancelled', 'success');
        setTimeout(function() { location.reload(); }, 600);
    });
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
