<?php
include('../../includes/connections.php');

if(isset($_POST['taskId'], $_POST['newOrder'])) {
    $taskId = $_POST['taskId'];

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
