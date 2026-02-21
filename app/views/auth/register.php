<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Dickscord Fest 2026</title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
    <div class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Dickscord Fest 2026 - Newcastle</h1>
                <p>November 20-22, 2026</p>
            </div>
        </div>
    </div>

    <div class="container" style="max-width: 500px; margin-top: 3rem;">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo e($error); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">Register for Dickscord Fest 2026</div>
            <div class="card-body">
                <form method="POST" action="/index.php?action=register">
                    <?php echo CSRF::field(); ?>

                    <div class="form-group">
                        <label class="form-label" for="discord_name">Discord Name</label>
                        <input type="text" class="form-control" id="discord_name" name="discord_name" required autofocus>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="name">Your Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="pin">PIN (4-6 digits)</label>
                        <input type="password" class="form-control" id="pin" name="pin" pattern="[0-9]{4,6}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="pin_confirm">Confirm PIN</label>
                        <input type="password" class="form-control" id="pin_confirm" name="pin_confirm" pattern="[0-9]{4,6}" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg">Register</button>
                </form>
            </div>
            <div class="card-footer text-center">
                <p>Already have an account? <a href="/index.php?page=login">Login</a></p>
            </div>
        </div>
    </div>

</body>
</html>
