<?php
$currentPage = 'home';
ob_start();
?>

<!-- Hero Section -->
<div class="hero">
    <div class="container">
        <div class="hero-content">
            <h1><?php echo e($event['title']); ?></h1>
            <p><?php echo e($event['description']); ?></p>
            <p><strong><?php echo date('F j, Y', strtotime($event['start_date'])); ?> - <?php echo date('F j, Y', strtotime($event['end_date'])); ?></strong></p>
            
            <?php if (!$isAttending): ?>
            <button class="btn btn-accent btn-lg" onclick="modalManager.open('attendance-modal')">
                I am attending!
            </button>
            <?php else: ?>
            <div class="badge badge-success" style="font-size: 1.25rem; padding: 1rem 2rem;">
                ✓ You're registered!
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Event Information -->
<div class="section">
    <div class="container">
        <div class="card">
            <div class="card-body">
                <?php echo $event['content']; ?>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Modal -->
<div class="modal" id="attendance-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Register Your Attendance</h3>
            <button class="modal-close" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="attendance-form">
                <?php echo CSRF::field(); ?>
                
                <div class="form-group">
                    <label class="form-label" for="discord_name">Discord Name *</label>
                    <input type="text" class="form-control" id="discord_name" name="discord_name" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="name">Name *</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="pin">Create a PIN (for future logins) *</label>
                    <input type="password" class="form-control" id="pin" name="pin" minlength="4" required>
                    <small>Minimum 4 digits</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Days Attending *</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="day-friday" name="days_attending[]" value="Friday">
                        <label class="form-check-label" for="day-friday">Friday (Nov 20)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="day-saturday" name="days_attending[]" value="Saturday">
                        <label class="form-check-label" for="day-saturday">Saturday (Nov 21)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="day-sunday" name="days_attending[]" value="Sunday">
                        <label class="form-check-label" for="day-sunday">Sunday (Nov 22)</label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Travel Method *</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="travel-train" name="travel_method[]" value="Train">
                        <label class="form-check-label" for="travel-train">Train</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="travel-plane" name="travel_method[]" value="Plane">
                        <label class="form-check-label" for="travel-plane">Plane</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="travel-car" name="travel_method[]" value="Car">
                        <label class="form-check-label" for="travel-car">Car</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="travel-coach" name="travel_method[]" value="Coach">
                        <label class="form-check-label" for="travel-coach">Coach</label>
                    </div>
                </div>

                <!-- Carshare Section (Conditional) -->
                <div id="carshare-section" style="display: none;">
                    <div class="form-group">
                        <label class="form-label">Can you offer a carshare/lift?</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="can-carshare-yes" name="can_carshare" value="yes">
                            <label class="form-check-label" for="can-carshare-yes">Yes</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="can-carshare-no" name="can_carshare" value="no">
                            <label class="form-check-label" for="can-carshare-no">No</label>
                        </div>
                    </div>

                    <div id="carshare-details" style="display: none;">
                        <div class="form-group">
                            <label class="form-label" for="carshare_origin">Where are you travelling from?</label>
                            <input type="text" class="form-control" id="carshare_origin" name="carshare_origin">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="carshare_capacity">How many passengers can you take?</label>
                            <input type="number" class="form-control" id="carshare_capacity" name="carshare_capacity" min="1" max="8">
                        </div>
                    </div>
                </div>

                <!-- Hosting Section -->
                <div class="form-group">
                    <label class="form-label">Can you host people?</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" id="can-host-yes" name="can_host" value="yes">
                        <label class="form-check-label" for="can-host-yes">Yes</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" id="can-host-no" name="can_host" value="no">
                        <label class="form-check-label" for="can-host-no">No</label>
                    </div>
                </div>

                <div id="hosting-details" style="display: none;">
                    <div class="form-group">
                        <label class="form-label" for="hosting_capacity">How many people can you host?</label>
                        <input type="number" class="form-control" id="hosting_capacity" name="hosting_capacity" min="1" max="20">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="hosting_notes">Additional notes (optional)</label>
                        <textarea class="form-control" id="hosting_notes" name="hosting_notes" rows="3"></textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg">Register Attendance</button>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
