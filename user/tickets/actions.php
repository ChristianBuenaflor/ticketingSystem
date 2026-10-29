<?php
include("../../includes/connections.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

$userId = (int) ($_SESSION['user_id'] ?? 0);
$role = (string) ($_SESSION['role'] ?? '');
if (empty($_SESSION['logged_in']) || $userId < 1
    || !in_array($role, ['admin', 'super_admin', 'technician'], true)) {
    http_response_code(403);
    exit('Forbidden.');
}
if (!isset($_POST['csrf_token'], $_SESSION['csrf_token'])
    || !hash_equals($_SESSION['csrf_token'], (string) $_POST['csrf_token'])) {
    http_response_code(403);
    exit('Invalid security token.');
}

$ticketId = filter_var($_POST['ticketID'] ?? null, FILTER_VALIDATE_INT);
if (!$ticketId || $ticketId < 1) {
    http_response_code(400);
    exit('Invalid ticket ID.');
}

$stmt = $con->prepare("SELECT ticket_id, user_id, technician, title, description, status FROM tickets WHERE ticket_id = ?");
if (!$stmt) {
    http_response_code(500);
    exit('Unable to load ticket.');
}
$stmt->bind_param('i', $ticketId);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$ticket) {
    http_response_code(404);
    exit('Ticket not found.');
}
if ($role === 'technician' && (int) $ticket['technician'] !== $userId) {
    http_response_code(403);
    exit('Forbidden.');
}

$nextStatus = $ticket['status'] === 'Open'
    ? 'In progress'
    : ($ticket['status'] === 'In progress' ? 'Closed' : 'Open');
$lastActivity = date('m/d/Y h:i a');
$updateStmt = $con->prepare("UPDATE tickets SET status = ?, last_activity = ? WHERE ticket_id = ?");
if (!$updateStmt) {
    http_response_code(500);
    exit('Unable to update ticket.');
}
$updateStmt->bind_param('ssi', $nextStatus, $lastActivity, $ticketId);
if (!$updateStmt->execute()) {
    $updateStmt->close();
    http_response_code(500);
    exit('Unable to update ticket.');
}
$updateStmt->close();

$emailStmt = $con->prepare("SELECT email FROM users WHERE user_id = ?");
if (!$emailStmt) {
    error_log('Unable to prepare ticket status notification recipient lookup.');
} else {
    $ownerId = (int) $ticket['user_id'];
    $emailStmt->bind_param('i', $ownerId);
    $emailStmt->execute();
    $owner = $emailStmt->get_result()->fetch_assoc();
    $emailStmt->close();

    if ($owner && filter_var($owner['email'], FILTER_VALIDATE_EMAIL)) {
        $client_email = $owner['email'];
        $subject = 'Ticket Update';
        $actorStmt = $con->prepare("SELECT first_name, last_name FROM users_info WHERE user_id = ?");
        $actorName = $role === 'technician' ? 'Technician' : 'Administrator';
        if ($actorStmt) {
            $actorStmt->bind_param('i', $userId);
            $actorStmt->execute();
            $actor = $actorStmt->get_result()->fetch_assoc();
            $actorStmt->close();
            if ($actor) {
                $actorName = trim($actor['first_name'] . ' ' . $actor['last_name']);
            }
        }

        $safeActor = htmlspecialchars($actorName, ENT_QUOTES, 'UTF-8');
        $safeStatus = htmlspecialchars($nextStatus, ENT_QUOTES, 'UTF-8');
        $safeTitle = htmlspecialchars($ticket['title'], ENT_QUOTES, 'UTF-8');
        $safeDescription = nl2br(htmlspecialchars($ticket['description'], ENT_QUOTES, 'UTF-8'));
        $message = $safeActor . ' marked your ticket as "' . $safeStatus . '".<br><br>'
            . 'Ticket ID: ' . $ticketId . '<br>'
            . 'Ticket Title: ' . $safeTitle . '<br>'
            . 'Ticket Description: ' . $safeDescription . '<br>'
            . 'Status: ' . $safeStatus;
        include('send.php');
    }
}

header('Location: view.php?ticketID=' . $ticketId);
exit;
