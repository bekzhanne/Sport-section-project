<?php
// Included by every admin/*.php page. Expects $adminActive to be set to the current tab key.
$adminActive = $adminActive ?? '';
$adminTabs = [
    'dashboard' => ['index.php', 'Dashboard'],
    'sections'  => ['sections.php', 'Sections'],
    'users'     => ['users.php', 'Users'],
    'bookings'  => ['bookings.php', 'Bookings'],
];
?>
<nav class="admin-nav">
    <?php foreach ($adminTabs as $key => $tab): ?>
        <a href="<?= e($tab[0]) ?>" class="<?= $key === $adminActive ? 'is-active' : '' ?>"><?= e($tab[1]) ?></a>
    <?php endforeach; ?>
</nav>
