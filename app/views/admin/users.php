<?php
$currentPage = 'admin-users';
ob_start();
?>

<div class="section">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 class="section-title">User Management</h1>
            <a href="/index.php?page=admin" class="btn btn-secondary">← Back to Admin</a>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo e($success); ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo e($error); ?></div>
        <?php endif; ?>

        <!-- Search and Filter -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-body">
                <form method="GET" action="/index.php" style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <input type="hidden" name="page" value="admin-users">
                    <div style="flex: 1; min-width: 250px;">
                        <input type="text" class="form-control" name="search" placeholder="Search by name or Discord name..." value="<?php echo e($_GET['search'] ?? ''); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Search</button>
                    <?php if (isset($_GET['search'])): ?>
                        <a href="/index.php?page=admin-users" class="btn btn-secondary">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Users List -->
        <div class="card">
            <div class="card-header">
                Registered Users (<?php echo count($users); ?> total)
            </div>
            <div class="card-body">
                <?php if (empty($users)): ?>
                    <p class="text-center" style="padding: 2rem;">No users found.</p>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 2px solid #dee2e6;">
                                    <th style="padding: 0.75rem; text-align: left;">ID</th>
                                    <th style="padding: 0.75rem; text-align: left;">Discord Name</th>
                                    <th style="padding: 0.75rem; text-align: left;">Real Name</th>
                                    <th style="padding: 0.75rem; text-align: left;">Admin</th>
                                    <th style="padding: 0.75rem; text-align: left;">Registered</th>
                                    <th style="padding: 0.75rem; text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr style="border-bottom: 1px solid #dee2e6;">
                                        <td style="padding: 0.75rem;"><?php echo e($user['id']); ?></td>
                                        <td style="padding: 0.75rem;"><strong><?php echo e($user['discord_name']); ?></strong></td>
                                        <td style="padding: 0.75rem;"><?php echo e($user['name']); ?></td>
                                        <td style="padding: 0.75rem;">
                                            <?php if ($user['is_admin']): ?>
                                                <span class="badge badge-primary">Admin</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">User</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 0.75rem;"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                        <td style="padding: 0.75rem; text-align: center;">
                                            <button class="btn btn-sm btn-primary" onclick="editUser(<?php echo $user['id']; ?>)">Edit</button>
                                            <?php if ($user['id'] != getCurrentUserId()): ?>
                                                <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo e($user['discord_name']); ?>')">Delete</button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal" id="edit-user-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Edit User</h3>
            <button class="modal-close" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="edit-user-form" method="POST" action="/index.php?page=admin-users&action=edit">
                <?php echo CSRF::field(); ?>
                <input type="hidden" id="edit-user-id" name="user_id">
                
                <div class="form-group">
                    <label class="form-label" for="edit-discord-name">Discord Name *</label>
                    <input type="text" class="form-control" id="edit-discord-name" name="discord_name" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="edit-name">Real Name *</label>
                    <input type="text" class="form-control" id="edit-name" name="name" required>
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="edit-is-admin" name="is_admin" value="1">
                        <label class="form-check-label" for="edit-is-admin">
                            Administrator
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="edit-new-pin">New PIN (leave blank to keep current)</label>
                    <input type="password" class="form-control" id="edit-new-pin" name="new_pin" minlength="4">
                    <small>Minimum 4 characters</small>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg">Save Changes</button>
            </form>
        </div>
    </div>
</div>

<script>
const usersData = <?php echo json_encode($users); ?>;

function editUser(userId) {
    const user = usersData.find(u => u.id == userId);
    if (!user) return;
    
    document.getElementById('edit-user-id').value = user.id;
    document.getElementById('edit-discord-name').value = user.discord_name;
    document.getElementById('edit-name').value = user.name;
    document.getElementById('edit-is-admin').checked = user.is_admin == 1;
    document.getElementById('edit-new-pin').value = '';
    
    modalManager.open('edit-user-modal');
}

function deleteUser(userId, discordName) {
    if (!confirm(`Are you sure you want to delete user "${discordName}"? This action cannot be undone and will remove all their bookings and data.`)) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/index.php?page=admin-users&action=delete';
    
    const csrfField = document.createElement('input');
    csrfField.type = 'hidden';
    csrfField.name = '_csrf_token';
    csrfField.value = document.querySelector('meta[name="csrf-token"]').content;
    form.appendChild(csrfField);
    
    const userIdField = document.createElement('input');
    userIdField.type = 'hidden';
    userIdField.name = 'user_id';
    userIdField.value = userId;
    form.appendChild(userIdField);
    
    document.body.appendChild(form);
    form.submit();
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
