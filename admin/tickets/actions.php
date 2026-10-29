<?php
include("../../includes/connections.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}
if (empty($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'super_admin') {
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

$stmt = $con->prepare("SELECT status FROM tickets WHERE ticket_id = ?");
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

header('Location: view.php?ticketID=' . $ticketId);
exit;
