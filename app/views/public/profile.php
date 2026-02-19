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
    $daysAttending = json_decode($attendance['days_attending'], true) ?: [];
    $travelMethod  = json_decode($attendance['travel_method'], true)  ?: [];
}
?>

<div class="section">
    <div class="container">
        <h1 class="section-title">My Profile</h1>

        <!-- Account Info -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">Account Information</div>
            <div class="card-body">
                <p><strong>Discord Name:</strong> <?php echo e($user['discord_name']); ?></p>
                <p><strong>Name:</strong> <?php echo e($user['name']); ?></p>
                <p><strong>Account Status:</strong>
                    <span class="badge badge-<?php echo $user['approved'] ? 'success' : 'warning'; ?>">
                        <?php echo $user['approved'] ? 'Approved' : 'Pending Approval'; ?>
                    </span>
                </p>
            </div>
        </div>

        <!-- Attendance Details -->
        <div class="card">
            <div class="card-header">Event Attendance</div>
            <div class="card-body">
                <?php if (!$attendance): ?>
                    <p>You haven't registered your attendance details yet.</p>
                    <button class="btn btn-primary" onclick="modalManager.open('attendance-modal')">Register Attendance</button>
                <?php else: ?>
                    <div style="margin-bottom: 1rem;">
                        <strong>Days Attending:</strong> <?php echo e(implode(', ', $daysAttending)); ?>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <strong>Travel Method:</strong> <?php echo e(implode(', ', $travelMethod)); ?>
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
