<?php
$currentPage = 'profile';
ob_start();

$db = getDbConnection();

// Get user info
$stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch();

// Load car share and hosting offers for the current user/event
require_once BASE_PATH . '/app/models/CarShare.php';
require_once BASE_PATH . '/app/models/Hosting.php';
$carshareModel = new CarShare();
$hostingModel  = new Hosting();
$carshareOffer = $carshareModel->getUserOffer($userId, $event['id']);
$hostingOffer  = $hostingModel->getUserOffer($userId, $event['id']);

?>

<div class="section">
    <div class="container">
        <h1 class="section-title">My Profile</h1>

        <!-- Account Info -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">Account Information</div>
            <div class="card-body">
                <p>
                    <strong>Discord Name:</strong> <?php echo e($user['discord_name']); ?>
                    <button class="btn btn-sm btn-secondary" style="margin-left: 0.5rem;" onclick="modalManager.open('edit-profile-modal')">Edit</button>
                </p>
                <p>
                    <strong>Name:</strong> <?php echo e($user['name']); ?>
                </p>
                <p><strong>Account Status:</strong>
                    <span class="badge badge-<?php echo $user['approved'] ? 'success' : 'warning'; ?>">
                        <?php echo $user['approved'] ? 'Approved' : 'Pending Approval'; ?>
                    </span>
                </p>
                <button class="btn btn-sm btn-secondary" onclick="modalManager.open('change-pin-modal')">Change PIN</button>
            </div>
        </div>

        <!-- Hotel Sharing Preference -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">Preferences</div>
            <div class="card-body">
                <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                    <input type="checkbox" id="open-to-hotel-sharing"
                           <?php echo !empty($user['open_to_hotel_sharing']) ? 'checked' : ''; ?>
                           style="width: 20px; height: 20px; cursor: pointer;">
                    <span>Open to sharing a hotel room</span>
                </label>
                <p style="margin: 0.5rem 0 0; font-size: 0.875rem; color: #666;">
                    When enabled, your name will appear on the <a href="/index.php?page=hotel_sharing">Hotel Sharing</a> page so others can request to share a room with you.
                </p>
            </div>
        </div>

        <!-- Car Sharing -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">Car Sharing</div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Can you offer a car share/lift?</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" id="prof-can-carshare-yes" name="prof_can_carshare" value="yes"
                               <?php echo $carshareOffer ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="prof-can-carshare-yes">Yes</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" id="prof-can-carshare-no" name="prof_can_carshare" value="no"
                               <?php echo !$carshareOffer ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="prof-can-carshare-no">No</label>
                    </div>
                </div>
                <div id="prof-carshare-details" style="display: <?php echo $carshareOffer ? 'block' : 'none'; ?>;">
                    <div class="form-group">
                        <label class="form-label" for="prof-carshare-origin">Where are you travelling from?</label>
                        <input type="text" class="form-control" id="prof-carshare-origin"
                               value="<?php echo e($carshareOffer['origin'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="prof-carshare-capacity">How many passengers can you take?</label>
                        <input type="number" class="form-control" id="prof-carshare-capacity"
                               min="1" max="8" value="<?php echo $carshareOffer ? (int)$carshareOffer['passenger_capacity'] : ''; ?>">
                    </div>
                    <button type="button" class="btn btn-primary" onclick="saveCarshare()">Save Car Share</button>
                </div>
                <?php if ($carshareOffer): ?>
                <div style="margin-top: 0.75rem;">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeCarshare()">Remove Car Share Offer</button>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Hosting -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">Hosting</div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Can you host people?</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" id="prof-can-host-yes" name="prof_can_host" value="yes"
                               <?php echo $hostingOffer ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="prof-can-host-yes">Yes</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" id="prof-can-host-no" name="prof_can_host" value="no"
                               <?php echo !$hostingOffer ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="prof-can-host-no">No</label>
                    </div>
                </div>
                <div id="prof-hosting-details" style="display: <?php echo $hostingOffer ? 'block' : 'none'; ?>;">
                    <div class="form-group">
                        <label class="form-label" for="prof-hosting-capacity">How many people can you host?</label>
                        <input type="number" class="form-control" id="prof-hosting-capacity"
                               min="1" value="<?php echo $hostingOffer ? (int)$hostingOffer['capacity'] : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="prof-hosting-notes">Notes (optional)</label>
                        <textarea class="form-control" id="prof-hosting-notes" rows="3"><?php echo e($hostingOffer['notes'] ?? ''); ?></textarea>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="saveHosting()">Save Hosting</button>
                </div>
                <?php if ($hostingOffer): ?>
                <div style="margin-top: 0.75rem;">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeHosting()">Remove Hosting Offer</button>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal" id="edit-profile-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Edit Profile</h3>
            <button class="modal-close" onclick="modalManager.close('edit-profile-modal')" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="edit-profile-form">
                <?php echo CSRF::field(); ?>
                <div class="form-group">
                    <label class="form-label" for="edit-discord-name">Discord Name</label>
                    <input type="text" class="form-control" id="edit-discord-name" name="discord_name" value="<?php echo e($user['discord_name']); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit-name">Name</label>
                    <input type="text" class="form-control" id="edit-name" name="name" value="<?php echo e($user['name']); ?>">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Save Changes</button>
            </form>
        </div>
    </div>
