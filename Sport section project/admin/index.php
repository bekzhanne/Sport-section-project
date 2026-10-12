<?php
require __DIR__ . '/../includes/session.php';
require __DIR__ . '/../includes/db.php';

require_admin();

$pdo = db();
$stats = [
    'Users'               => (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'Active sections'     => (int) $pdo->query('SELECT COUNT(*) FROM sports_sections WHERE is_active = 1')->fetchColumn(),
    'Upcoming classes'    => (int) $pdo->query('SELECT COUNT(*) FROM class_sessions WHERE is_active = 1 AND starts_at > NOW()')->fetchColumn(),
    'Active memberships'  => (int) $pdo->query("SELECT COUNT(*) FROM section_memberships WHERE status = 'active'")->fetchColumn(),
    'Confirmed bookings'  => (int) $pdo->query("SELECT COUNT(*) FROM class_bookings WHERE status = 'booked'")->fetchColumn(),
];

$adminActive = 'dashboard';
$pageTitle = 'Admin dashboard · KBTU culturist';
require __DIR__ . '/../includes/header.php';
?>
        <main class="admin-section">
            <div class="container">
                <div class="catalog-heading">
                    <p class="eyebrow">Admin</p>
                    <h1 class="section-title">Dashboard</h1>
                </div>

                <?php require __DIR__ . '/_nav.php'; ?>

                <div class="admin-stats">
                    <?php foreach ($stats as $label => $value): ?>
                        <div class="admin-stat">
                            <strong><?= $value ?></strong>
                            <span><?= e($label) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="admin-card">
                    <h2>Quick links</h2>
                    <p><a class="text-link" href="section_form.php">Add a new sport section</a></p>
                    <p><a class="text-link" href="bookings.php">Review latest bookings</a></p>
                </div>
            </div>
        </main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
