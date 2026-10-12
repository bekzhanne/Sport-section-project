<?php
require __DIR__ . '/../includes/session.php';
require __DIR__ . '/../includes/db.php';

require_admin();

$stmt = db()->query(
    "SELECT u.id, u.name, u.email, u.phone, u.is_admin, u.created_at,
            COUNT(DISTINCT CASE WHEN m.status = 'active' THEN m.id END) AS active_sections
     FROM users u
     LEFT JOIN section_memberships m ON m.user_id = u.id
     GROUP BY u.id
     ORDER BY u.created_at DESC"
);
$users = $stmt->fetchAll();

$adminActive = 'users';
$pageTitle = 'Users · KBTU culturist';
require __DIR__ . '/../includes/header.php';
?>
        <main class="admin-section">
            <div class="container">
                <div class="catalog-heading">
                    <p class="eyebrow">Admin</p>
                    <h1 class="section-title">Users</h1>
                </div>

                <?php require __DIR__ . '/_nav.php'; ?>

                <div class="admin-card">
                    <h2>All users (<?= count($users) ?>)</h2>
                    <?php if (!$users): ?>
                        <div class="empty-state">No registered users yet.</div>
                    <?php else: ?>
                        <div class="table-scroll">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Active sections</th>
                                        <th>Joined</th>
                                        <th>Role</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $u): ?>
                                        <tr>
                                            <td><?= e($u['name']) ?></td>
                                            <td><?= e($u['email']) ?></td>
                                            <td><?= e($u['phone'] ?: '—') ?></td>
                                            <td><?= (int) $u['active_sections'] ?></td>
                                            <td><?= e(date('d.m.Y', strtotime($u['created_at']))) ?></td>
                                            <td><?= $u['is_admin'] ? '<span class="status-pill is-admin">Admin</span>' : '—' ?></td>
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
