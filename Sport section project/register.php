<?php
require __DIR__ . '/includes/session.php';
require __DIR__ . '/includes/db.php';

if (is_logged_in()) {
    redirect('mp.php');
}

$firstName = '';
$lastName = '';
$email = '';
$phone = '';
$birthDate = '';
$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $firstName = trim(post_string('first_name'));
    $lastName = trim(post_string('last_name'));
    $name = trim($firstName . ' ' . $lastName);
    $email = mb_strtolower(trim(post_string('email')));
    $password = post_string('password');
    $passwordConfirm = post_string('password_confirm');
    $phone = trim(post_string('phone'));
    $birthDate = trim(post_string('birth_date'));

    if (!verify_csrf()) {
        $errors['form'] = 'Your session has expired. Please try again.';
    }

    if (mb_strlen($firstName) < 1 || mb_strlen($firstName) > 50) {
        $errors['first_name'] = 'First name must be between 1 and 50 characters.';
    }

    if (mb_strlen($lastName) < 1 || mb_strlen($lastName) > 50) {
        $errors['last_name'] = 'Last name must be between 1 and 50 characters.';
    }

    if (mb_strlen($name) > 100) {
        $errors['form'] = 'First and last name together must not exceed 100 characters.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 255) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters.';
    } elseif ($password !== $passwordConfirm) {
        $errors['password_confirm'] = 'Passwords do not match.';
    }

    if ($phone !== '' && !preg_match('/^[0-9+\s()-]{7,20}$/', $phone)) {
        $errors['phone'] = 'Please enter a valid phone number.';
    }

    if ($birthDate !== '') {
        $birthDateTime = DateTime::createFromFormat('Y-m-d', $birthDate);
        if (!$birthDateTime || $birthDateTime->format('Y-m-d') !== $birthDate || $birthDateTime > new DateTime()) {
            $errors['birth_date'] = 'Please enter a valid date of birth.';
        }
    }

    if (!$errors) {
        try {
            $stmt = db()->prepare('INSERT INTO users (name, email, password_hash, phone, birth_date) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $phone !== '' ? $phone : null, $birthDate !== '' ? $birthDate : null]);

            login_user(['id' => db()->lastInsertId(), 'name' => $name, 'email' => $email, 'is_admin' => false]);
            redirect('mp.php');
        } catch (PDOException $ex) {
            // 23000 = unique key violation on users.email
            if ($ex->getCode() !== '23000') {
                throw $ex;
            }
            $errors['email'] = 'An account with this email already exists.';
        }
    }
}

$pageTitle = 'Register · KBTU culturist';
require __DIR__ . '/includes/header.php';
?>
        <main class="auth-section">
            <div class="auth-card">
                <h1 class="auth-title">Register</h1>
                <p class="auth-subtitle">Enlist to sections and track your progress</p>

                <?php if (isset($errors['form'])): ?>
                    <div class="form-alert"><?= e($errors['form']) ?></div>
                <?php endif; ?>

                <form method="post" id="register-form" class="auth-form" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                    <label class="form-group">
                        <span class="form-label">First name</span>
                        <input type="text" name="first_name" class="form-input<?= isset($errors['first_name']) ? ' is-invalid' : '' ?>" value="<?= e($firstName) ?>" autocomplete="given-name" maxlength="50" required>
                        <?php if (isset($errors['first_name'])): ?><span class="form-error"><?= e($errors['first_name']) ?></span><?php endif; ?>
                    </label>

                    <label class="form-group">
                        <span class="form-label">Last name</span>
                        <input type="text" name="last_name" class="form-input<?= isset($errors['last_name']) ? ' is-invalid' : '' ?>" value="<?= e($lastName) ?>" autocomplete="family-name" maxlength="50" required>
                        <?php if (isset($errors['last_name'])): ?><span class="form-error"><?= e($errors['last_name']) ?></span><?php endif; ?>
                    </label>

                    <label class="form-group">
                        <span class="form-label">Email</span>
                        <input type="email" name="email" class="form-input<?= isset($errors['email']) ? ' is-invalid' : '' ?>" value="<?= e($email) ?>" autocomplete="email" required>
                        <?php if (isset($errors['email'])): ?><span class="form-error"><?= e($errors['email']) ?></span><?php endif; ?>
                    </label>

                    <label class="form-group">
                        <span class="form-label">Phone number <span class="form-optional">(optional)</span></span>
                        <input type="tel" name="phone" class="form-input<?= isset($errors['phone']) ? ' is-invalid' : '' ?>" value="<?= e($phone) ?>" autocomplete="tel" maxlength="20">
                        <?php if (isset($errors['phone'])): ?><span class="form-error"><?= e($errors['phone']) ?></span><?php endif; ?>
                    </label>

                    <label class="form-group">
                        <span class="form-label">Date of birth <span class="form-optional">(optional)</span></span>
                        <input type="date" name="birth_date" class="form-input<?= isset($errors['birth_date']) ? ' is-invalid' : '' ?>" value="<?= e($birthDate) ?>" autocomplete="bday" max="<?= e(date('Y-m-d')) ?>">
                        <?php if (isset($errors['birth_date'])): ?><span class="form-error"><?= e($errors['birth_date']) ?></span><?php endif; ?>
                    </label>

                    <label class="form-group">
                        <span class="form-label">Password</span>
                        <input type="password" name="password" class="form-input<?= isset($errors['password']) ? ' is-invalid' : '' ?>" autocomplete="new-password" minlength="8" required>
                        <?php if (isset($errors['password'])): ?><span class="form-error"><?= e($errors['password']) ?></span><?php endif; ?>
                    </label>

                    <label class="form-group">
                        <span class="form-label">Confirm password</span>
                        <input type="password" name="password_confirm" class="form-input<?= isset($errors['password_confirm']) ? ' is-invalid' : '' ?>" autocomplete="new-password" required>
                        <?php if (isset($errors['password_confirm'])): ?><span class="form-error"><?= e($errors['password_confirm']) ?></span><?php endif; ?>
                    </label>

                    <button type="submit" class="btn btn-navy auth-submit">Create account</button>
                </form>

                <p class="auth-switch">Already have an account? <a href="login.php">Log in</a></p>
            </div>
        </main>
<?php require __DIR__ . '/includes/footer.php'; ?>
