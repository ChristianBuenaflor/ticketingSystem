<?php
include("../../includes/connections.php");

if (isset($_POST['post-project-comment'])) {

  $comment_user_id =  mysqli_real_escape_string($con, $_POST['project_comment_users_id']);
  $project_id =  mysqli_real_escape_string($con, $_POST['project_comment_project_id']);
  $comment_textarea = mysqli_real_escape_string($con, $_POST['project_comment_textarea']);
  
  
  if($comment_textarea == '' && empty($_FILES['project_comment_files']['tmp_name'][0])){
    $res = [
        'status' => 101,
    ];
    echo json_encode($res);
    return;
  }
  
  $uploadedFiles = array();
  
  if (!empty($_FILES['project_comment_files']['tmp_name'][0])) {
    $fileCount = count($_FILES['project_comment_files']['tmp_name']);
  
    for ($i = 0; $i < $fileCount; $i++) {
        $file_name = $_FILES['project_comment_files']['name'][$i];
        $file_type = $_FILES['project_comment_files']['type'][$i];
        $file_tmp_name = $_FILES['project_comment_files']['tmp_name'][$i];
  
        // Move the file to the 'files' folder
        $target_path = '../../files/project/' . $file_name;
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
    $query = "INSERT INTO project_comments (project_id, user_id, description, files, date_stamp) 
              VALUES ('$project_id', '$comment_user_id', '$comment_textarea', '$fileIdsString', '$date_stamp')";
  } else {
    $query = "INSERT INTO project_comments (project_id, user_id, description, date_stamp) 
              VALUES ('$project_id', '$comment_user_id', '$comment_textarea', '$date_stamp')";
  }
  
  $result = mysqli_query($con, $query);
  
  
  
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


















if (isset($_POST['add-files'])) {

  $project_id =  mysqli_real_escape_string($con, $_POST['project_id']);

  if(empty($_FILES['file_files']['tmp_name'][0])){
    $res = [
        'status' => 101,
    ];
    echo json_encode($res);
    return;
  }
  
  $uploadedFiles = array();
  
  function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    // Calculate bytes in the appropriate unit
    $bytes /= (1 << (10 * $pow));

    return round($bytes, $precision) . ' ' . $units[$pow];
}


  if (!empty($_FILES['file_files']['tmp_name'][0])) {
    $fileCount = count($_FILES['file_files']['tmp_name']);

    for ($i = 0; $i < $fileCount; $i++) {
        $file_name = $_FILES['file_files']['name'][$i];
        $file_type = $_FILES['file_files']['type'][$i];
        $file_tmp_name = $_FILES['file_files']['tmp_name'][$i];

        $file_size = filesize($file_tmp_name);
        $file_sizes = formatBytes($file_size);
        // Move the file to the 'files' folder
        $target_path = '../../files/project/' . $file_name;
        if (move_uploaded_file($file_tmp_name, $target_path)) {
            // Insert file name and file type into the database
            $query = "INSERT INTO files (file_name, file_type, file_size) 
                      VALUES ('$file_name', '$file_type', '$file_sizes')";
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

$newfilesadded = implode(',', $uploadedFiles); 
$qpfiles = "SELECT * FROM projects WHERE project_id = $project_id";
$rpfiles = mysqli_query($con, $qpfiles);

if (mysqli_num_rows($rpfiles) > 0) {
  while ($rowpfiles = $rpfiles->fetch_assoc()) {
    if(!empty($rowpfiles['files'])){
      $newfiles = $rowpfiles['files'].','.$newfilesadded;
    }
    else{
      $newfiles = $newfilesadded;
    }
  }
}


$sql = "UPDATE projects SET files = '$newfiles'  WHERE project_id = '$project_id'";
$result = mysqli_query($con, $sql);



$res = [
  'status' => 100,
];
echo json_encode($res);
return;






  
}




if (isset($_POST['post-task-comment'])) {

  $comment_user_id =  mysqli_real_escape_string($con, $_POST['users_id']);
  $comment_task_id =  mysqli_real_escape_string($con, $_POST['comment_task_id']);
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
        $target_path = '../../files/task/' . $file_name;
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
    $query = "INSERT INTO task_comments (task_id, user_id, description, files, date_stamp) 
              VALUES ('$comment_task_id', '$comment_user_id', '$comment_textarea', '$fileIdsString', '$date_stamp')";
  } else {
    $query = "INSERT INTO task_comments (task_id, user_id, description, date_stamp) 
              VALUES ('$comment_task_id', '$comment_user_id', '$comment_textarea', '$date_stamp')";
  }
  
  $result = mysqli_query($con, $query);
  
  
  
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
  
  







if (isset($_GET['deletetaskid'])) {
  $id = $_GET['deletetaskid'];

  $sql = "SELECT * FROM projects_task WHERE task_id = $id";
  $result = mysqli_query($con, $sql);
  $row = mysqli_fetch_assoc($result);
  if ($row) {
    $pId = 'Task Info #' . sprintf("%04d", $row['task_id']);
    $start_date = $row['start_date'];
    $deadline = $row['deadline'];
    $date_posted = $row['date_created'];
    if ($start_date != '') {
      $start_date = date('Y-m-d', strtotime($start_date));
    }
    if ($deadline != '') {
      $deadline = date('Y-m-d', strtotime($deadline));
    }
    $project_id = $row['project_id'];
    $user_id = $row['user_id'];

    

    $sqlpt = "SELECT * FROM projects Where project_id = '$project_id'";
    $resultpt = mysqli_query($con, $sqlpt);
    $rowpt = mysqli_fetch_assoc($resultpt);
    $project_title = $rowpt['project_title'];

    
    $sqlpr = "SELECT * FROM users_info Where user_id = '$user_id'";
    $resultpr = mysqli_query($con, $sqlpr);

    if (mysqli_num_rows($resultpr) > 0) {


    $rowpr = mysqli_fetch_assoc($resultpr);
    $profiles = $rowpr['profile'];
    $task_poster_name = $rowpr['first_name'].' '.$rowpr['last_name'];
    if(empty($profiles))
    {
      $profiles = file_get_contents('images/Default_pfp.png');
    }

  }else{
    $profiles = file_get_contents('images/Default_pfp.png');
    $task_poster_name = 'Deleted Account';
  }

    
    $task_files = $row['files'];
    if(!empty($task_files)){
      
    $task_files = explode(',', $task_files);

    $file_details = array();
    foreach ($task_files as $file_id) {
        $qfile = "SELECT * FROM `files` WHERE id=$file_id";
        $fileresult = mysqli_query($con, $qfile);
        $filerow = mysqli_fetch_assoc($fileresult);
        $file_details[] = $filerow;
    }

  }else{
    $file_details[] = '';
  }



//other comments


$qComments = "SELECT * FROM task_comments WHERE task_id = $id ORDER BY comment_id DESC";
$resultC = mysqli_query($con, $qComments);
$comments = [];


if (mysqli_num_rows($resultC) > 0) {
  while ($rowC = $resultC->fetch_assoc()) {
      $comment_id = $rowC['comment_id'];
      $commenter_user_id = $rowC['user_id'];
      $commenter_comment = $rowC['description'];
      $commenter_files = $rowC['files'];
      $comment_date = $rowC['date_stamp'];

      $qcommenter = "SELECT * FROM users_info WHERE user_id = $commenter_user_id";
      $rCommenter = mysqli_query($con, $qcommenter);
      if (mysqli_num_rows($rCommenter) > 0) {

      $commenter_dp = mysqli_fetch_assoc($rCommenter);
      $commenter_profile = $commenter_dp['profile'];

      $task_commenter_name = $commenter_dp['first_name'].' '.$commenter_dp['last_name'];
      if (empty($commenter_dp['profile'])) {
          $commenter_dp = file_get_contents('images/Default_pfp.png');
      }

    }else{
      $commenter_profile = file_get_contents('images/Default_pfp.png');
      $task_commenter_name = 'Deleted Account';
    }

      // Fetch files
      $files = [];
      if (!empty($commenter_files)) {
          $file_ids = explode(',', $commenter_files);
          foreach ($file_ids as $file_id) {
              $qfile = "SELECT * FROM files WHERE id=$file_id";
              $fileresult = mysqli_query($con, $qfile);
              $file = mysqli_fetch_assoc($fileresult);
              $files[] = $file;
          }
      }

      $comments[] = [
          'comment_id' => $comment_id,
          'commenter_user_id' => $commenter_user_id,
          'commenter_comment' => $commenter_comment,
          'commenter_files' => $files,
          'commenter_files_all' => $commenter_files,
          'comment_date' => $comment_date,
          'commenter_profile' => base64_encode($commenter_profile),
          'commenter_name' => $task_commenter_name,
      ];
  }
}








    $res = [
      'status' => 400,
      'data' => $row,
      'start_date' => $start_date,
      'deadline' => $deadline,
      'pId' => $pId,
      'project_title' => $project_title,
      'profiles' => base64_encode($profiles),
      'taskPName' => $task_poster_name,
      'taskPDate' => $date_posted,
      'pTaskFiles' => $file_details,
      'pTaskComments' => $comments,
    ];
    
    echo json_encode($res);
    return false;
  }

















}



if (isset($_GET['updateid'])) {
  $id = $_GET['updateid'];
  $sql = "SELECT * FROM projects WHERE project_id = $id";
  $result = mysqli_query($con, $sql);
  $row = mysqli_fetch_assoc($result);
  if ($row) {
    $start_date = $row['date_start'];
    $deadline = $row['deadline'];
    if ($start_date != '') {
      $start_date = date('m/d/Y', strtotime($start_date));
    }
    if ($deadline != '') {
      $deadline = date('m/d/Y', strtotime($deadline));
    }

    $res = [
      'status' => 200,
      'data' => $row,
      'start_date' => $start_date,
      'deadline' => $deadline,
    ];
    echo json_encode($res);
    return false;
  }
}

if (isset($_GET['updatetaskid'])) {
  $id = $_GET['updatetaskid'];
  $sql = "SELECT * FROM projects_task WHERE task_id = $id";
  $result = mysqli_query($con, $sql);
  $row = mysqli_fetch_assoc($result);
  if ($row) {
    $start_date = $row['start_date'];
    $deadline = $row['deadline'];
    if ($start_date != '') {
      $start_date = date('m/d/Y', strtotime($start_date));
    }
    if ($deadline != '') {
      $deadline = date('m/d/Y', strtotime($deadline));
    }

    $res = [
      'status' => 200,
      'data' => $row,
      'start_date' => $start_date,
      'deadline' => $deadline,
    ];
    echo json_encode($res);
    return false;
  }
}



if (isset($_GET['previewid'])) {
  $id = $_GET['previewid'];
  $sql = "SELECT * FROM projects_task WHERE task_id = $id";
  $result = mysqli_query($con, $sql);
  $row = mysqli_fetch_assoc($result);
  if ($row) {
    $pId = 'Task Info #' . sprintf("%04d", $row['task_id']);
    $taskDescription = $row['description'];
    $taskDescription = nl2br($taskDescription);
    $start_date = $row['start_date'];
    $deadline = $row['deadline'];
    $date_posted = $row['date_created'];
    if ($start_date != '') {
      $start_date = date('Y-m-d', strtotime($start_date));
    }
    if ($deadline != '') {
      $deadline = date('Y-m-d', strtotime($deadline));
    }
    $project_id = $row['project_id'];
    $user_id = $row['user_id'];

    

    $sqlpt = "SELECT * FROM projects Where project_id = '$project_id'";
    $resultpt = mysqli_query($con, $sqlpt);
    $rowpt = mysqli_fetch_assoc($resultpt);
    $project_title = $rowpt['project_title'];

    
    $sqlpr = "SELECT * FROM users_info Where user_id = '$user_id'";
    $resultpr = mysqli_query($con, $sqlpr);
    
if (mysqli_num_rows($resultpr) > 0) {
  while ($rowpr = $resultpr->fetch_assoc()) {
    $profiles = $rowpr['profile'];
    $task_poster_name = $rowpr['first_name'].' '.$rowpr['last_name'];
    if(empty($profiles))
    {
      $profiles = file_get_contents('images/Default_pfp.png');
    }
  }
}else{
  
  $profiles = file_get_contents('images/Default_pfp.png');
  $task_poster_name = 'Deleted Account';
}
   

    
    $task_files = $row['files'];
    if(!empty($task_files)){
      
    $task_files = explode(',', $task_files);

    $file_details = array();
    foreach ($task_files as $file_id) {
        $qfile = "SELECT * FROM `files` WHERE id=$file_id";
        $fileresult = mysqli_query($con, $qfile);
        $filerow = mysqli_fetch_assoc($fileresult);
        $file_details[] = $filerow;
    }

  }else{
    $file_details[] = '';
  }



//other comments


$qComments = "SELECT * FROM task_comments WHERE task_id = $id ORDER BY comment_id DESC";
$resultC = mysqli_query($con, $qComments);
$comments = [];


if (mysqli_num_rows($resultC) > 0) {
  while ($rowC = $resultC->fetch_assoc()) {
      $comment_id = $rowC['comment_id'];
      $commenter_user_id = $rowC['user_id'];
      $commenter_comment = nl2br($rowC['description']);
      $commenter_files = $rowC['files'];
      $comment_date = $rowC['date_stamp'];

      $qcommenter = "SELECT * FROM users_info WHERE user_id = $commenter_user_id";
      $rCommenter = mysqli_query($con, $qcommenter);
      
if (mysqli_num_rows($rCommenter) > 0) {
  while ($commenter_dp = $rCommenter->fetch_assoc()) {
    $commenter_profile = $commenter_dp['profile'];
      $task_commenter_name = $commenter_dp['first_name'].' '.$commenter_dp['last_name'];
      if (empty($commenter_dp['profile'])) {
          $commenter_dp = file_get_contents('images/Default_pfp.png');
      }
  }
}else{
  
  $commenter_profile = file_get_contents('images/Default_pfp.png');
  $task_commenter_name = 'Deleted Account';
}
      

      // Fetch files
      $files = [];
      if (!empty($commenter_files)) {
          $file_ids = explode(',', $commenter_files);
          foreach ($file_ids as $file_id) {
              $qfile = "SELECT * FROM files WHERE id=$file_id";
              $fileresult = mysqli_query($con, $qfile);
              $file = mysqli_fetch_assoc($fileresult);
              $files[] = $file;
          }
      }

      $comments[] = [
          'comment_id' => $comment_id,
          'commenter_user_id' => $commenter_user_id,
          'commenter_comment' => $commenter_comment,
          'commenter_files' => $files,
          'commenter_files_all' => $commenter_files,
          'comment_date' => $comment_date,
          'commenter_profile' => base64_encode($commenter_profile),
          'commenter_name' => $task_commenter_name,
      ];
  }
}








    $res = [
      'status' => 300,
      'data' => $row,
      'start_date' => $start_date,
      'deadline' => $deadline,
      'pId' => $pId,
      'project_title' => $project_title,
      'profiles' => base64_encode($profiles),
      'taskPName' => $task_poster_name,
      'taskPDate' => $date_posted,
      'pTaskFiles' => $file_details,
      'pTaskComments' => $comments,
      'taskDescription' => $taskDescription,
    ];
    
    echo json_encode($res);
    return false;
  }
}







if (isset($_POST['previewDeleteTaskF'])) {
  $m_previewDeleteTaskId =  mysqli_real_escape_string($con, $_POST['m_previewDeleteTaskId']);


  $sql = "delete from `projects_task` where task_id =$m_previewDeleteTaskId";
  $result = mysqli_query($con, $sql);




  $res = [
    'status' => 100,
  ];
  echo json_encode($res);
  return;



}
















if (isset($_POST['add-task'])) {


  $users_id =  mysqli_real_escape_string($con, $_POST['user_id']);
  $project_id =  mysqli_real_escape_string($con, $_POST['project_id']);
  $m_taskId =  mysqli_real_escape_string($con, $_POST['m_taskId']);
  $task_title =  mysqli_real_escape_string($con, $_POST['task_title']);
  $task_description =  mysqli_real_escape_string($con, $_POST['task_description']);
  $start_date = mysqli_real_escape_string($con, $_POST['start_date']);
  $deadline = mysqli_real_escape_string($con, $_POST['deadline']);
  $task_status = mysqli_real_escape_string($con, $_POST['task_status']);
  if (!in_array($task_status, ['To do', 'In progress', 'For Checking', 'Completed', 'Cancelled'], true)) {
    http_response_code(400);
    exit('Invalid task status');
  }


  if (empty($start_date)) {
    $start_date = '';
  } else {
    $start_date = new DateTime($start_date);
    $start_date = $start_date->format('F j, Y');
  }
  if (empty($deadline)) {
    $deadline = '';
  } else {
    $deadline = new DateTime($deadline);
    $deadline = $deadline->format('F j, Y');
  }
  if (empty($task_status)) {
    $task_status = '';
  }


  if ($m_taskId != '') {
    date_default_timezone_set('Asia/Manila');
    $newOrder = date('m-d-Y H:i:s');
    $sql = "UPDATE projects_task SET task_title='$task_title', description='$task_description', start_date='$start_date', deadline='$deadline', status='$task_status', orders='$newOrder' WHERE task_id = '$m_taskId'";
    $result = mysqli_query($con, $sql);

    if ($result) {


      $res = [
        'status' => 100,
      ];
      echo json_encode($res);
      return;
    } else {
      echo "error";
    }
  } else {




    $uploadedFiles = array();

    if (!empty($_FILES['task_files']['tmp_name'][0])) {
        $fileCount = count($_FILES['task_files']['tmp_name']);
    
        for ($i = 0; $i < $fileCount; $i++) {
            $file_name = $_FILES['task_files']['name'][$i];
            $file_type = $_FILES['task_files']['type'][$i];
            $file_tmp_name = $_FILES['task_files']['tmp_name'][$i];
    
            // Move the file to the 'files' folder
            $target_path = '../../files/task/' . $file_name;
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
$newOrder = date('m-d-Y H:i:s');
 



    if (!empty($uploadedFiles)) {
      $fileIdsString = implode(',', $uploadedFiles); 
      $query = "INSERT INTO projects_task (project_id, user_id, task_title, description, start_date, deadline, status, files, orders, date_created) 
      VALUES ('$project_id','$users_id', '$task_title', '$task_description', '$start_date', '$deadline', '$task_status', '$fileIdsString', '$newOrder', '$createdDate')";
  } else {
    $query = "INSERT INTO projects_task (project_id, user_id, task_title, description, start_date, deadline, status, orders, date_created) 
    VALUES ('$project_id','$users_id', '$task_title', '$task_description', '$start_date', '$deadline', '$task_status', '$newOrder', '$createdDate')";
  }
  
  $result = mysqli_query($con, $query);









    if ($result) {
      

      $last_id = $con->insert_id;


      $res = [
        'status' => 100,
      ];
      echo json_encode($res);
      return;
    } else {
      echo "error";
    }
  }





  mysqli_close($con);
}





































if (isset($_POST['add-project'])) {


  $m_projectId =  mysqli_real_escape_string($con, $_POST['m_projectId']);
  $project_title =  mysqli_real_escape_string($con, $_POST['project_title']);
  $project_description =  mysqli_real_escape_string($con, $_POST['project_description']);
  $start_date = mysqli_real_escape_string($con, $_POST['start_date']);
  $deadline = mysqli_real_escape_string($con, $_POST['deadline']);
  $project_status = mysqli_real_escape_string($con, $_POST['project_status']);


  if (empty($start_date)) {
    $start_date = '';
  } else {
    $start_date = new DateTime($start_date);
    $start_date = $start_date->format('F j, Y');
  }
  if (empty($deadline)) {
    $deadline = '';
  } else {
    $deadline = new DateTime($deadline);
    $deadline = $deadline->format('F j, Y');
  }
  if (empty($project_status)) {
    $project_status = '';
  }


  if ($m_projectId != '') {
    $sql = "UPDATE projects SET project_title='$project_title',project_description='$project_description', date_start='$start_date', deadline='$deadline', status='$project_status' WHERE project_id = '$m_projectId'";
    $result = mysqli_query($con, $sql);

    if ($result) {


      $res = [
        'status' => 200,
        'id' => $m_projectId,
      ];
      echo json_encode($res);
      return;
    } else {
      echo "error";
    }
  } else {



    function formatBytes($bytes, $precision = 2) {
      $units = ['B', 'KB', 'MB', 'GB', 'TB'];
  
      $bytes = max($bytes, 0);
      $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
      $pow = min($pow, count($units) - 1);
  
      // Calculate bytes in the appropriate unit
      $bytes /= (1 << (10 * $pow));
  
      return round($bytes, $precision) . ' ' . $units[$pow];
  }


    if (!empty($_FILES['project_files']['tmp_name'][0])) {
      $fileCount = count($_FILES['project_files']['tmp_name']);
  
      for ($i = 0; $i < $fileCount; $i++) {
          $file_name = $_FILES['project_files']['name'][$i];
          $file_type = $_FILES['project_files']['type'][$i];
          $file_tmp_name = $_FILES['project_files']['tmp_name'][$i];
  
          $file_size = filesize($file_tmp_name);
          $file_sizes = formatBytes($file_size);
          // Move the file to the 'files' folder
          $target_path = '../../files/project/' . $file_name;
          if (move_uploaded_file($file_tmp_name, $target_path)) {
              // Insert file name and file type into the database
              $query = "INSERT INTO files (file_name, file_type, file_size) 
                        VALUES ('$file_name', '$file_type', '$file_sizes')";
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








  if (!empty($uploadedFiles)) {
    $fileIdsString = implode(',', $uploadedFiles); 
    $query = "INSERT INTO projects (project_title,project_description, date_start, deadline, files, status) 
    VALUES ('$project_title', '$project_description', '$start_date', '$deadline', '$fileIdsString', '$project_status')";
} else {
  $query = "INSERT INTO projects (project_title,project_description, date_start, deadline, status) 
  VALUES ('$project_title', '$project_description', '$start_date', '$deadline', '$project_status')";
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










