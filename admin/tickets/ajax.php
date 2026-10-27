<?php
include("../../includes/connections.php");



if (isset($_GET['updateid'])) {
    $id = $_GET['updateid'];
    $sql = "SELECT * FROM tickets WHERE ticket_id = $id";
    $result = mysqli_query($con, $sql);
    $row = mysqli_fetch_assoc($result);
    if ($row) {
      
        $res = [
            'status' => 200,
            'data' => $row,
        ];
        echo json_encode($res);
        return false;
    }
  }
  











if (isset($_POST['post-ticket'])) {



  $m_ticketId =  mysqli_real_escape_string($con, $_POST['m_ticketId']);
    $user_id =  mysqli_real_escape_string($con, $_POST['user_id']);
$title =  mysqli_real_escape_string($con, $_POST['ticket_title']);
$project_id = mysqli_real_escape_string($con, $_POST['project']);
$ticket_type_id = mysqli_real_escape_string($con, $_POST['ticket_type']);
$description = mysqli_real_escape_string($con, $_POST['ticket_description']);
$status = mysqli_real_escape_string($con, $_POST['ticket_status']);

if($project_id == ''){
    $project_id = 0;
}

if($title == ''){
    $res = [
        'status' => 101,
    ];
    echo json_encode($res);
    return;
}

if($status == ''){
    $res = [
        'status' => 103,
    ];
    echo json_encode($res);
    return;
}







if($m_ticketId != ''){
    
date_default_timezone_set('Asia/Manila');
$last_activity = date("m/d/Y h:i a");
    $sqlf = "UPDATE tickets SET title='$title', project_id=$project_id, type_id='$ticket_type_id', description='$description', status='$status', last_activity='$last_activity' WHERE ticket_id = '$m_ticketId'";
    $resultf = mysqli_query($con, $sqlf);

    if ($resultf) {
     
      $res = [
        'status' => 200,
        'id' => $m_ticketId,
      ];
      echo json_encode($res);
      return;
    } else {
      echo "error";
    }
    

  }
  else{








    $uploadedFiles = array();

    if (!empty($_FILES['ticket_files']['tmp_name'][0])) {
        $fileCount = count($_FILES['ticket_files']['tmp_name']);
    
        for ($i = 0; $i < $fileCount; $i++) {
            $file_name = $_FILES['ticket_files']['name'][$i];
            $file_type = $_FILES['ticket_files']['type'][$i];
            $file_tmp_name = $_FILES['ticket_files']['tmp_name'][$i];
    
            // Move the file to the 'files' folder
            $target_path = '../../files/ticket/' . $file_name;
            if (move_uploaded_file($file_tmp_name, $target_path)) {
                // Insert file name and file type into the database
                $query = "INSERT INTO files (file_name, file_type) 
                          VALUES ('$file_name', '$file_type')";
                $result = mysqli_query($con, $query);
                if ($result) {
                    $uploadedFiles[] = mysqli_insert_id($con); // Store the ID of the inserted file
                } else {
                    echo "Error inserting file into database: " . mysqli_error($con);
                    exit;
                }
            } else {
                echo "Error moving uploaded file to destination folder.";
                exit;
            }
        }
    }

date_default_timezone_set('Asia/Manila');
$createdDate = date("m/d/Y h:i a");


if (!empty($uploadedFiles)) {
    $fileIdsString = implode(',', $uploadedFiles); 
    $query = "INSERT INTO tickets (user_id, title, project_id, type_id, description, status, files, created) 
              VALUES ('$user_id', '$title', '$project_id', '$ticket_type_id', '$description', '$status', '$fileIdsString', '$createdDate')";
} else {
    $query = "INSERT INTO tickets (user_id, title, project_id, type_id, description, created, status) 
              VALUES ('$user_id', '$title', '$project_id', '$ticket_type_id', '$description', '$createdDate', '$status')";
}

$result = mysqli_query($con, $query);

if ($result) {
    $last_id = $con->insert_id;
   
    $res = [
        'status' => 100,
        'id' => sprintf("%04d", $last_id),
    ];
    echo json_encode($res);
    return;
} else {
    echo "error";
}









  }











mysqli_close($con);
}





































if (isset($_POST['post-ticket-comment'])) {

    $comment_user_id =  mysqli_real_escape_string($con, $_POST['users_id']);
$comment_ticket_id =  mysqli_real_escape_string($con, $_POST['comment_ticket_id']);
$comment_textarea = mysqli_real_escape_string($con, $_POST['comment_textarea']);


if($comment_textarea == '' && empty($_FILES['comment_files']['tmp_name'][0])){
    $res = [
        'status' => 101,
    ];
    echo json_encode($res);
    return;
}

$uploadedFiles = array();

if (!empty($_FILES['comment_files']['tmp_name'][0])) {
    $fileCount = count($_FILES['comment_files']['tmp_name']);

    for ($i = 0; $i < $fileCount; $i++) {
        $file_name = $_FILES['comment_files']['name'][$i];
        $file_type = $_FILES['comment_files']['type'][$i];
        $file_tmp_name = $_FILES['comment_files']['tmp_name'][$i];

        // Move the file to the 'files' folder
        $target_path = '../../files/ticket/' . $file_name;
        if (move_uploaded_file($file_tmp_name, $target_path)) {
            // Insert file name and file type into the database
            $query = "INSERT INTO files (file_name, file_type) 
                      VALUES ('$file_name', '$file_type')";
            $result = mysqli_query($con, $query);
            if ($result) {
                $uploadedFiles[] = mysqli_insert_id($con); // Store the ID of the inserted file
            } else {
                echo "Error inserting file into database: " . mysqli_error($con);
                exit;
            }
        } else {
            echo "Error moving uploaded file to destination folder.";
            exit;
        }
    }
}

date_default_timezone_set('Asia/Manila');
$date_stamp = date("m/d/Y h:i a");


if (!empty($uploadedFiles)) {
    $fileIdsString = implode(',', $uploadedFiles); 
    $query = "INSERT INTO ticket_comments (ticket_id, user_id, description, files, date_stamp) 
              VALUES ('$comment_ticket_id', '$comment_user_id', '$comment_textarea', '$fileIdsString', '$date_stamp')";
} else {
    $query = "INSERT INTO ticket_comments (ticket_id, user_id, description, date_stamp) 
              VALUES ('$comment_ticket_id', '$comment_user_id', '$comment_textarea', '$date_stamp')";
}

$result = mysqli_query($con, $query);


$last_activity = date("m/d/Y h:i a");
$sql = "update `tickets` set last_activity='$last_activity' where ticket_id='$comment_ticket_id'";
            $updateresult = mysqli_query($con, $sql);



if ($result) {
   
    $res = [
        'status' => 100,
    ];
    echo json_encode($res);
    return;
} else {
    echo "error";
}


mysqli_close($con);
}










?>

