<?php
require __DIR__ . '/../includes/session.php';
require __DIR__ . '/../includes/db.php';

require_admin();

$membershipsStmt = db()->query(
    "SELECT m.id, u.name AS user_name, u.email, s.name AS section_name, m.joined_at
     FROM section_memberships m
     INNER JOIN users u ON u.id = m.user_id
     INNER JOIN sports_sections s ON s.id = m.section_id
     WHERE m.status = 'active'
     ORDER BY m.joined_at DESC
     LIMIT 100"
);
$memberships = $membershipsStmt->fetchAll();

$bookingsStmt = db()->query(
    "SELECT b.id, b.status, u.name AS user_name, u.email, s.name AS section_name, cs.starts_at
     FROM class_bookings b
     INNER JOIN users u ON u.id = b.user_id
     INNER JOIN class_sessions cs ON cs.id = b.session_id
     INNER JOIN sports_sections s ON s.id = cs.section_id
     ORDER BY cs.starts_at DESC
     LIMIT 100"
);
$bookings = $bookingsStmt->fetchAll();

$messages = [
    'cancelled'        => ['success', 'Cancelled.'],
    'invalid_request'  => ['error', 'The request could not be completed. Please try again.'],
];
$notice = $messages[$_GET['status'] ?? ''] ?? null;

$adminActive = 'bookings';
$pageTitle = 'Bookings · KBTU culturist';
require __DIR__ . '/../includes/header.php';
?>
        <main class="admin-section">
            <div class="container">
                <div class="catalog-heading">
                    <p class="eyebrow">Admin</p>
                    <h1 class="section-title">Bookings</h1>
                </div>

                <?php require __DIR__ . '/_nav.php'; ?>

                <?php if ($notice): ?>
                    <div class="form-alert <?= e($notice[0]) ?>" role="status"><?= e($notice[1]) ?></div>
                <?php endif; ?>

                <div class="admin-card">
                    <h2>Section memberships</h2>
                    <?php if (!$memberships): ?>
                        <div class="empty-state">No active memberships.</div>
                    <?php else: ?>
                        <div class="table-scroll">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Section</th>
                                        <th>Joined</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($memberships as $m): ?>
                                        <tr>
                                            <td><?= e($m['user_name']) ?> <span class="form-optional">(<?= e($m['email']) ?>)</span></td>
                                            <td><?= e($m['section_name']) ?></td>
                                            <td><?= e(date('d.m.Y', strtotime($m['joined_at']))) ?></td>
                                            <td>
                                                <form action="booking_cancel.php" method="post" class="inline-delete">
                                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                                    <input type="hidden" name="type" value="membership">
                                                    <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                                                    <button type="submit" class="link-button">Remove</button>
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
                    <h2>Class bookings</h2>
                    <?php if (!$bookings): ?>
                        <div class="empty-state">No class bookings yet.</div>
                    <?php else: ?>
                        <div class="table-scroll">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Section</th>
                                        <th>Class time</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bookings as $b): ?>
                                        <tr>
                                            <td><?= e($b['user_name']) ?> <span class="form-optional">(<?= e($b['email']) ?>)</span></td>
                                            <td><?= e($b['section_name']) ?></td>
                                            <td><?= e(date('D, d M Y · H:i', strtotime($b['starts_at']))) ?></td>
                                            <td><span class="status-pill <?= $b['status'] === 'booked' ? 'is-booked' : 'is-cancelled' ?>"><?= $b['status'] === 'booked' ? 'Confirmed' : 'Cancelled' ?></span></td>
                                            <td>
                                                <?php if ($b['status'] === 'booked'): ?>
                                                    <form action="booking_cancel.php" method="post" class="inline-delete">
                                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                                        <input type="hidden" name="type" value="booking">
                                                        <input type="hidden" name="id" value="<?= (int) $b['id'] ?>">
                                                        <button type="submit" class="link-button">Cancel</button>
                                                    </form>
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
        </main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
