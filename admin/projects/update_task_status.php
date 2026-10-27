<?php
session_start();
include('../../includes/connections.php');

if (!isset($_POST['task_id'], $_POST['new_status'])) {
    http_response_code(400);
    exit('Task ID or new status not provided!');
}

$taskId = (int) $_POST['task_id'];
$newStatus = (string) $_POST['new_status'];
$userId = (int) ($_SESSION['user_id'] ?? 0);
$validStatuses = ['To do', 'In progress', 'For Checking', 'Completed', 'Cancelled'];

if ($taskId <= 0 || !in_array($newStatus, $validStatuses, true)) {
    http_response_code(400);
    exit('Invalid task ID or status.');
}
if ($userId <= 0) {
    http_response_code(403);
    exit('Forbidden');
}
if (!mysqli_begin_transaction($con)) {
    http_response_code(500);
    exit('Unable to update task status.');
}

$statusStmt = mysqli_prepare($con, 'SELECT status FROM projects_task WHERE task_id = ?');
if (!$statusStmt) {
    mysqli_rollback($con);
    http_response_code(500);
    exit('Unable to load task status.');
}
mysqli_stmt_bind_param($statusStmt, 'i', $taskId);
if (!mysqli_stmt_execute($statusStmt)) {
    mysqli_stmt_close($statusStmt);
    mysqli_rollback($con);
    http_response_code(500);
    exit('Unable to load task status.');
}
$statusResult = mysqli_stmt_get_result($statusStmt);
$task = $statusResult ? mysqli_fetch_assoc($statusResult) : null;
mysqli_stmt_close($statusStmt);
if (!$task) {
    mysqli_rollback($con);
    http_response_code(404);
    exit('Task not found.');
}

if ($newStatus !== $task['status']) {
    date_default_timezone_set('Asia/Manila');
    $description = 'Moved this card to ' . $newStatus;
    $dateStamp = date('m/d/Y h:i a');
    $commentStmt = mysqli_prepare($con, 'INSERT INTO task_comments (task_id, user_id, description, date_stamp) VALUES (?, ?, ?, ?)');
    if (!$commentStmt) {
        mysqli_rollback($con);
        http_response_code(500);
        exit('Unable to record task activity.');
    }
    mysqli_stmt_bind_param($commentStmt, 'iiss', $taskId, $userId, $description, $dateStamp);
    if (!mysqli_stmt_execute($commentStmt)) {
        mysqli_stmt_close($commentStmt);
        mysqli_rollback($con);
        http_response_code(500);
        exit('Unable to record task activity.');
    }
    mysqli_stmt_close($commentStmt);
}

$updateStmt = mysqli_prepare($con, 'UPDATE projects_task SET status = ? WHERE task_id = ?');
if (!$updateStmt) {
    mysqli_rollback($con);
    http_response_code(500);
    exit('Unable to update task status.');
}
mysqli_stmt_bind_param($updateStmt, 'si', $newStatus, $taskId);
$updated = mysqli_stmt_execute($updateStmt);
mysqli_stmt_close($updateStmt);
if (!$updated || !mysqli_commit($con)) {
    mysqli_rollback($con);
    http_response_code(500);
    exit('Unable to update task status.');
}

echo 'Task status updated successfully!';
?>
