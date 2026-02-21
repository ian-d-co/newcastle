<?php
$currentPage = 'whos_going';
ob_start();

$db = getDbConnection();

// Get all attendees with their attendance details
$stmt = $db->prepare("
    SELECT ea.id as attendance_id, ea.user_id, ea.days_attending, ea.travel_method,
           u.discord_name, u.name
    FROM event_attendees ea
    JOIN users u ON ea.user_id = u.id
    WHERE ea.event_id = :event_id
    ORDER BY u.discord_name
");
$stmt->execute(['event_id' => $event['id']]);
$attendees = $stmt->fetchAll();

// Get activity bookings for all users
$activityBookingsByUser = [];
if (!empty($attendees)) {
    $userIds = array_column($attendees, 'user_id');
    $placeholders = implode(',', array_fill(0, count($userIds), '?'));
    $stmt = $db->prepare("
        SELECT ab.user_id, a.title, a.day, a.start_time
        FROM activity_bookings ab
        JOIN activities a ON ab.activity_id = a.id
        WHERE ab.user_id IN ($placeholders) AND a.event_id = ?
        ORDER BY a.day, a.start_time
    ");
    $stmt->execute(array_merge($userIds, [$event['id']]));
    foreach ($stmt->fetchAll() as $row) {
        $activityBookingsByUser[$row['user_id']][] = $row;
    }

    // Get meal bookings for all users
    $stmt = $db->prepare("
        SELECT mb.user_id, m.title, m.day, m.start_time
        FROM meal_bookings mb
        JOIN meals m ON mb.meal_id = m.id
        WHERE mb.user_id IN ($placeholders) AND m.event_id = ?
        ORDER BY m.day, m.start_time
    ");
    $stmt->execute(array_merge($userIds, [$event['id']]));
    foreach ($stmt->fetchAll() as $row) {
        $mealBookingsByUser[$row['user_id']][] = $row;
    }
}
$mealBookingsByUser = $mealBookingsByUser ?? [];
?>

<div class="section">
    <div class="container">
        <h1 class="section-title">Who's Going</h1>

        <p style="color: #666; margin-bottom: 1.5rem;">
            <?php echo count($attendees); ?> registered attendee<?php echo count($attendees) !== 1 ? 's' : ''; ?> so far.
        </p>

        <?php if (empty($attendees)): ?>
            <div class="card">
                <div class="card-body text-center">
                    <p>No registered attendees yet. Be the first!</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($attendees as $attendee): ?>
            <?php
            $days = [];
            $daysRaw = $attendee['days_attending'];
            if (is_string($daysRaw)) {
                $decoded = json_decode($daysRaw, true);
                if (is_array($decoded)) $days = $decoded;
            } elseif (is_array($daysRaw)) {
                $days = $daysRaw;
            }

            $travel = [];
            $travelRaw = $attendee['travel_method'];
            if (is_string($travelRaw)) {
                $decoded = json_decode($travelRaw, true);
                if (is_array($decoded)) $travel = $decoded;
            } elseif (is_array($travelRaw)) {
                $travel = $travelRaw;
            }

            $uid = $attendee['user_id'];
            $userActivities = $activityBookingsByUser[$uid] ?? [];
            $userMeals      = $mealBookingsByUser[$uid] ?? [];
            ?>
            <div class="card" style="margin-bottom: 1rem;">
                <div class="card-header"
                     style="cursor: pointer; user-select: none; display: flex; justify-content: space-between; align-items: center;"
                     onclick="toggleWhosGoingRow('wg-<?php echo $uid; ?>')"
                     tabindex="0" role="button"
                     onkeydown="if(event.key==='Enter'||event.key===' '){toggleWhosGoingRow('wg-<?php echo $uid; ?>');}">
                    <div>
                        <strong><?php echo displayName($attendee['discord_name']); ?></strong>
                        <span style="margin-left: 1rem; font-size: 0.875rem; opacity: 0.85;">
                            <?php echo !empty($days) ? e(implode(', ', $days)) : '—'; ?>
                            &bull;
                            <?php echo !empty($travel) ? e(implode(', ', $travel)) : '—'; ?>
                        </span>
                    </div>
                    <span id="wg-icon-<?php echo $uid; ?>" aria-hidden="true">▼</span>
                </div>
                <div id="wg-<?php echo $uid; ?>" class="card-body" style="display: none;">
                    <div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 200px;">
                            <strong>Activities booked:</strong>
                            <?php if (empty($userActivities)): ?>
                                <p style="color: #999; margin-top: 0.25rem; font-size: 0.875rem;">None</p>
                            <?php else: ?>
                                <ul style="margin: 0.25rem 0 0; padding-left: 1.25rem; font-size: 0.875rem;">
                                    <?php foreach ($userActivities as $ab): ?>
                                    <li><?php echo e($ab['title']); ?> &mdash; <?php echo e($ab['day']); ?> <?php echo e(formatDisplayTime($ab['start_time'])); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                        <div style="flex: 1; min-width: 200px;">
                            <strong>Meals booked:</strong>
                            <?php if (empty($userMeals)): ?>
                                <p style="color: #999; margin-top: 0.25rem; font-size: 0.875rem;">None</p>
                            <?php else: ?>
                                <ul style="margin: 0.25rem 0 0; padding-left: 1.25rem; font-size: 0.875rem;">
                                    <?php foreach ($userMeals as $mb): ?>
                                    <li><?php echo e($mb['title']); ?> &mdash; <?php echo e($mb['day']); ?> <?php echo e(formatDisplayTime($mb['start_time'])); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleWhosGoingRow(id) {
    var el   = document.getElementById(id);
    var icon = document.getElementById('wg-icon-' + id.replace('wg-', ''));
    if (!el) return;
    if (el.style.display === 'none') {
        el.style.display = 'block';
        if (icon) icon.textContent = '▲';
    } else {
        el.style.display = 'none';
        if (icon) icon.textContent = '▼';
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
