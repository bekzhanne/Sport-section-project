<?php
require __DIR__ . '/../includes/session.php';
require __DIR__ . '/../includes/db.php';

require_admin();

$sectionId = post_positive_int('section_id');
if (!$sectionId) {
    redirect('sections.php?status=invalid_request');
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !verify_csrf()) {
    redirect('sessions.php?section_id=' . $sectionId . '&status=invalid_request');
}

$id = post_positive_int('id');
if (!$id) {
    redirect('sessions.php?section_id=' . $sectionId . '&status=invalid_request');
}

$stmt = db()->prepare('DELETE FROM class_sessions WHERE id = ? AND section_id = ?');
$stmt->execute([$id, $sectionId]);

redirect('sessions.php?section_id=' . $sectionId . '&status=deleted');
