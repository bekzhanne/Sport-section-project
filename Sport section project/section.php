<?php
require __DIR__ . '/includes/session.php';
require __DIR__ . '/includes/db.php';

$sectionId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$sectionId) {
    redirect('sections.php?status=unavailable');
}

$stmt = db()->prepare(
    "SELECT s.*,
            COUNT(DISTINCT CASE WHEN m.status = 'active' THEN m.user_id END) AS active_members
     FROM sports_sections s
     LEFT JOIN section_memberships m ON m.section_id = s.id
     WHERE s.id = ? AND s.is_active = 1
     GROUP BY s.id"
);
$stmt->execute([$sectionId]);
$section = $stmt->fetch();

if (!$section) {
    redirect('sections.php?status=unavailable');
}

$user = current_user();
$isMember = false;
$bookingsBySession = [];
$sessions = [];

if ($user) {
    $membershipStmt = db()->prepare("SELECT status FROM section_memberships WHERE user_id = ? AND section_id = ?");
    $membershipStmt->execute([$user['id'], $sectionId]);
    $isMember = $membershipStmt->fetchColumn() === 'active';
}

$sessionsStmt = db()->prepare(
    "SELECT cs.id, cs.starts_at, cs.capacity,
            COUNT(CASE WHEN b.status = 'booked' THEN 1 END) AS booked_count
     FROM class_sessions cs
     LEFT JOIN class_bookings b ON b.session_id = cs.id
     WHERE cs.section_id = ? AND cs.is_active = 1 AND cs.starts_at > NOW()
     GROUP BY cs.id
     ORDER BY cs.starts_at"
);
$sessionsStmt->execute([$sectionId]);
$sessions = $sessionsStmt->fetchAll();

if ($user && $sessions) {
    $bookingStmt = db()->prepare("SELECT session_id FROM class_bookings WHERE user_id = ? AND status = 'booked'");
    $bookingStmt->execute([$user['id']]);
    $bookingsBySession = array_fill_keys(array_map('intval', $bookingStmt->fetchAll(PDO::FETCH_COLUMN)), true);
}

$messages = [
    'joined' => ['success', 'You have joined the section. You can now book a class.'],
    'left' => ['success', 'You have left the section. Future class bookings were cancelled.'],
    'section_full' => ['error', 'This section is full.'],
    'already_member' => ['error', 'You are already a member of this section.'],
    'membership_required' => ['error', 'Join this section before booking a class.'],
    'booking_confirmed' => ['success', 'Your class booking is confirmed.'],
    'already_booked' => ['error', 'You already have a booking for this class.'],
    'session_full' => ['error', 'This class is full.'],
    'booking_cancelled' => ['success', 'Your class booking has been cancelled.'],
    'session_unavailable' => ['error', 'This class is no longer available for booking.'],
    'session_expired' => ['error', 'Your session has expired. Please try again.'],
    'invalid_request' => ['error', 'The request could not be completed. Please try again.'],
];
$notice = $messages[$_GET['status'] ?? ''] ?? null;
$confirmation = null;
if ($user && ($_GET['status'] ?? '') === 'booking_confirmed') {
    $confirmation = $_SESSION['booking_confirmation'] ?? null;
    if (!$confirmation || (int) $confirmation['section_id'] !== (int) $sectionId) {
        $confirmation = null;
    }
    unset($_SESSION['booking_confirmation']);
}

