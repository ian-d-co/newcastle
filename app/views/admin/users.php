<?php
$currentPage = 'admin';
ob_start();
?>

<div class="section">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 class="section-title">Manage Users</h1>
            <div style="display: flex; gap: 0.5rem;">
                <a href="/index.php?page=admin_users&action=pending" class="btn btn-warning">Pending Approvals</a>
                <a href="/index.php?page=admin" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>

        <?php if (empty($users)): ?>
            <div class="card">
                <div class="card-body text-center">
                    <p>No users registered yet.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid #dee2e6;">
                                <th style="padding: 0.75rem; text-align: left;">Discord Name</th>
                                <th style="padding: 0.75rem; text-align: left;">Real Name</th>
                                <th style="padding: 0.75rem; text-align: center;">Admin</th>
                                <th style="padding: 0.75rem; text-align: center;">Attending</th>
                                <th style="padding: 0.75rem; text-align: left;">Registered</th>
                                <th style="padding: 0.75rem; text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr style="border-bottom: 1px solid #dee2e6;">
                                    <td style="padding: 0.75rem;">
                                        <?php echo e($user['discord_name']); ?>
                                    </td>
                                    <td style="padding: 0.75rem;">
                                        <?php echo e($user['name']); ?>
                                    </td>
                                    <td style="padding: 0.75rem; text-align: center;">
                                        <?php if ($user['is_admin']): ?>
                                            <span style="color: #28a745; font-weight: bold;">✓ Admin</span>
                                        <?php else: ?>
                                            <span style="color: #999;">User</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 0.75rem; text-align: center;">
                                        <?php if ($user['attendance_id']): ?>
                                            <span style="color: #28a745;">✓</span>
                                            <?php if (!empty($user['days_attending'])): ?>
                                                <br><small style="color: #666;">
                                                    <?php echo implode(', ', $user['days_attending']); ?>
                                                </small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span style="color: #999;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 0.75rem;">
                                        <?php echo formatDisplayDate($user['created_at']); ?>
                                    </td>
                                    <td style="padding: 0.75rem; text-align: right;">
                                        <?php if ($user['id'] != getCurrentUserId()): ?>
                                            <button onclick="toggleAdmin(<?php echo $user['id']; ?>, <?php echo $user['is_admin'] ? 0 : 1; ?>)" 
                                                    class="btn btn-sm btn-warning" style="margin-right: 0.5rem;">
                                                <?php echo $user['is_admin'] ? 'Remove Admin' : 'Make Admin'; ?>
                                            </button>
                                            <button onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo e($user['discord_name']); ?>')" 
                                                    class="btn btn-sm btn-danger">Delete</button>
                                        <?php else: ?>
                                            <span style="color: #999; font-style: italic;">You</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="card" style="margin-top: 1.5rem;">
                <div class="card-body">
                    <h3>User Statistics</h3>
                    <div class="row">
                        <div class="col">
                            <p><strong>Total Users:</strong> <?php echo count($users); ?></p>
                        </div>
                        <div class="col">
                            <p><strong>Admins:</strong> <?php echo count(array_filter($users, function($u) { return $u['is_admin']; })); ?></p>
                        </div>
                        <div class="col">
                            <p><strong>Attending:</strong> <?php echo count(array_filter($users, function($u) { return $u['attendance_id']; })); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleAdmin(userId, makeAdmin) {
    const action = makeAdmin ? 'make admin' : 'remove admin privileges from';
    
    if (!confirm(`Are you sure you want to ${action} this user?`)) {
        return;
    }
    
    fetch('/index.php?page=admin_users&action=toggle_admin', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: userId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('User admin status updated!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert(data.message || 'Failed to update user', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred', 'danger');
    });
}

function deleteUser(userId, userName) {
    if (!confirm(`Are you sure you want to delete user "${userName}"?\n\nThis will remove:\n- Their account\n- All their event registrations\n- All their bookings\n- All their votes\n- All their offers (carshare, hosting)\n\nThis action cannot be undone.`)) {
        return;
    }
    
    // Second confirmation
    if (!confirm('Are you ABSOLUTELY sure? This cannot be undone!')) {
        return;
    }
    
    fetch('/index.php?page=admin_users&action=delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: userId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('User deleted successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert(data.message || 'Failed to delete user', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred', 'danger');
    });
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
