<?php
require __DIR__ . '/includes/session.php';
require __DIR__ . '/includes/db.php';

require_login();

function enrollment_redirect(int $sectionId, string $status): void
{
    if ($sectionId > 0) {
        redirect('section.php?id=' . $sectionId . '&status=' . rawurlencode($status));
    }

    redirect('sections.php?status=' . rawurlencode($status));
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    redirect('sections.php');
}

$sectionId = post_positive_int('section_id');
if (!$sectionId) {
    enrollment_redirect(0, 'invalid_request');
}

if (!verify_csrf()) {
    enrollment_redirect((int) $sectionId, 'session_expired');
}

$action = $_POST['action'] ?? '';
$sessionId = post_positive_int('session_id');
$pdo = db();
$userId = (int) current_user()['id'];
$pdo->beginTransaction();

if ($action === 'join_section' || $action === 'leave_section') {
    $sectionStmt = $pdo->prepare('SELECT id, capacity FROM sports_sections WHERE id = ? AND is_active = 1 FOR UPDATE');
    $sectionStmt->execute([$sectionId]);
    $section = $sectionStmt->fetch();

    if (!$section) {
        $pdo->commit();
        enrollment_redirect((int) $sectionId, 'unavailable');
    }

    $membershipStmt = $pdo->prepare('SELECT id, status FROM section_memberships WHERE user_id = ? AND section_id = ? FOR UPDATE');
    $membershipStmt->execute([$userId, $sectionId]);
    $membership = $membershipStmt->fetch();

    if ($action === 'leave_section') {
        if (!$membership || $membership['status'] !== 'active') {
            $pdo->commit();
            enrollment_redirect((int) $sectionId, 'invalid_request');
        }

        $cancelFutureBookings = $pdo->prepare(
            "UPDATE class_bookings b
             INNER JOIN class_sessions cs ON cs.id = b.session_id
             SET b.status = 'cancelled'
             WHERE b.user_id = ? AND cs.section_id = ? AND cs.starts_at > NOW() AND b.status = 'booked'"
        );
        $cancelFutureBookings->execute([$userId, $sectionId]);
        $leaveStmt = $pdo->prepare("UPDATE section_memberships SET status = 'cancelled' WHERE id = ?");
        $leaveStmt->execute([$membership['id']]);
        $pdo->commit();
        enrollment_redirect((int) $sectionId, 'left');
    }

    if ($membership && $membership['status'] === 'active') {
        $pdo->commit();
        enrollment_redirect((int) $sectionId, 'already_member');
    }

    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM section_memberships WHERE section_id = ? AND status = 'active'");
    $countStmt->execute([$sectionId]);
    if ((int) $countStmt->fetchColumn() >= (int) $section['capacity']) {
        $pdo->commit();
        enrollment_redirect((int) $sectionId, 'section_full');
    }

    if ($membership) {
        $joinStmt = $pdo->prepare("UPDATE section_memberships SET status = 'active', joined_at = CURRENT_TIMESTAMP WHERE id = ?");
        $joinStmt->execute([$membership['id']]);
    } else {
        $joinStmt = $pdo->prepare("INSERT INTO section_memberships (user_id, section_id) VALUES (?, ?)");
        $joinStmt->execute([$userId, $sectionId]);
    }

    $pdo->commit();
    enrollment_redirect((int) $sectionId, 'joined');
}

if ($action === 'book_class' || $action === 'cancel_class') {
    if (!$sessionId) {
        $pdo->commit();
        enrollment_redirect((int) $sectionId, 'invalid_request');
    }

    $sectionStmt = $pdo->prepare('SELECT id FROM sports_sections WHERE id = ? AND is_active = 1 FOR UPDATE');
    $sectionStmt->execute([$sectionId]);
    if (!$sectionStmt->fetch()) {
        $pdo->commit();
        enrollment_redirect((int) $sectionId, 'unavailable');
    }

    $classStmt = $pdo->prepare(
        'SELECT id, section_id, starts_at, capacity
         FROM class_sessions
         WHERE id = ? AND section_id = ? AND is_active = 1 AND starts_at > NOW()
         FOR UPDATE'
    );
    $classStmt->execute([$sessionId, $sectionId]);
    $class = $classStmt->fetch();

    if (!$class) {
        $pdo->commit();
        enrollment_redirect((int) $sectionId, 'session_unavailable');
    }

    $membershipStmt = $pdo->prepare("SELECT id FROM section_memberships WHERE user_id = ? AND section_id = ? AND status = 'active' FOR UPDATE");
    $membershipStmt->execute([$userId, $sectionId]);
    if (!$membershipStmt->fetch()) {
        $pdo->commit();
        enrollment_redirect((int) $sectionId, 'membership_required');
    }

    $bookingStmt = $pdo->prepare('SELECT id, status FROM class_bookings WHERE user_id = ? AND session_id = ? FOR UPDATE');
    $bookingStmt->execute([$userId, $sessionId]);
    $booking = $bookingStmt->fetch();

    if ($action === 'cancel_class') {
        if (!$booking || $booking['status'] !== 'booked') {
            $pdo->commit();
            enrollment_redirect((int) $sectionId, 'invalid_request');
        }

        $cancelStmt = $pdo->prepare("UPDATE class_bookings SET status = 'cancelled' WHERE id = ?");
        $cancelStmt->execute([$booking['id']]);
        $pdo->commit();
        enrollment_redirect((int) $sectionId, 'booking_cancelled');
    }

    if ($booking && $booking['status'] === 'booked') {
        $pdo->commit();
        enrollment_redirect((int) $sectionId, 'already_booked');
    }

    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM class_bookings WHERE session_id = ? AND status = 'booked'");
    $countStmt->execute([$sessionId]);
    if ((int) $countStmt->fetchColumn() >= (int) $class['capacity']) {
        $pdo->commit();
        enrollment_redirect((int) $sectionId, 'session_full');
    }

    if ($booking) {
        $bookStmt = $pdo->prepare("UPDATE class_bookings SET status = 'booked', booked_at = CURRENT_TIMESTAMP WHERE id = ?");
        $bookStmt->execute([$booking['id']]);
        $bookingId = (int) $booking['id'];
    } else {
        $bookStmt = $pdo->prepare("INSERT INTO class_bookings (user_id, session_id) VALUES (?, ?)");
        $bookStmt->execute([$userId, $sessionId]);
        $bookingId = (int) $pdo->lastInsertId();
    }

    $pdo->commit();
    $_SESSION['booking_confirmation'] = [
        'booking_id' => $bookingId,
        'section_id' => (int) $sectionId,
        'starts_at' => $class['starts_at'],
    ];
    enrollment_redirect((int) $sectionId, 'booking_confirmed');
}

$pdo->commit();
enrollment_redirect((int) $sectionId, 'invalid_request');
