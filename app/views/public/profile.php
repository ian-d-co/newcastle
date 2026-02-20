<?php
$currentPage = 'profile';
ob_start();

$db = getDbConnection();

// Get user info
$stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch();

// Get event attendance
$stmt = $db->prepare("SELECT * FROM event_attendees WHERE user_id = :user_id AND event_id = :event_id");
$stmt->execute(['user_id' => $userId, 'event_id' => $event['id']]);
$attendance = $stmt->fetch();

// Get carshare and hosting offers
$stmt = $db->prepare("SELECT * FROM carshare_offers WHERE user_id = :user_id AND event_id = :event_id");
$stmt->execute(['user_id' => $userId, 'event_id' => $event['id']]);
$carshareOffer = $stmt->fetch();

$stmt = $db->prepare("SELECT * FROM hosting_offers WHERE user_id = :user_id AND event_id = :event_id");
$stmt->execute(['user_id' => $userId, 'event_id' => $event['id']]);
$hostingOffer = $stmt->fetch();

// Parse JSON fields
$daysAttending = [];
$travelMethod  = [];
if ($attendance) {
    $daysAttending = !empty($attendance['days_attending']) ? explode(',', $attendance['days_attending']) : [];
    $travelMethod  = !empty($attendance['travel_method'])  ? explode(',', $attendance['travel_method'])  : [];
}
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

        <!-- Attendance Details -->
        <div class="card">
            <div class="card-header">My Details • Travel • Hosting • Where I am staying</div>
            <div class="card-body">
                <?php if (!$attendance): ?>
                    <p>You haven't registered your attendance details yet.</p>
                    <button class="btn btn-primary" onclick="modalManager.open('attendance-modal')">Register Attendance</button>
                <?php else: ?>
                    <div style="margin-bottom: 1rem;">
                        <strong>Days Attending:</strong><br>
                        <?php foreach ($daysAttending as $day): ?>
                            <span class="badge" style="background: #6d4c1f; color: white; padding: 0.25rem 0.5rem; margin-right: 0.5rem; border-radius: 4px;">
                                <?php echo e(trim($day)); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <strong>Travel Method:</strong><br>
                        <?php foreach ($travelMethod as $method): ?>
                            <span class="badge" style="background: #6c757d; color: white; padding: 0.25rem 0.5rem; margin-right: 0.5rem; border-radius: 4px;">
                                <?php echo e(trim($method)); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($carshareOffer): ?>
                        <div style="margin-bottom: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 4px;">
                            <strong>🚗 Carshare Offer:</strong><br>
                            From: <?php echo e($carshareOffer['origin']); ?><br>
                            Capacity: <?php echo (int)$carshareOffer['passenger_capacity']; ?> passengers<br>
                            Available: <?php echo (int)$carshareOffer['available_spaces']; ?> spaces
                        </div>
                    <?php endif; ?>

                    <?php if ($hostingOffer): ?>
                        <div style="margin-bottom: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 4px;">
                            <strong>🏠 Hosting Offer:</strong><br>
                            Capacity: <?php echo (int)$hostingOffer['capacity']; ?> people<br>
                            Available: <?php echo (int)$hostingOffer['available_spaces']; ?> spaces
                            <?php if ($hostingOffer['notes']): ?>
                                <br>Notes: <?php echo e($hostingOffer['notes']); ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <button class="btn btn-secondary" onclick="modalManager.open('attendance-modal')">Update Attendance</button>
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

