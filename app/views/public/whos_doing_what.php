<?php
$currentPage = 'whos_doing_what';
ob_start();

$db = getDbConnection();

// Get all activities ordered by day, start_time
$stmt = $db->prepare("
    SELECT a.id, a.title, a.day, a.start_time, a.end_time, a.max_capacity, a.current_bookings
    FROM activities a
    WHERE a.event_id = :event_id
    ORDER BY FIELD(a.day, 'Friday', 'Saturday', 'Sunday'), a.start_time
");
$stmt->execute(['event_id' => $event['id']]);
$activities = $stmt->fetchAll();

// Get bookers for each activity
$bookersByActivity = [];
if (!empty($activities)) {
    $activityIds  = array_column($activities, 'id');
    $placeholders = implode(',', array_fill(0, count($activityIds), '?'));
    $stmt = $db->prepare("
        SELECT ab.activity_id, u.discord_name
        FROM activity_bookings ab
        JOIN users u ON ab.user_id = u.id
        WHERE ab.activity_id IN ($placeholders)
        ORDER BY u.discord_name
    ");
    $stmt->execute($activityIds);
    foreach ($stmt->fetchAll() as $row) {
        $bookersByActivity[$row['activity_id']][] = $row['discord_name'];
    }
}
?>

<div class="section">
    <div class="container">
        <h1 class="section-title">Who's Doing What</h1>

        <p style="color: #666; margin-bottom: 1.5rem;">Click an activity to see who has booked it.</p>

        <?php if (empty($activities)): ?>
            <div class="card">
                <div class="card-body text-center">
                    <p>No activities available yet.</p>
                </div>
            </div>
        <?php else: ?>
            <?php
            $days = ['Friday' => [], 'Saturday' => [], 'Sunday' => []];
            foreach ($activities as $activity) {
                $dayKey = ucfirst(strtolower(trim($activity['day'])));
                if (isset($days[$dayKey])) {
                    $days[$dayKey][] = $activity;
                }
            }
            ?>
            <?php foreach ($days as $day => $dayActivities): ?>
                <?php if (!empty($dayActivities)): ?>
                <div class="expander" style="margin-bottom: 1.5rem;">
                    <div class="expander-header" onclick="toggleExpander(this)"
                         style="background: linear-gradient(135deg, #6d4c1f 0%, #8b6331 100%); color: #f5f5dc;">
                        <h2 style="margin: 0; font-weight: 400;"><?php echo e($day); ?> (<?php echo count($dayActivities); ?>)</h2>
                        <span class="expander-icon">▶</span>
                    </div>
                    <div class="expander-content">
                        <?php foreach ($dayActivities as $activity): ?>
                        <?php
                        $bookers = $bookersByActivity[$activity['id']] ?? [];
                        $aid     = $activity['id'];
                        ?>
                        <div class="item" style="margin-bottom: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.5rem; cursor: pointer;"
                                 onclick="toggleWdw('wdw-<?php echo $aid; ?>')"
                                 tabindex="0" role="button"
                                 onkeydown="if(event.key==='Enter'||event.key===' '){toggleWdw('wdw-<?php echo $aid; ?>');}">
                                <div>
                                    <h4 style="margin: 0 0 0.25rem; color: #613704;"><?php echo e($activity['title']); ?></h4>
                                    <span style="font-size: 0.875rem; color: #666;">
                                        <?php echo e(formatDisplayTime($activity['start_time'])); ?>
                                        <?php if ($activity['end_time']): ?> &ndash; <?php echo e(formatDisplayTime($activity['end_time'])); ?><?php endif; ?>
                                    </span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <span class="badge badge-secondary"><?php echo count($bookers); ?> / <?php echo e($activity['max_capacity']); ?> booked</span>
                                    <span id="wdw-icon-<?php echo $aid; ?>" aria-hidden="true">▼</span>
                                </div>
                            </div>
                            <div id="wdw-<?php echo $aid; ?>" style="display: none; margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid #FDDC62;">
                                <?php if (empty($bookers)): ?>
                                    <p style="color: #999; font-size: 0.875rem; margin: 0;">Nobody has booked this yet.</p>
                                <?php else: ?>
                                    <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.875rem; columns: 2; column-gap: 1.5rem;">
                                        <?php foreach ($bookers as $name): ?>
                                        <li><?php echo displayName($name); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleExpander(header) {
    header.classList.toggle('active');
    var content = header.nextElementSibling;
    content.classList.toggle('active');
    var icon = header.querySelector('.expander-icon');
    icon.textContent = header.classList.contains('active') ? '▼' : '▶';
}

function toggleWdw(id) {
    var el   = document.getElementById(id);
    var icon = document.getElementById('wdw-icon-' + id.replace('wdw-', ''));
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
