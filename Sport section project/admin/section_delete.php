<?php
require __DIR__ . '/../includes/session.php';
require __DIR__ . '/../includes/db.php';

require_admin();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !verify_csrf()) {
    redirect('sections.php?status=invalid_request');
}

$id = post_positive_int('id');
if (!$id) {
    redirect('sections.php?status=invalid_request');
}

$stmt = db()->prepare('DELETE FROM sports_sections WHERE id = ?');
$stmt->execute([$id]);

redirect('sections.php?status=' . ($stmt->rowCount() > 0 ? 'deleted' : 'not_found'));
