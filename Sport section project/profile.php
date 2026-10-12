<?php
require __DIR__ . '/includes/session.php';
require __DIR__ . '/includes/db.php';

require_login();

$stmt = db()->prepare('SELECT id, name, email, phone, birth_date, created_at FROM users WHERE id = ?');
$stmt->execute([current_user()['id']]);
$profile = $stmt->fetch();

// Account was deleted from the database while the session was still alive
if (!$profile) {
    unset($_SESSION['user']);
    redirect('login.php');
}

$membershipsStmt = db()->prepare(
    "SELECT s.id, s.name, s.instructor, s.location, m.joined_at
     FROM section_memberships m
     INNER JOIN sports_sections s ON s.id = m.section_id
     WHERE m.user_id = ? AND m.status = 'active' AND s.is_active = 1
     ORDER BY m.joined_at DESC"
);
$membershipsStmt->execute([$profile['id']]);
$memberships = $membershipsStmt->fetchAll();

$bookingsStmt = db()->prepare(
    "SELECT b.id, b.status, cs.id AS session_id, cs.section_id, cs.starts_at,
            s.name AS section_name, s.location
     FROM class_bookings b
     INNER JOIN class_sessions cs ON cs.id = b.session_id
     INNER JOIN sports_sections s ON s.id = cs.section_id
     WHERE b.user_id = ?
     ORDER BY cs.starts_at DESC
     LIMIT 50"
);
$bookingsStmt->execute([$profile['id']]);
$bookings = $bookingsStmt->fetchAll();

$messages = [
    'updated' => ['success', 'Your profile has been updated.'],
];
$notice = $messages[$_GET['status'] ?? ''] ?? null;

$pageTitle = 'My profile · KBTU culturist';
require __DIR__ . '/includes/header.php';
?>
        <main class="profile-section">
            <div class="container">
                <?php if ($notice): ?>
                    <div class="form-alert <?= e($notice[0]) ?>" role="status"><?= e($notice[1]) ?></div>
                <?php endif; ?>

                <div class="auth-card profile-card">
                <div class="profile-avatar"><img src="static/img/profile-icon.svg" alt=""></div>
                <h1 class="auth-title"><?= e($profile['name']) ?></h1>
                <p class="auth-subtitle"><?= e($profile['email']) ?></p>

                <dl class="profile-details">
                    <dt>Member since</dt>
                    <dd><?= e(date('d.m.Y', strtotime($profile['created_at']))) ?></dd>
                </dl>
                <?php if ($profile['phone']): ?>
                    <dl class="profile-details">
                        <dt>Phone</dt>
                        <dd><?= e($profile['phone']) ?></dd>
                    </dl>
                <?php endif; ?>
                <?php if ($profile['birth_date']): ?>
                    <dl class="profile-details">
                        <dt>Date of birth</dt>
                        <dd><?= e(date('d.m.Y', strtotime($profile['birth_date']))) ?></dd>
                    </dl>
                <?php endif; ?>
                <a href="profile_edit.php" class="btn btn-outline-navy auth-submit">Edit profile</a>
                </div>

                <section class="profile-list">
                    <div class="schedule-heading">
                        <div>
                            <p class="eyebrow">Your activities</p>
                            <h2>My sections</h2>
                        </div>
                        <a class="text-link" href="sections.php">Explore sections</a>
                    </div>
                    <?php if (!$memberships): ?>
                        <div class="empty-state">You have not joined any sections yet.</div>
                    <?php else: ?>
                        <div class="profile-records">
                            <?php foreach ($memberships as $membership): ?>
                                <a class="profile-record" href="section.php?id=<?= (int) $membership['id'] ?>">
                                    <span>
                                        <strong><?= e($membership['name']) ?></strong>
                                        <small><?= e($membership['instructor']) ?> · <?= e($membership['location']) ?></small>
                                    </span>
                                    <span class="text-link">View schedule</span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="profile-list" id="bookings">
                    <div class="schedule-heading">
                        <div>
                            <p class="eyebrow">Reservations</p>
                            <h2>My bookings</h2>
                        </div>
                    </div>
                    <?php if (!$bookings): ?>
                        <div class="empty-state">Your class bookings will appear here.</div>
                    <?php else: ?>
                        <div class="profile-records">
                            <?php foreach ($bookings as $booking): ?>
                                <?php $isUpcoming = strtotime($booking['starts_at']) > time(); ?>
                                <article class="profile-record">
                                    <span>
                                        <strong>#<?= (int) $booking['id'] ?> · <?= e($booking['section_name']) ?></strong>
                                        <small><?= e(date('D, d M Y · H:i', strtotime($booking['starts_at']))) ?> · <?= e($booking['location']) ?></small>
                                    </span>
                                    <span class="booking-record-actions">
                                        <span class="booking-status <?= $booking['status'] === 'booked' ? 'is-booked' : 'is-cancelled' ?>">
                                            <?= $booking['status'] === 'booked' ? 'Confirmed' : 'Cancelled' ?>
                                        </span>
                                        <?php if ($booking['status'] === 'booked' && $isUpcoming): ?>
                                            <form action="enroll.php" method="post">
                                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                                <input type="hidden" name="action" value="cancel_class">
                                                <input type="hidden" name="section_id" value="<?= (int) $booking['section_id'] ?>">
                                                <input type="hidden" name="session_id" value="<?= (int) $booking['session_id'] ?>">
                                                <button type="submit" class="text-link">Cancel</button>
                                            </form>
                                        <?php endif; ?>
                                    </span>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <form action="logout.php" method="post" class="profile-logout">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <button type="submit" class="btn btn-outline-navy">Log out</button>
                </form>
            </div>
        </main>
<?php require __DIR__ . '/includes/footer.php'; ?>
