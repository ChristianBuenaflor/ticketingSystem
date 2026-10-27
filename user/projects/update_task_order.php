<?php
session_start();
include('../../includes/connections.php');

if(isset($_POST['taskId'], $_POST['newOrder'])) {
    $user_role = $_SESSION['role'] ?? '';
    $user_id = (int) ($_SESSION['user_id'] ?? 0);
    if (!in_array($user_role, ['admin', 'super_admin', 'technician'], true)) {
        http_response_code(403);
        exit('Forbidden');
    }
    $taskId = (int) $_POST['taskId'];
    $access_sql = "SELECT task_id FROM projects_task WHERE task_id = $taskId";
    if (!in_array($user_role, ['admin', 'super_admin'], true)) {
        $access_sql .= " AND EXISTS (SELECT 1 FROM project_shares
            WHERE project_shares.project_id = projects_task.project_id
            AND project_shares.user_id = $user_id)";
    }
    $access = mysqli_query($con, $access_sql);
    if (!$access || mysqli_num_rows($access) === 0) {
        http_response_code(403);
        exit('Forbidden');
    }

    date_default_timezone_set('Asia/Manila');
    $newOrder = date('m-d-Y H:i:s');

    // Update task order in the database
    $query = "UPDATE projects_task SET orders = '$newOrder' WHERE task_id = '$taskId'";
    $result = mysqli_query($con, $query);

    if($result) {
        echo "Task order updated successfully!";
    } else {
        echo "Error updating task order!";
    }
} else {
    echo "Invalid request!";
}
?>
