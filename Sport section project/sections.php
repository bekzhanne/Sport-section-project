<?php
require __DIR__ . '/includes/session.php';
require __DIR__ . '/includes/db.php';

$userId = (int) (current_user()['id'] ?? 0);
$query = trim((string) ($_GET['q'] ?? ''));
$availableOnly = isset($_GET['available']);

$sql = "SELECT s.*,
               COUNT(DISTINCT CASE WHEN m.status = 'active' THEN m.user_id END) AS active_members,
               MAX(CASE WHEN m.user_id = ? AND m.status = 'active' THEN 1 ELSE 0 END) AS has_membership
        FROM sports_sections s
        LEFT JOIN section_memberships m ON m.section_id = s.id
        WHERE s.is_active = 1";
$params = [$userId];

if ($query !== '') {
    $sql .= " AND (s.name LIKE ? OR s.instructor LIKE ? OR s.location LIKE ?)";
    $like = '%' . $query . '%';
    array_push($params, $like, $like, $like);
}

$sql .= " GROUP BY s.id ORDER BY s.name";

$stmt = db()->prepare($sql);
$stmt->execute($params);
$sections = $stmt->fetchAll();

if ($availableOnly) {
    $sections = array_values(array_filter(
        $sections,
        fn (array $section): bool => (int) $section['active_members'] < (int) $section['capacity']
    ));
}

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

                <form method="get" class="search-bar">
                    <input type="search" name="q" class="form-input" placeholder="Search by name, instructor, or location" value="<?= e($query) ?>">
                    <label class="search-checkbox">
                        <input type="checkbox" name="available" value="1" <?= $availableOnly ? 'checked' : '' ?>>
                        Available only
                    </label>
                    <button type="submit" class="btn btn-navy">Search</button>
                    <?php if ($query !== '' || $availableOnly): ?><a class="text-link" href="sections.php">Clear</a><?php endif; ?>
                </form>

                <?php if (!$sections): ?>
                    <div class="empty-state"><?= $query !== '' || $availableOnly ? 'No sections match your search.' : 'There are no active sport sections yet.' ?></div>
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
