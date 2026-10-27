<?php
session_start();
include('../../includes/connections.php');

if (isset($_POST['task_id'], $_POST['new_status'])) {
    $user_role = $_SESSION['role'] ?? '';
    $user_id = (int) ($_SESSION['user_id'] ?? 0);
    if (!in_array($user_role, ['admin', 'super_admin', 'technician'], true)) {
        http_response_code(403);
        exit('Forbidden');
    }

    $taskId = (int) $_POST['task_id'];
    $newStatus = mysqli_real_escape_string($con, $_POST['new_status']);
    if (!in_array($newStatus, ['To do', 'In progress', 'For Checking', 'Completed', 'Cancelled'], true)) {
        http_response_code(400);
        exit('Invalid status');
    }

    $access_sql = "SELECT status FROM projects_task WHERE task_id = $taskId";
    if (!in_array($user_role, ['admin', 'super_admin'], true)) {
        $access_sql .= " AND EXISTS (SELECT 1 FROM project_shares
            WHERE project_shares.project_id = projects_task.project_id
            AND project_shares.user_id = $user_id)";
    }
    $access = mysqli_query($con, $access_sql);
    $task = $access ? mysqli_fetch_assoc($access) : null;
    if (!$task) {
        http_response_code(403);
        exit('Forbidden');
    }

    if ($newStatus !== $task['status']) {
        $description = 'Moved this card to ' . $newStatus;
        date_default_timezone_set('Asia/Manila');
        $date_stamp = date("m/d/Y h:i a");
        $queryCom = "INSERT INTO task_comments (task_id, user_id, description, date_stamp)
            VALUES ('$taskId', '$user_id', '$description', '$date_stamp')";
        mysqli_query($con, $queryCom);
    }

    $query = "UPDATE projects_task SET status = '$newStatus' WHERE task_id = '$taskId'";
    if (mysqli_query($con, $query)) {
        echo "Task status updated successfully!";
    } else {
        http_response_code(500);
        echo "Error updating task status!";
    }
} else {
    http_response_code(400);
    echo "Task ID or new status not provided!";
}
