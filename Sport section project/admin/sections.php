<?php
require __DIR__ . '/../includes/session.php';
require __DIR__ . '/../includes/db.php';

require_admin();

$stmt = db()->query(
    "SELECT s.*,
            COUNT(DISTINCT CASE WHEN m.status = 'active' THEN m.user_id END) AS active_members
     FROM sports_sections s
     LEFT JOIN section_memberships m ON m.section_id = s.id
     GROUP BY s.id
     ORDER BY s.name"
);
$sections = $stmt->fetchAll();

$messages = [
    'created'         => ['success', 'Section created.'],
    'updated'         => ['success', 'Section updated.'],
    'deleted'         => ['success', 'Section deleted.'],
    'invalid_request' => ['error', 'The request could not be completed. Please try again.'],
    'not_found'       => ['error', 'That section no longer exists.'],
];
$notice = $messages[$_GET['status'] ?? ''] ?? null;

$adminActive = 'sections';
$pageTitle = 'Manage sections · KBTU culturist';
require __DIR__ . '/../includes/header.php';
?>
        <main class="admin-section">
            <div class="container">
                <div class="catalog-heading">
                    <p class="eyebrow">Admin</p>
                    <h1 class="section-title">Sections</h1>
                </div>

                <?php require __DIR__ . '/_nav.php'; ?>

                <?php if ($notice): ?>
                    <div class="form-alert <?= e($notice[0]) ?>" role="status"><?= e($notice[1]) ?></div>
                <?php endif; ?>

                <div class="admin-card">
                    <div class="page-actions">
                        <h2>All sections</h2>
                        <a href="section_form.php" class="btn btn-navy">Add new section</a>
                    </div>

                    <?php if (!$sections): ?>
                        <div class="empty-state">No sections yet.</div>
                    <?php else: ?>
                        <div class="table-scroll">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Instructor</th>
                                        <th>Location</th>
                                        <th>Members</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sections as $section): ?>
                                        <tr>
                                            <td><?= e($section['name']) ?></td>
                                            <td><?= e($section['instructor']) ?></td>
                                            <td><?= e($section['location']) ?></td>
                                            <td><?= (int) $section['active_members'] ?> / <?= (int) $section['capacity'] ?></td>
                                            <td><span class="status-pill <?= $section['is_active'] ? 'is-active' : 'is-inactive' ?>"><?= $section['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                                            <td>
                                                <a class="text-link" href="sessions.php?section_id=<?= (int) $section['id'] ?>">Classes</a>
                                                &nbsp;·&nbsp;
                                                <a class="text-link" href="section_form.php?id=<?= (int) $section['id'] ?>">Edit</a>
                                                &nbsp;·&nbsp;
                                                <form action="section_delete.php" method="post" class="inline-delete" onsubmit="return confirm('Delete this section? This also removes its class sessions and bookings.');">
                                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                                    <input type="hidden" name="id" value="<?= (int) $section['id'] ?>">
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
            </div>
        </main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
