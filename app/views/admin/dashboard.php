<?php
$currentPage = 'admin';
ob_start();
?>

<div class="section">
    <div class="container">
        <h1 class="section-title">Admin Dashboard</h1>

        <div class="dashboard-grid">
            <div class="dashboard-stat">
                <div class="dashboard-stat-value"><?php echo $stats['total_attendees'] ?? 0; ?></div>
                <div class="dashboard-stat-label">Total Attendees</div>
            </div>

            <div class="dashboard-stat">
                <div class="dashboard-stat-value"><?php echo $stats['total_activities'] ?? 0; ?></div>
                <div class="dashboard-stat-label">Activities</div>
            </div>

            <div class="dashboard-stat">
                <div class="dashboard-stat-value"><?php echo $stats['total_meals'] ?? 0; ?></div>
                <div class="dashboard-stat-label">Meals</div>
            </div>

            <div class="dashboard-stat">
                <div class="dashboard-stat-value"><?php echo $stats['carshare_offers'] ?? 0; ?></div>
                <div class="dashboard-stat-label">Carshare Offers</div>
            </div>

            <div class="dashboard-stat">
                <div class="dashboard-stat-value"><?php echo $stats['hosting_offers'] ?? 0; ?></div>
                <div class="dashboard-stat-label">Hosting Offers</div>
            </div>

            <div class="dashboard-stat">
                <div class="dashboard-stat-value"><?php echo $stats['active_polls'] ?? 0; ?></div>
                <div class="dashboard-stat-label">Active Polls</div>
            </div>
        </div>

        <h2 class="text-primary mt-4 mb-3">Quick Actions</h2>
        
        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-header">Event Management</div>
                    <div class="card-body">
                        <p>Edit event information, manage dates, and update content.</p>
                        <a href="/index.php?page=admin_event" class="btn btn-primary btn-block">Edit Event</a>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card">
                    <div class="card-header">Activities</div>
                    <div class="card-body">
                        <p>Create, edit, and manage activities for the event.</p>
                        <a href="/index.php?page=admin_activities" class="btn btn-primary btn-block">Manage Activities</a>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card">
                    <div class="card-header">Meals</div>
                    <div class="card-body">
                        <p>Create, edit, and manage meals for the event.</p>
                        <a href="/index.php?page=admin_meals" class="btn btn-primary btn-block">Manage Meals</a>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card">
                    <div class="card-header">Polls</div>
                    <div class="card-body">
                        <p>Create and manage polls with various options.</p>
                        <a href="/index.php?page=admin_polls" class="btn btn-primary btn-block">Manage Polls</a>
                        <a href="/index.php?page=admin_poll_categories" class="btn btn-secondary btn-block" style="margin-top: 0.5rem;">Manage Categories</a>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card">
                    <div class="card-header">Hotels</div>
                    <div class="card-body">
                        <p>Add hotels, manage rooms, and track reservations.</p>
                        <a href="/index.php?page=admin_hotels" class="btn btn-primary btn-block">Manage Hotels</a>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card">
                    <div class="card-header">Users</div>
                    <div class="card-body">
                        <p>View and manage registered users.</p>
                        <a href="/index.php?page=admin_users" class="btn btn-primary btn-block">Manage Users</a>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card">
                    <div class="card-header">Payments</div>
                    <div class="card-body">
                        <p>Track and manage payments for activities and meals.</p>
                        <a href="/index.php?page=admin_payments" class="btn btn-primary btn-block">Manage Payments</a>
                    </div>
                </div>
            </div>
        </div>

        <h2 class="text-primary mt-4 mb-3">Recent Attendees</h2>
        
        <?php if (!empty($recentAttendees)): ?>
            <div class="card">
                <div class="card-body">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid #dee2e6;">
                                <th style="padding: 0.75rem; text-align: left;">Discord Name</th>
                                <th style="padding: 0.75rem; text-align: left;">Name</th>
                                <th style="padding: 0.75rem; text-align: left;">Days</th>
                                <th style="padding: 0.75rem; text-align: left;">Registered</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentAttendees as $attendee): ?>
                                <tr style="border-bottom: 1px solid #dee2e6;">
                                    <td style="padding: 0.75rem;"><?php echo e($attendee['discord_name']); ?></td>
                                    <td style="padding: 0.75rem;"><?php echo e($attendee['name']); ?></td>
                                    <td style="padding: 0.75rem;"><?php echo implode(', ', $attendee['days_attending']); ?></td>
                                    <td style="padding: 0.75rem;"><?php echo formatDisplayDate($attendee['created_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center">
                    <p>No attendees registered yet.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
