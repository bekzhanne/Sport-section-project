<?php
require __DIR__ . '/includes/session.php';
require __DIR__ . '/includes/db.php';

require_login();

$userId = (int) current_user()['id'];
$stmt = db()->prepare('SELECT id, name, email, phone, birth_date FROM users WHERE id = ?');
$stmt->execute([$userId]);
$profile = $stmt->fetch();

if (!$profile) {
    unset($_SESSION['user']);
    redirect('login.php');
}

$name = $profile['name'];
$email = $profile['email'];
$phone = $profile['phone'] ?? '';
$birthDate = $profile['birth_date'] ?? '';
$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $name = trim(post_string('name'));
    $email = mb_strtolower(trim(post_string('email')));
    $phone = trim(post_string('phone'));
    $birthDate = trim(post_string('birth_date'));
    $currentPassword = post_string('current_password');
    $newPassword = post_string('new_password');
    $newPasswordConfirm = post_string('new_password_confirm');

    if (!verify_csrf()) {
        $errors['form'] = 'Your session has expired. Please try again.';
    }

    if (mb_strlen($name) < 1 || mb_strlen($name) > 100) {
        $errors['name'] = 'Name must be between 1 and 100 characters.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 255) {
        $errors['email'] = 'Please enter a valid email address.';
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

    $changingPassword = $newPassword !== '' || $newPasswordConfirm !== '';
    if ($changingPassword) {
        $hashStmt = db()->prepare('SELECT password_hash FROM users WHERE id = ?');
        $hashStmt->execute([$userId]);
        $currentHash = $hashStmt->fetchColumn();

        if ($currentPassword === '' || !password_verify($currentPassword, (string) $currentHash)) {
            $errors['current_password'] = 'Your current password is incorrect.';
        } elseif (strlen($newPassword) < 8) {
            $errors['new_password'] = 'New password must be at least 8 characters.';
        } elseif ($newPassword !== $newPasswordConfirm) {
            $errors['new_password_confirm'] = 'New passwords do not match.';
        }
    }

    if (!$errors) {
        $pdo = db();
        try {
            $pdo->beginTransaction();

            $dupStmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
            $dupStmt->execute([$email, $userId]);
            if ($dupStmt->fetch()) {
                $pdo->rollBack();
                $errors['email'] = 'An account with this email already exists.';
            } else {
                if ($changingPassword) {
                    $updateStmt = $pdo->prepare(
                        'UPDATE users SET name = ?, email = ?, phone = ?, birth_date = ?, password_hash = ? WHERE id = ?'
                    );
                    $updateStmt->execute([
                        $name,
                        $email,
                        $phone !== '' ? $phone : null,
                        $birthDate !== '' ? $birthDate : null,
                        password_hash($newPassword, PASSWORD_DEFAULT),
                        $userId,
                    ]);
                } else {
                    $updateStmt = $pdo->prepare(
                        'UPDATE users SET name = ?, email = ?, phone = ?, birth_date = ? WHERE id = ?'
                    );
                    $updateStmt->execute([
                        $name,
                        $email,
                        $phone !== '' ? $phone : null,
                        $birthDate !== '' ? $birthDate : null,
                        $userId,
                    ]);
                }

                $pdo->commit();

                $_SESSION['user']['name'] = $name;
                $_SESSION['user']['email'] = $email;
                redirect('profile.php?status=updated');
            }
        } catch (PDOException $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if ($ex->getCode() !== '23000') {
                throw $ex;
            }
            $errors['email'] = 'An account with this email already exists.';
        }
    }
}

$pageTitle = 'Edit profile · KBTU culturist';
require __DIR__ . '/includes/header.php';
?>
        <main class="auth-section">
            <div class="auth-card">
                <h1 class="auth-title">Edit profile</h1>
                <p class="auth-subtitle">Update your account details</p>

                <?php if (isset($errors['form'])): ?>
                    <div class="form-alert"><?= e($errors['form']) ?></div>
                <?php endif; ?>

                <form method="post" id="profile-edit-form" class="auth-form" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                    <label class="form-group">
                        <span class="form-label">Name</span>
                        <input type="text" name="name" class="form-input<?= isset($errors['name']) ? ' is-invalid' : '' ?>" value="<?= e($name) ?>" maxlength="100" required>
                        <?php if (isset($errors['name'])): ?><span class="form-error"><?= e($errors['name']) ?></span><?php endif; ?>
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
                        <input type="date" name="birth_date" class="form-input<?= isset($errors['birth_date']) ? ' is-invalid' : '' ?>" value="<?= e($birthDate) ?>" max="<?= e(date('Y-m-d')) ?>">
                        <?php if (isset($errors['birth_date'])): ?><span class="form-error"><?= e($errors['birth_date']) ?></span><?php endif; ?>
                    </label>

                    <label class="form-group">
                        <span class="form-label">Current password <span class="form-optional">(only to change password)</span></span>
                        <input type="password" name="current_password" class="form-input<?= isset($errors['current_password']) ? ' is-invalid' : '' ?>" autocomplete="current-password">
                        <?php if (isset($errors['current_password'])): ?><span class="form-error"><?= e($errors['current_password']) ?></span><?php endif; ?>
                    </label>

                    <label class="form-group">
                        <span class="form-label">New password <span class="form-optional">(optional)</span></span>
                        <input type="password" name="new_password" class="form-input<?= isset($errors['new_password']) ? ' is-invalid' : '' ?>" autocomplete="new-password" minlength="8">
                        <?php if (isset($errors['new_password'])): ?><span class="form-error"><?= e($errors['new_password']) ?></span><?php endif; ?>
                    </label>

                    <label class="form-group">
                        <span class="form-label">Confirm new password</span>
                        <input type="password" name="new_password_confirm" class="form-input<?= isset($errors['new_password_confirm']) ? ' is-invalid' : '' ?>" autocomplete="new-password">
                        <?php if (isset($errors['new_password_confirm'])): ?><span class="form-error"><?= e($errors['new_password_confirm']) ?></span><?php endif; ?>
                    </label>

                    <button type="submit" class="btn btn-navy auth-submit">Save changes</button>
                </form>

                <p class="auth-switch"><a href="profile.php">← Back to profile</a></p>
            </div>
        </main>
<?php require __DIR__ . '/includes/footer.php'; ?>
