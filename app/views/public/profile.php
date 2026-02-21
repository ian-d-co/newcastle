<?php
$currentPage = 'profile';
ob_start();

$db = getDbConnection();

// Get user info
$stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch();

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