<!-- Attendance Modal -->
<div class="modal" id="attendance-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title"><?php echo $attendance ? 'Update Attendance' : 'Register Attendance'; ?></h3>
            <button class="modal-close" onclick="modalManager.close('attendance-modal')" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="attendance-form">
                <?php echo CSRF::field(); ?>

                <div class="form-group">
                    <label class="form-label">Days Attending *</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="day-friday" name="days_attending[]" value="Friday"
                               <?php echo in_array('Friday', $daysAttending) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="day-friday">Friday (Nov 20)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="day-saturday" name="days_attending[]" value="Saturday"
                               <?php echo in_array('Saturday', $daysAttending) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="day-saturday">Saturday (Nov 21)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="day-sunday" name="days_attending[]" value="Sunday"
                               <?php echo in_array('Sunday', $daysAttending) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="day-sunday">Sunday (Nov 22)</label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Travel Method *</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="travel-train" name="travel_method[]" value="Train"
                               <?php echo in_array('Train', $travelMethod) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="travel-train">Train</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="travel-plane" name="travel_method[]" value="Plane"
                               <?php echo in_array('Plane', $travelMethod) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="travel-plane">Plane</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="travel-car" name="travel_method[]" value="Car"
                               <?php echo in_array('Car', $travelMethod) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="travel-car">Car</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="travel-coach" name="travel_method[]" value="Coach"
                               <?php echo in_array('Coach', $travelMethod) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="travel-coach">Coach</label>
                    </div>
                </div>

                <div id="carshare-section" style="display: <?php echo in_array('Car', $travelMethod) ? 'block' : 'none'; ?>;">
                    <div class="form-group">
                        <label class="form-label">Can you offer a carshare/lift?</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="can-carshare-yes" name="can_carshare" value="yes"
                                   <?php echo $carshareOffer ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="can-carshare-yes">Yes</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="can-carshare-no" name="can_carshare" value="no"
                                   <?php echo (!$carshareOffer && in_array('Car', $travelMethod)) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="can-carshare-no">No</label>
                        </div>
                    </div>

                    <div id="carshare-details" style="display: <?php echo $carshareOffer ? 'block' : 'none'; ?>;">
                        <div class="form-group">
                            <label class="form-label" for="carshare_origin">Where are you travelling from?</label>
                            <input type="text" class="form-control" id="carshare_origin" name="carshare_origin"
                                   value="<?php echo e($carshareOffer['origin'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="carshare_capacity">How many passengers can you take?</label>
                            <input type="number" class="form-control" id="carshare_capacity" name="carshare_capacity"
                                   min="1" max="8" value="<?php echo (int)($carshareOffer['passenger_capacity'] ?? 0) ?: ''; ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Can you host people?</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" id="can-host-yes" name="can_host" value="yes"
                               <?php echo $hostingOffer ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="can-host-yes">Yes</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" id="can-host-no" name="can_host" value="no"
                               <?php echo !$hostingOffer ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="can-host-no">No</label>
                    </div>
                </div>

                <div id="hosting-details" style="display: <?php echo $hostingOffer ? 'block' : 'none'; ?>;">
                    <div class="form-group">
                        <label class="form-label" for="hosting_capacity">How many people can you host?</label>
                        <input type="number" class="form-control" id="hosting_capacity" name="hosting_capacity"
                               min="1" max="20" value="<?php echo (int)($hostingOffer['capacity'] ?? 0) ?: ''; ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="hosting_notes">Additional notes (optional)</label>
                        <textarea class="form-control" id="hosting_notes" name="hosting_notes" rows="3"><?php echo e($hostingOffer['notes'] ?? ''); ?></textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg">Save Attendance</button>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('travel-car').addEventListener('change', function() {
    document.getElementById('carshare-section').style.display = this.checked ? 'block' : 'none';
});

document.getElementById('can-carshare-yes').addEventListener('change', function() {
    document.getElementById('carshare-details').style.display = this.checked ? 'block' : 'none';
});

document.getElementById('can-carshare-no').addEventListener('change', function() {
    document.getElementById('carshare-details').style.display = 'none';
});

document.getElementById('can-host-yes').addEventListener('change', function() {
    document.getElementById('hosting-details').style.display = this.checked ? 'block' : 'none';
});

document.getElementById('can-host-no').addEventListener('change', function() {
    document.getElementById('hosting-details').style.display = 'none';
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


document.getElementById('attendance-form').addEventListener('submit', function(e) {
    e.preventDefault();

    var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    var form = this;
    var days = Array.from(form.querySelectorAll('input[name="days_attending[]"]:checked')).map(function(el) { return el.value; });
    var travel = Array.from(form.querySelectorAll('input[name="travel_method[]"]:checked')).map(function(el) { return el.value; });
    var canCarshare = form.querySelector('input[name="can_carshare"]:checked');
    var canHost = form.querySelector('input[name="can_host"]:checked');

    var data = {
        csrf_token: csrfToken,
        days_attending: days,
        travel_method: travel
    };

    if (canCarshare && canCarshare.value === 'yes') {
        data.carshare_origin = form.carshare_origin.value;
        data.carshare_capacity = form.carshare_capacity.value;
    }

    if (canHost && canHost.value === 'yes') {
        data.hosting_capacity = form.hosting_capacity.value;
        data.hosting_notes = form.hosting_notes.value;
    }

    apiCall('/api/attendance-update.php', 'POST', data, function(err, response) {
        if (err) {
            showAlert(err.message || 'Failed to update attendance', 'danger');
        } else {
            showAlert('Attendance updated successfully!', 'success');
            setTimeout(function() { location.reload(); }, 1000);
        }
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
