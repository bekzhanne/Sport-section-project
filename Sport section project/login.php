<?php
require __DIR__ . '/includes/session.php';
require __DIR__ . '/includes/db.php';

if (is_logged_in()) {
    redirect('mp.php');
}

$email = '';
$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $email = mb_strtolower(trim(post_string('email')));
    $password = post_string('password');

    if (!verify_csrf()) {
        $error = 'Your session has expired. Please try again.';
    } elseif ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        $stmt = db()->prepare('SELECT id, name, email, password_hash FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            login_user($user);
            redirect('mp.php');
        }

        // Same message for unknown email and wrong password, so emails can't be probed
        $error = 'Invalid email or password.';
    }
}

$pageTitle = 'Log in · KBTU culturist';
require __DIR__ . '/includes/header.php';
?>
        <main class="auth-section">
            <div class="auth-card">
                <h1 class="auth-title">Log in</h1>
                <p class="auth-subtitle">Welcome back to KBTU culturist</p>

                <?php if ($error): ?>
                    <div class="form-alert"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="post" class="auth-form" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                    <label class="form-group">
                        <span class="form-label">Email</span>
                        <input type="email" name="email" class="form-input" value="<?= e($email) ?>" autocomplete="email" required>
                    </label>

                    <label class="form-group">
                        <span class="form-label">Password</span>
                        <input type="password" name="password" class="form-input" autocomplete="current-password" required>
                    </label>

                    <button type="submit" class="btn btn-navy auth-submit">Log in</button>
                </form>

                <p class="auth-switch">Don't have an account? <a href="register.php">Register</a></p>
            </div>
        </main>
<?php require __DIR__ . '/includes/footer.php'; ?>
