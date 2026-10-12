<?php
require __DIR__ . '/../includes/session.php';
require __DIR__ . '/../includes/db.php';

require_admin();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !verify_csrf()) {
    redirect('bookings.php?status=invalid_request');
}

$id = post_positive_int('id');
$type = $_POST['type'] ?? '';

if (!$id || !in_array($type, ['membership', 'booking'], true)) {
    redirect('bookings.php?status=invalid_request');
}

if ($type === 'membership') {
    $stmt = db()->prepare("UPDATE section_memberships SET status = 'cancelled' WHERE id = ?");
} else {
    $stmt = db()->prepare("UPDATE class_bookings SET status = 'cancelled' WHERE id = ?");
}
$stmt->execute([$id]);

redirect('bookings.php?status=cancelled');
