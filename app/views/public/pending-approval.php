<?php
$currentPage = 'pending_approval';
ob_start();
?>

<div class="section">
    <div class="container text-center">
        <div class="card" style="max-width: 600px; margin: 3rem auto;">
            <div class="card-body">
                <h1>⏳ Awaiting Approval</h1>
                <p class="lead">Thank you for registering!</p>
                <p>Your account is pending approval from an administrator.</p>
                <p>You will be able to access the system once your account has been approved.</p>
                <hr>
                <p><small>Please check back later or contact an administrator if you have questions.</small></p>
                <a href="/index.php?action=logout" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