$pageTitle = $section['name'] . ' · KBTU culturist';
require __DIR__ . '/includes/header.php';
?>
        <main class="catalog-section">
            <div class="container">
                <a class="back-link" href="sections.php">← All sport sections</a>

                <section class="section-detail">
                    <div class="section-detail-main">
                        <p class="eyebrow">Sport section</p>
                        <h1><?= e($section['name']) ?></h1>
                        <p class="section-detail-description"><?= e($section['description']) ?></p>
                        <dl class="section-card-meta detail-meta">
                            <div><dt>Instructor</dt><dd><?= e($section['instructor']) ?></dd></div>
                            <div><dt>Location</dt><dd><?= e($section['location']) ?></dd></div>
                            <div><dt>Section places</dt><dd><?= max(0, (int) $section['capacity'] - (int) $section['active_members']) ?> available</dd></div>
                        </dl>
                    </div>

                    <?php if ($notice): ?>
                        <div class="form-alert <?= e($notice[0]) ?>" role="status"><?= e($notice[1]) ?></div>
                    <?php endif; ?>
                    <?php if ($confirmation): ?>
                        <div class="booking-confirmation" role="status">
                            <h2>Booking confirmed</h2>
                            <p>Booking ID: #<?= (int) $confirmation['booking_id'] ?></p>
                            <p>Class time: <?= e(date('D, d M Y · H:i', strtotime($confirmation['starts_at']))) ?></p>
                            <a class="text-link" href="profile.php#bookings">View my bookings</a>
                        </div>
                    <?php endif; ?>

                    <?php if (!$user): ?>
                        <div class="registration-prompt">
                            <p>Log in or create an account to join this section and book classes.</p>
                            <a class="btn btn-navy" href="login.php">Log in to continue</a>
                            <a class="text-link" href="register.php">Create an account</a>
                        </div>
                    <?php elseif ($isMember): ?>
                        <form action="enroll.php" method="post" class="inline-action">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="action" value="leave_section">
                            <input type="hidden" name="section_id" value="<?= (int) $section['id'] ?>">
                            <button type="submit" class="btn btn-outline-navy">Leave section</button>
                        </form>
                    <?php else: ?>
                        <form action="enroll.php" method="post" class="inline-action">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="action" value="join_section">
                            <input type="hidden" name="section_id" value="<?= (int) $section['id'] ?>">
                            <button type="submit" class="btn btn-navy" <?= (int) $section['active_members'] >= (int) $section['capacity'] ? 'disabled' : '' ?>>Join section</button>
                        </form>
                    <?php endif; ?>
                </section>

                <section class="schedule-section">
                    <div class="schedule-heading">
                        <div>
                            <p class="eyebrow">Upcoming activities</p>
                            <h2>Class schedule</h2>
                        </div>
                        <?php if (!$user): ?><p>Sign in to reserve a place.</p><?php endif; ?>
                    </div>

                    <?php if (!$sessions): ?>
                        <div class="empty-state">There are no upcoming classes for this section yet.</div>
                    <?php else: ?>
                        <div class="schedule-list">
                            <?php foreach ($sessions as $class): ?>
                                <?php
                                $classId = (int) $class['id'];
                                $placesLeft = max(0, (int) $class['capacity'] - (int) $class['booked_count']);
                                $isBooked = isset($bookingsBySession[$classId]);
                                ?>
                                <article class="schedule-card">
                                    <div>
                                        <time datetime="<?= e(date(DATE_ATOM, strtotime($class['starts_at']))) ?>">
                                            <?= e(date('D, d M Y · H:i', strtotime($class['starts_at']))) ?>
                                        </time>
                                        <p><?= $placesLeft ?> <?= $placesLeft === 1 ? 'place' : 'places' ?> available</p>
                                    </div>
                                    <?php if (!$user): ?>
                                        <a class="btn btn-outline-navy" href="login.php">Log in to book</a>
                                    <?php elseif (!$isMember): ?>
                                        <span class="booking-hint">Join the section to book</span>
                                    <?php elseif ($isBooked): ?>
                                        <form action="enroll.php" method="post">
                                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="action" value="cancel_class">
                                            <input type="hidden" name="section_id" value="<?= (int) $section['id'] ?>">
                                            <input type="hidden" name="session_id" value="<?= $classId ?>">
                                            <button type="submit" class="btn btn-outline-navy">Cancel booking</button>
                                        </form>
                                    <?php else: ?>
                                        <form action="enroll.php" method="post">
                                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="action" value="book_class">
                                            <input type="hidden" name="section_id" value="<?= (int) $section['id'] ?>">
                                            <input type="hidden" name="session_id" value="<?= $classId ?>">
                                            <button type="submit" class="btn btn-navy" <?= $placesLeft === 0 ? 'disabled' : '' ?>>Book class</button>
                                        </form>
                                    <?php endif; ?>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </main>
<?php require __DIR__ . '/includes/footer.php'; ?>