</div>

<!-- Change PIN Modal -->
<div class="modal" id="change-pin-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Change PIN</h3>
            <button class="modal-close" onclick="modalManager.close('change-pin-modal')" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="change-pin-form">
                <?php echo CSRF::field(); ?>
                <div class="form-group">
                    <label class="form-label" for="current-pin">Current PIN</label>
                    <input type="password" class="form-control" id="current-pin" name="current_pin" autocomplete="current-password">
                </div>
                <div class="form-group">
                    <label class="form-label" for="new-pin">New PIN (4-8 digits)</label>
                    <input type="password" class="form-control" id="new-pin" name="new_pin" pattern="[0-9]{4,8}" autocomplete="new-password">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Change PIN</button>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('open-to-hotel-sharing').addEventListener('change', function() {
    var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    apiCall('/api/profile-update.php', 'POST', {
        csrf_token: csrfToken,
        action: 'update_hotel_sharing',
        open_to_hotel_sharing: this.checked ? 1 : 0
    }, function(err, res) {
        if (err) {
            showAlert(err.message || 'Failed to update preference', 'danger');
        } else {
            showAlert(res.message || 'Preference updated!', 'success');
        }
    });
});

// Car share toggle
document.getElementById('prof-can-carshare-yes').addEventListener('change', function() {
    document.getElementById('prof-carshare-details').style.display = 'block';
});
document.getElementById('prof-can-carshare-no').addEventListener('change', function() {
    document.getElementById('prof-carshare-details').style.display = 'none';
});

// Hosting toggle
document.getElementById('prof-can-host-yes').addEventListener('change', function() {
    document.getElementById('prof-hosting-details').style.display = 'block';
});
document.getElementById('prof-can-host-no').addEventListener('change', function() {
    document.getElementById('prof-hosting-details').style.display = 'none';
});

function saveCarshare() {
    var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    var origin = document.getElementById('prof-carshare-origin').value.trim();
    var capacity = document.getElementById('prof-carshare-capacity').value;
    if (!origin) { showAlert('Please enter your travel origin', 'danger'); return; }
    if (!capacity || capacity < 1) { showAlert('Please enter a valid passenger capacity', 'danger'); return; }
    apiCall('/api/profile-update.php', 'POST', {
        csrf_token: csrfToken,
        action: 'update_carshare',
        carshare_origin: origin,
        carshare_capacity: parseInt(capacity)
    }, function(err, res) {
        if (err) { showAlert(err.message || 'Failed to save', 'danger'); return; }
        showAlert(res.message || 'Car share saved!', 'success');
        setTimeout(function() { location.reload(); }, 800);
    });
}

function removeCarshare() {
    if (!confirm('Remove your car share offer?')) return;
    var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    apiCall('/api/profile-update.php', 'POST', {
        csrf_token: csrfToken,
        action: 'remove_carshare'
    }, function(err, res) {
        if (err) { showAlert(err.message || 'Failed to remove', 'danger'); return; }
        showAlert(res.message || 'Car share offer removed', 'success');
        setTimeout(function() { location.reload(); }, 800);
    });
}

function saveHosting() {
    var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    var capacity = document.getElementById('prof-hosting-capacity').value;
    var notes = document.getElementById('prof-hosting-notes').value.trim();
    if (!capacity || capacity < 1) { showAlert('Please enter a valid hosting capacity', 'danger'); return; }
    apiCall('/api/profile-update.php', 'POST', {
        csrf_token: csrfToken,
        action: 'update_hosting',
        hosting_capacity: parseInt(capacity),
        hosting_notes: notes
    }, function(err, res) {
        if (err) { showAlert(err.message || 'Failed to save', 'danger'); return; }
        showAlert(res.message || 'Hosting saved!', 'success');
        setTimeout(function() { location.reload(); }, 800);
    });
}

function removeHosting() {
    if (!confirm('Remove your hosting offer?')) return;
    var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    apiCall('/api/profile-update.php', 'POST', {
        csrf_token: csrfToken,
        action: 'remove_hosting'
    }, function(err, res) {
        if (err) { showAlert(err.message || 'Failed to remove', 'danger'); return; }
        showAlert(res.message || 'Hosting offer removed', 'success');
        setTimeout(function() { location.reload(); }, 800);
    });
}

document.getElementById('edit-profile-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    var form = this;
    apiCall('/api/profile-update.php', 'POST', {
        csrf_token: csrfToken,
        action: 'update_profile',
        discord_name: form.discord_name.value,
        name: form.name.value
    }, function(err, response) {
        if (err) {
            showAlert(err.message || 'Failed to update profile', 'danger');
        } else {
            showAlert('Profile updated successfully!', 'success');
            modalManager.close('edit-profile-modal');
            setTimeout(function() { location.reload(); }, 1000);
        }
    });
});

document.getElementById('change-pin-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    var form = this;
    apiCall('/api/profile-update.php', 'POST', {
        csrf_token: csrfToken,
        action: 'change_pin',
        current_pin: form.current_pin.value,
        new_pin: form.new_pin.value
    }, function(err, response) {
        if (err) {
            showAlert(err.message || 'Failed to change PIN', 'danger');
        } else {
            showAlert('PIN changed successfully!', 'success');
            modalManager.close('change-pin-modal');
            form.reset();
        }
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
