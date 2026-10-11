<?php
require __DIR__ . '/includes/session.php';
require __DIR__ . '/includes/db.php';

$userId = (int) (current_user()['id'] ?? 0);
$stmt = db()->prepare(
    "SELECT s.*,
            COUNT(DISTINCT CASE WHEN m.status = 'active' THEN m.user_id END) AS active_members,
            MAX(CASE WHEN m.user_id = ? AND m.status = 'active' THEN 1 ELSE 0 END) AS has_membership
     FROM sports_sections s
     LEFT JOIN section_memberships m ON m.section_id = s.id
     WHERE s.is_active = 1
     GROUP BY s.id
     ORDER BY s.name"
);
$stmt->execute([$userId]);
$sections = $stmt->fetchAll();

$messages = [
    'joined' => ['success', 'You have joined the section. Choose a class from its schedule.'],
    'left' => ['success', 'You have left the section. Future class bookings were cancelled.'],
    'section_full' => ['error', 'This section is full.'],
    'already_member' => ['error', 'You are already a member of this section.'],
    'session_expired' => ['error', 'Your session has expired. Please try again.'],
    'invalid_request' => ['error', 'The request could not be completed. Please try again.'],
    'unavailable' => ['error', 'That section is no longer available.'],
];
$notice = $messages[$_GET['status'] ?? ''] ?? null;

$pageTitle = 'Sport sections · KBTU culturist';
require __DIR__ . '/includes/header.php';
?>
        <main class="catalog-section">
            <div class="container">
                <div class="catalog-heading">
                    <p class="eyebrow">Find your activity</p>
                    <h1 class="section-title">Sport sections</h1>
                    <p>Explore the available sections, check the details, and join the activities that fit your goals.</p>
                </div>

                <?php if ($notice): ?>
                    <div class="form-alert <?= e($notice[0]) ?>" role="status"><?= e($notice[1]) ?></div>
                <?php endif; ?>

                <?php if (!$sections): ?>
                    <div class="empty-state">There are no active sport sections yet.</div>
                <?php else: ?>
                    <div class="section-grid">
                        <?php foreach ($sections as $section): ?>
                            <article class="section-card">
                                <div class="section-card-body">
                                    <p class="section-card-label">KBTU Sports</p>
                                    <h2><?= e($section['name']) ?></h2>
                                    <p><?= e($section['description']) ?></p>
                                    <dl class="section-card-meta">
                                        <div><dt>Instructor</dt><dd><?= e($section['instructor']) ?></dd></div>
                                        <div><dt>Location</dt><dd><?= e($section['location']) ?></dd></div>
                                        <div><dt>Places</dt><dd><?= max(0, (int) $section['capacity'] - (int) $section['active_members']) ?> available</dd></div>
                                    </dl>
                                </div>
                                <a class="btn btn-navy section-card-link" href="section.php?id=<?= (int) $section['id'] ?>">
                                    <?= (int) $section['has_membership'] === 1 ? 'View schedule' : 'View details' ?>
                                </a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
<?php require __DIR__ . '/includes/footer.php'; ?>
