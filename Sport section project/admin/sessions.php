<?php
require __DIR__ . '/../includes/session.php';
require __DIR__ . '/../includes/db.php';

require_admin();

$sectionId = filter_input(INPUT_GET, 'section_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$sectionId && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $sectionId = post_positive_int('section_id');
}
if (!$sectionId) {
    redirect('sections.php?status=not_found');
}

$sectionStmt = db()->prepare('SELECT id, name FROM sports_sections WHERE id = ?');
$sectionStmt->execute([$sectionId]);
$section = $sectionStmt->fetch();
if (!$section) {
    redirect('sections.php?status=not_found');
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!verify_csrf()) {
        redirect('sessions.php?section_id=' . $sectionId . '&status=invalid_request');
    }

    $startsAt = post_string('starts_at');
    $capacity = filter_var($_POST['capacity'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 500]]);
    $startsAtTime = DateTime::createFromFormat('Y-m-d\TH:i', $startsAt);

    if (!$startsAtTime || $capacity === false) {
        redirect('sessions.php?section_id=' . $sectionId . '&status=invalid_input');
    }

    $stmt = db()->prepare('INSERT INTO class_sessions (section_id, starts_at, capacity) VALUES (?, ?, ?)');
    try {
        $stmt->execute([$sectionId, $startsAtTime->format('Y-m-d H:i:s'), $capacity]);
        redirect('sessions.php?section_id=' . $sectionId . '&status=added');
    } catch (PDOException $ex) {
        if ($ex->getCode() !== '23000') {
            throw $ex;
        }
        redirect('sessions.php?section_id=' . $sectionId . '&status=duplicate');
    }
}

$sessionsStmt = db()->prepare(
    "SELECT cs.id, cs.starts_at, cs.capacity, cs.is_active,
            COUNT(CASE WHEN b.status = 'booked' THEN 1 END) AS booked_count
     FROM class_sessions cs
     LEFT JOIN class_bookings b ON b.session_id = cs.id
     WHERE cs.section_id = ?
     GROUP BY cs.id
     ORDER BY cs.starts_at DESC"
);
$sessionsStmt->execute([$sectionId]);
$sessions = $sessionsStmt->fetchAll();

$messages = [
    'added'            => ['success', 'Class session added.'],
    'deleted'          => ['success', 'Class session deleted.'],
    'invalid_request'  => ['error', 'The request could not be completed. Please try again.'],
    'invalid_input'    => ['error', 'Please provide a valid date/time and capacity.'],
    'duplicate'        => ['error', 'This section already has a class at that time.'],
];
$notice = $messages[$_GET['status'] ?? ''] ?? null;

$adminActive = 'sections';
$pageTitle = 'Classes · ' . $section['name'] . ' · KBTU culturist';
require __DIR__ . '/../includes/header.php';
?>
        <main class="admin-section">
            <div class="container">
                <a class="back-link" href="sections.php">← All sections</a>

                <div class="catalog-heading">
                    <p class="eyebrow">Admin</p>
                    <h1 class="section-title"><?= e($section['name']) ?> — classes</h1>
                </div>

                <?php require __DIR__ . '/_nav.php'; ?>

                <?php if ($notice): ?>
                    <div class="form-alert <?= e($notice[0]) ?>" role="status"><?= e($notice[1]) ?></div>
                <?php endif; ?>

                <div class="admin-card">
                    <h2>Scheduled classes</h2>
                    <?php if (!$sessions): ?>
                        <div class="empty-state">No class sessions yet.</div>
                    <?php else: ?>
                        <div class="table-scroll">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Date &amp; time</th>
                                        <th>Booked</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sessions as $class): ?>
                                        <tr>
                                            <td><?= e(date('D, d M Y · H:i', strtotime($class['starts_at']))) ?></td>
                                            <td><?= (int) $class['booked_count'] ?> / <?= (int) $class['capacity'] ?></td>
                                            <td><span class="status-pill <?= strtotime($class['starts_at']) > time() ? 'is-active' : 'is-inactive' ?>"><?= strtotime($class['starts_at']) > time() ? 'Upcoming' : 'Past' ?></span></td>
                                            <td>
                                                <form action="session_delete.php" method="post" class="inline-delete" onsubmit="return confirm('Delete this class session?');">
                                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                                    <input type="hidden" name="id" value="<?= (int) $class['id'] ?>">
                                                    <input type="hidden" name="section_id" value="<?= (int) $sectionId ?>">
                                                    <button type="submit" class="link-button">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="admin-card">
                    <h2>Add a class</h2>
                    <form method="post" class="form-grid">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="section_id" value="<?= (int) $sectionId ?>">

                        <label class="form-group">
                            <span class="form-label">Date &amp; time</span>
                            <input type="datetime-local" name="starts_at" class="form-input" required>
                        </label>

                        <label class="form-group">
                            <span class="form-label">Capacity</span>
                            <input type="number" name="capacity" class="form-input" value="20" min="1" max="500" required>
                        </label>

                        <div class="form-group span-2">
                            <button type="submit" class="btn btn-navy">Add class</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
