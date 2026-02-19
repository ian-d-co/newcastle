<?php
$currentPage = 'admin';
ob_start();
?>

<div class="section">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 class="section-title">Pending User Approvals</h1>
            <a href="/index.php?page=admin_users" class="btn btn-secondary">Back to Users</a>
        </div>

        <?php if (empty($pendingUsers)): ?>
            <div class="card">
                <div class="card-body text-center">
                    <p>No pending user approvals.</p>
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
                                <th style="padding: 0.75rem; text-align: left;">Registered</th>
                                <th style="padding: 0.75rem; text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingUsers as $user): ?>
                                <tr style="border-bottom: 1px solid #dee2e6;">
                                    <td style="padding: 0.75rem;"><?php echo e($user['discord_name']); ?></td>
                                    <td style="padding: 0.75rem;"><?php echo e($user['name']); ?></td>
                                    <td style="padding: 0.75rem;"><?php echo formatDisplayDate($user['created_at']); ?></td>
                                    <td style="padding: 0.75rem; text-align: right;">
                                        <button onclick="approveUser(<?php echo (int)$user['id']; ?>)"
                                                class="btn btn-sm btn-success" style="margin-right: 0.5rem;">Approve</button>
                                        <button onclick="rejectUser(<?php echo (int)$user['id']; ?>, '<?php echo e($user['discord_name']); ?>')"
                                                class="btn btn-sm btn-danger">Reject</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function approveUser(userId) {
    if (!confirm('Approve this user?')) return;

    fetch('/index.php?page=admin_users&action=approve', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: userId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('User approved!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert(data.message || 'Failed to approve user', 'danger');
        }
    })
    .catch(() => showAlert('An error occurred', 'danger'));
}

function rejectUser(userId, userName) {
    if (!confirm('Reject and delete user "' + userName + '"? This cannot be undone.')) return;

    fetch('/index.php?page=admin_users&action=reject', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: userId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('User rejected and deleted', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert(data.message || 'Failed to reject user', 'danger');
        }
    })
    .catch(() => showAlert('An error occurred', 'danger'));
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
