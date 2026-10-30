<?php
include('../../includes/connections.php');
session_start();

if (empty($_SESSION['logged_in']) || !isManagementRole((string) ($_SESSION['role'] ?? ''))) {
    http_response_code(403);
    exit('Forbidden');
}

if(isset($_POST['taskId'], $_POST['newOrder'])) {
    $taskId = (int) $_POST['taskId'];
    $taskResult = mysqli_query($con, "SELECT project_id FROM projects_task WHERE task_id = $taskId");
    $task = $taskResult ? mysqli_fetch_assoc($taskResult) : null;
    if (!$task || !canAccessProject(
        $con,
        (int) $task['project_id'],
        (string) ($_SESSION['role'] ?? ''),
        (int) ($_SESSION['user_id'] ?? 0)
    )) {
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
