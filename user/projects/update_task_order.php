<?php
session_start();
include('../../includes/connections.php');

if(isset($_POST['taskId'], $_POST['newOrder'])) {
    $user_role = $_SESSION['role'] ?? '';
    $user_id = (int) ($_SESSION['user_id'] ?? 0);
    if (!isManagementRole((string) $user_role)) {
        http_response_code(403);
        exit('Forbidden');
    }
    $taskId = (int) $_POST['taskId'];
    $access_sql = "SELECT task_id, project_id FROM projects_task WHERE task_id = $taskId";
    $access = mysqli_query($con, $access_sql);
    $task = $access ? mysqli_fetch_assoc($access) : null;
    if (!$task || !canAccessProject($con, (int) $task['project_id'], (string) $user_role, $user_id)) {
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
