<?php
session_start();

include("../../includes/connections.php");

$user_role = $_SESSION['role'] ?? '';
$user_id = (int) ($_SESSION['user_id'] ?? 0);

if (isset($_GET['shareusers']) || isset($_POST['share-project']) || isset($_POST['unshare-project'])) {
  $project_id = (int) ($_GET['shareusers'] ?? $_POST['project_id'] ?? 0);
  $can_manage_projects = in_array($user_role, ['admin', 'super_admin'], true);
  $project_check = mysqli_query($con, "SELECT project_id FROM projects WHERE project_id = $project_id");
  if (!$project_check || mysqli_num_rows($project_check) === 0) {
    http_response_code(404);
    echo json_encode(['status' => 404, 'message' => 'Project not found.']);
    exit;
  }
  $share_access = $can_manage_projects;

  if (!$share_access) {
    $check = mysqli_query($con, "SELECT 1 FROM project_shares WHERE project_id = $project_id AND user_id = $user_id");
    $share_access = $check && mysqli_num_rows($check) > 0;
  }

  if (!$share_access || !in_array($user_role, ['admin', 'super_admin', 'technician', 'client'], true)) {
    http_response_code(403);
    echo json_encode(['status' => 403, 'message' => 'You do not have access to share this project.']);
    exit;
  }

  if (isset($_POST['share-project'])) {
    $share_user_ids = $_POST['share_user_ids'] ?? [$_POST['share_user_id'] ?? 0];
    if (!is_array($share_user_ids) || !$share_user_ids) {
      http_response_code(400);
      echo json_encode(['status' => 400, 'message' => 'Select at least one valid user to share with.']);
      exit;
    }

    $share_user_ids = array_values(array_unique(array_map('intval', $share_user_ids)));
    $user_check = mysqli_prepare($con, "SELECT user_id FROM users WHERE user_id = ? AND user_id != ?");
    if (!$user_check) {
      http_response_code(500);
      echo json_encode(['status' => 500, 'message' => 'Unable to validate selected users.']);
      exit;
    }
    foreach ($share_user_ids as $share_user_id) {
      if ($share_user_id <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 400, 'message' => 'Select valid users to share with.']);
        exit;
      }
      mysqli_stmt_bind_param($user_check, 'ii', $share_user_id, $user_id);
      if (!mysqli_stmt_execute($user_check)) {
        http_response_code(500);
        echo json_encode(['status' => 500, 'message' => 'Unable to validate selected users.']);
        exit;
      }
      $valid_user = mysqli_stmt_get_result($user_check);
      if (!$valid_user || mysqli_num_rows($valid_user) === 0) {
        http_response_code(400);
        echo json_encode(['status' => 400, 'message' => 'Select valid users to share with.']);
        exit;
      }
      mysqli_free_result($valid_user);
    }
    mysqli_stmt_close($user_check);

    if (!mysqli_begin_transaction($con)) {
      http_response_code(500);
      echo json_encode(['status' => 500, 'message' => 'Unable to share this project.']);
      exit;
    }
    $stmt = mysqli_prepare($con, "INSERT IGNORE INTO project_shares (project_id, user_id, shared_by) VALUES (?, ?, ?)");
    if (!$stmt) {
      mysqli_rollback($con);
      http_response_code(500);
      echo json_encode(['status' => 500, 'message' => 'Unable to share this project.']);
      exit;
    }
    foreach ($share_user_ids as $share_user_id) {
      mysqli_stmt_bind_param($stmt, 'iii', $project_id, $share_user_id, $user_id);
      if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        mysqli_rollback($con);
        http_response_code(500);
        echo json_encode(['status' => 500, 'message' => 'Unable to share this project.']);
        exit;
      }
    }
    mysqli_stmt_close($stmt);
    if (!mysqli_commit($con)) {
      mysqli_rollback($con);
      http_response_code(500);
      echo json_encode(['status' => 500, 'message' => 'Unable to share this project.']);
      exit;
    }
  }

  if (isset($_POST['unshare-project'])) {
    $share_user_id = (int) ($_POST['share_user_id'] ?? 0);
    if ($share_user_id <= 0) {
      http_response_code(400);
      echo json_encode(['status' => 400, 'message' => 'Select a valid user to remove.']);
      exit;
    }

    $stmt = mysqli_prepare($con, "DELETE FROM project_shares WHERE project_id = ? AND user_id = ?");
    if (!$stmt) {
      http_response_code(500);
      echo json_encode(['status' => 500, 'message' => 'Unable to remove project sharing.']);
      exit;
    }
    mysqli_stmt_bind_param($stmt, 'ii', $project_id, $share_user_id);
    if (!mysqli_stmt_execute($stmt)) {
      mysqli_stmt_close($stmt);
      http_response_code(500);
      echo json_encode(['status' => 500, 'message' => 'Unable to remove project sharing.']);
      exit;
    }
    $removed = mysqli_stmt_affected_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    if (!$removed) {
      http_response_code(404);
      echo json_encode(['status' => 404, 'message' => 'This user is not currently shared on the project.']);
      exit;
    }
  }

  $users = [];
  $user_query = mysqli_query($con, "SELECT users.user_id, users.role, users.email,
      users_info.first_name, users_info.last_name
    FROM users
    LEFT JOIN users_info ON users_info.user_id = users.user_id
    WHERE users.user_id != $user_id
    AND NOT EXISTS (
      SELECT 1 FROM project_shares
      WHERE project_shares.project_id = $project_id AND project_shares.user_id = users.user_id
    )
    ORDER BY users_info.first_name, users.email");
  if (!$user_query) {
    http_response_code(500);
    echo json_encode(['status' => 500, 'message' => 'Unable to load project users.']);
    exit;
  }
  while ($row = mysqli_fetch_assoc($user_query)) {
    $users[] = [
      'user_id' => (int) $row['user_id'],
      'role' => $row['role'],
      'name' => trim($row['first_name'] . ' ' . $row['last_name']) ?: $row['email'],
    ];
  }

  $shared = [];
  $shared_query = mysqli_query($con, "SELECT users.user_id, users.role, users.email,
      users_info.first_name, users_info.last_name, users_info.profile, users_info.profile_type
    FROM project_shares
    INNER JOIN users ON users.user_id = project_shares.user_id
    LEFT JOIN users_info ON users_info.user_id = users.user_id
    WHERE project_shares.project_id = $project_id
    ORDER BY users_info.first_name, users.email");
  if (!$shared_query) {
    http_response_code(500);
    echo json_encode(['status' => 500, 'message' => 'Unable to load shared users.']);
    exit;
  }
  while ($row = mysqli_fetch_assoc($shared_query)) {
    $account_profile = $row['profile'] ?? '';
    $account_profile_type = $row['profile_type'] ?? '';
    if (empty($account_profile)) {
      $account_profile = file_get_contents('images/Default_pfp.png');
      $account_profile_type = 'image/png';
    }
    $shared[] = [
      'user_id' => (int) $row['user_id'],
      'role' => $row['role'],
      'name' => trim($row['first_name'] . ' ' . $row['last_name']) ?: $row['email'],
      'profile' => base64_encode($account_profile),
      'profile_type' => $account_profile_type ?: 'image/png',
    ];
  }

  echo json_encode(['status' => 200, 'users' => $users, 'shared' => $shared]);
  exit;
}

if (isset($_POST['post-project-comment'])) {

  $comment_user_id = (int) $user_id;
  $project_id = (int) ($_POST['project_comment_project_id'] ?? 0);
  $can_manage_projects = in_array($user_role, ['admin', 'super_admin'], true);
  $project_access = mysqli_query($con, "SELECT project_id FROM projects WHERE project_id = $project_id");
  if (!$project_access || mysqli_num_rows($project_access) === 0) {
    http_response_code(404);
    echo json_encode(['status' => 404]);
    exit;
  }
  if (!$can_manage_projects) {
    $project_access = mysqli_query($con, "SELECT project_id FROM project_shares WHERE project_id = $project_id AND user_id = $user_id");
    if (!$project_access || mysqli_num_rows($project_access) === 0) {
      http_response_code(403);
      echo json_encode(['status' => 403]);
      exit;
    }
  }
  $comment_textarea = mysqli_real_escape_string($con, $_POST['project_comment_textarea']);


  if ($comment_textarea == '' && empty($_FILES['project_comment_files']['tmp_name'][0])) {
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

  if (!in_array($user_role, ['admin', 'super_admin'], true)) {
    http_response_code(403);
    echo json_encode(['status' => 403]);
    exit;
  }
  $project_id = (int) ($_POST['project_id'] ?? 0);

  if (empty($_FILES['file_files']['tmp_name'][0])) {
    $res = [
      'status' => 101,
    ];
    echo json_encode($res);
    return;
  }

  $uploadedFiles = array();

  function formatBytes($bytes, $precision = 2)
  {
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
      if (!empty($rowpfiles['files'])) {
        $newfiles = $rowpfiles['files'] . ',' . $newfilesadded;
      } else {
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

  $comment_user_id = (int) $user_id;
  $comment_task_id = (int) ($_POST['comment_task_id'] ?? 0);
  $task_access_sql = "SELECT projects_task.task_id FROM projects_task
    INNER JOIN projects ON projects.project_id = projects_task.project_id
    WHERE projects_task.task_id = $comment_task_id";
  if (!in_array($user_role, ['admin', 'super_admin'], true)) {
    $task_access_sql .= " AND EXISTS (
      SELECT 1 FROM project_shares
      WHERE project_shares.project_id = projects.project_id
      AND project_shares.user_id = $user_id
    )";
  }
  $task_access = mysqli_query($con, $task_access_sql);
  if (!$task_access || mysqli_num_rows($task_access) === 0) {
    http_response_code(403);
    echo json_encode(['status' => 403]);
    exit;
  }
  $comment_textarea = mysqli_real_escape_string($con, $_POST['comment_textarea']);


  if ($comment_textarea == '' && empty($_FILES['comment_files']['tmp_name'][0])) {
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
  if (!in_array($user_role, ['admin', 'super_admin', 'technician'], true)) {
    http_response_code(403);
    echo json_encode(['status' => 403]);
    exit;
  }
  $id = (int) $_GET['deletetaskid'];

  $sql = "SELECT projects_task.* FROM projects_task
    WHERE task_id = $id";
  if (!in_array($user_role, ['admin', 'super_admin'], true)) {
    $sql .= " AND EXISTS (SELECT 1 FROM project_shares
      WHERE project_shares.project_id = projects_task.project_id
      AND project_shares.user_id = $user_id)";
  }
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
    $task_poster_name = $rowpr['first_name'] . ' ' . $rowpr['last_name'];
    if (empty($profiles)) {
      $profiles = file_get_contents('images/Default_pfp.png');
    }

  }else{
    $profiles = file_get_contents('images/Default_pfp.png');
    $task_poster_name = 'Deleted Account';
  }

    $task_files = $row['files'];
    if (!empty($task_files)) {

      $task_files = explode(',', $task_files);

      $file_details = array();
      foreach ($task_files as $file_id) {
        $qfile = "SELECT * FROM `files` WHERE id=$file_id";
        $fileresult = mysqli_query($con, $qfile);
        $filerow = mysqli_fetch_assoc($fileresult);
        $file_details[] = $filerow;
      }
    } else {
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
        $task_commenter_name = $commenter_dp['first_name'] . ' ' . $commenter_dp['last_name'];
        if (empty($commenter_dp['profile'])) {
          $commenter_profile = file_get_contents('images/Default_pfp.png');
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
  if (!in_array($user_role, ['admin', 'super_admin'], true)) {
    http_response_code(403);
    echo json_encode(['status' => 403]);
    exit;
  }
  $id = (int) $_GET['updateid'];
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
  if (!in_array($user_role, ['admin', 'super_admin', 'technician'], true)) {
    http_response_code(403);
    echo json_encode(['status' => 403]);
    exit;
  }
  $id = (int) $_GET['updatetaskid'];
  $sql = "SELECT projects_task.* FROM projects_task WHERE task_id = $id";
  if (!in_array($user_role, ['admin', 'super_admin'], true)) {
    $sql .= " AND EXISTS (SELECT 1 FROM project_shares
      WHERE project_shares.project_id = projects_task.project_id
      AND project_shares.user_id = $user_id)";
  }
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

    $task_files = [];
    if (!empty($row['files'])) {
      foreach (explode(',', $row['files']) as $file_id) {
        $file_id = (int) $file_id;
        if ($file_id > 0) {
          $file_result = mysqli_query($con, "SELECT id, file_name, file_type FROM files WHERE id = $file_id");
          if ($file_result && $file = mysqli_fetch_assoc($file_result)) {
            $task_files[] = $file;
          }
        }
      }
    }

    $res = [
      'status' => 200,
      'data' => $row,
      'start_date' => $start_date,
      'deadline' => $deadline,
      'taskFiles' => $task_files,
    ];
    echo json_encode($res);
    return false;
  }
}



if (isset($_GET['previewid'])) {
  $id = (int) $_GET['previewid'];
  $sql = "SELECT projects_task.* FROM projects_task WHERE task_id = $id";
  if (!in_array($user_role, ['admin', 'super_admin'], true)) {
    $sql .= " AND EXISTS (SELECT 1 FROM project_shares
      WHERE project_shares.project_id = projects_task.project_id
      AND project_shares.user_id = $user_id)";
  }
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
        $task_poster_name = $rowpr['first_name'] . ' ' . $rowpr['last_name'];
        if (empty($profiles)) {
          $profiles = file_get_contents('images/Default_pfp.png');
        }
      }
    } else {

      $profiles = file_get_contents('images/Default_pfp.png');
      $task_poster_name = 'Deleted Account';
    }

    $task_files = $row['files'];
    if (!empty($task_files)) {

      $task_files = explode(',', $task_files);

      $file_details = array();
      foreach ($task_files as $file_id) {
        $qfile = "SELECT * FROM `files` WHERE id=$file_id";
        $fileresult = mysqli_query($con, $qfile);
        $filerow = mysqli_fetch_assoc($fileresult);
        $file_details[] = $filerow;
      }
    } else {
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


        $sqlurole = "SELECT * FROM users Where user_id = '$commenter_user_id'";
        $resulturole = mysqli_query($con, $sqlurole);
        if (mysqli_num_rows($resulturole) > 0) {
      
        $rowurole = mysqli_fetch_assoc($resulturole);
        $users_role = $rowurole['role'];

        $qcommenter = "SELECT * FROM users_info WHERE user_id = $commenter_user_id";
        $rCommenter = mysqli_query($con, $qcommenter);

        if (mysqli_num_rows($rCommenter) > 0) {
          while ($commenter_dp = $rCommenter->fetch_assoc()) {
            $commenter_profile = $commenter_dp['profile'];
            if ($users_role == 'admin' || $users_role == 'super_admin') {
              $task_commenter_name = $commenter_dp['first_name'] . ' ' . $commenter_dp['last_name'] . ' (admin)';
            } else {
              $task_commenter_name = $commenter_dp['first_name'] . ' ' . $commenter_dp['last_name'];
            }
            if (empty($commenter_dp['profile'])) {
              $commenter_profile = file_get_contents('images/Default_pfp.png');
            }
          }
        } else {

          $commenter_profile = file_get_contents('images/Default_pfp.png');
          $task_commenter_name = 'Deleted Account';
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
  if (!in_array($user_role, ['admin', 'super_admin', 'technician'], true)) {
    http_response_code(403);
    echo json_encode(['status' => 403]);
    exit;
  }
  $m_previewDeleteTaskId = (int) ($_POST['m_previewDeleteTaskId'] ?? 0);
  $sql = "DELETE FROM projects_task WHERE task_id = $m_previewDeleteTaskId";
  if (!in_array($user_role, ['admin', 'super_admin'], true)) {
    $sql .= " AND EXISTS (SELECT 1 FROM project_shares
      WHERE project_shares.project_id = projects_task.project_id
      AND project_shares.user_id = $user_id)";
  }
  $result = mysqli_query($con, $sql);

  if (!$result || mysqli_affected_rows($con) !== 1) {
    http_response_code(403);
    echo json_encode(['status' => 403]);
    exit;
  }

  $res = [
    'status' => 100,
  ];
  echo json_encode($res);
  return;
}
















if (isset($_POST['add-task'])) {
  if (!in_array($user_role, ['admin', 'super_admin', 'technician'], true)) {
    http_response_code(403);
    echo json_encode(['status' => 403]);
    exit;
  }
  $users_id = (int) $user_id;
  $project_id = (int) ($_POST['project_id'] ?? 0);
  $m_taskId = (int) ($_POST['m_taskId'] ?? 0);
  $project_access_sql = "SELECT project_id FROM projects WHERE project_id = $project_id";
  if (!in_array($user_role, ['admin', 'super_admin'], true)) {
    $project_access_sql .= " AND EXISTS (SELECT 1 FROM project_shares
      WHERE project_shares.project_id = projects.project_id
      AND project_shares.user_id = $user_id)";
  }
  $project_access = mysqli_query($con, $project_access_sql);
  if (!$project_access || mysqli_num_rows($project_access) === 0) {
    http_response_code(403);
    echo json_encode(['status' => 403]);
    exit;
  }
  if ($m_taskId > 0) {
    $task_access_sql = "SELECT task_id FROM projects_task
      WHERE task_id = $m_taskId AND project_id = $project_id";
    if (!in_array($user_role, ['admin', 'super_admin'], true)) {
      $task_access_sql .= " AND EXISTS (SELECT 1 FROM project_shares
        WHERE project_shares.project_id = projects_task.project_id
        AND project_shares.user_id = $user_id)";
    }
    $task_access = mysqli_query($con, $task_access_sql);
    if (!$task_access || mysqli_num_rows($task_access) === 0) {
      http_response_code(403);
      echo json_encode(['status' => 403]);
      exit;
    }
  }

  $task_title = mysqli_real_escape_string($con, $_POST['task_title'] ?? '');
  $task_description = mysqli_real_escape_string($con, $_POST['task_description'] ?? '');
  if (trim($task_title) === '') {
    echo json_encode(['status' => 101, 'message' => 'Task title is required.']);
    return;
  }
  $start_date = mysqli_real_escape_string($con, $_POST['start_date'] ?? '');
  $deadline = mysqli_real_escape_string($con, $_POST['deadline'] ?? '');
  $task_status = mysqli_real_escape_string($con, $_POST['task_status'] ?? 'To do');
  if (!in_array($task_status, ['To do', 'In progress', 'For Checking', 'Completed', 'Cancelled'], true)) {
    http_response_code(400);
    echo json_encode(['status' => 400, 'message' => 'Invalid task status.']);
    exit;
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

  $uploadedFileIds = [];
  if (!empty($_FILES['task_files']['name'])) {
    foreach ($_FILES['task_files']['name'] as $index => $originalName) {
      if ($_FILES['task_files']['error'][$index] === UPLOAD_ERR_NO_FILE) {
        continue;
      }
      if ($_FILES['task_files']['error'][$index] !== UPLOAD_ERR_OK) {
        echo json_encode(['status' => 102, 'message' => 'A selected file could not be uploaded.']);
        return;
      }

      $file_name = basename($originalName);
      $file_type = mysqli_real_escape_string($con, $_FILES['task_files']['type'][$index]);
      $target_path = '../../files/task/' . $file_name;
      if (!move_uploaded_file($_FILES['task_files']['tmp_name'][$index], $target_path)) {
        echo json_encode(['status' => 102, 'message' => 'A selected file could not be saved.']);
        return;
      }

      $safe_file_name = mysqli_real_escape_string($con, $file_name);
      $query = "INSERT INTO files (file_name, file_type) VALUES ('$safe_file_name', '$file_type')";
      if (!mysqli_query($con, $query)) {
        echo json_encode(['status' => 102, 'message' => 'A selected file could not be recorded.']);
        return;
      }
      $uploadedFileIds[] = (int) mysqli_insert_id($con);
    }
  }



  if ($m_taskId > 0) {

    date_default_timezone_set('Asia/Manila');
    $newOrder = date('m-d-Y H:i:s');


   



    $old_task = mysqli_query($con, "SELECT status, files FROM projects_task WHERE task_id = $m_taskId AND project_id = $project_id");
    $old_task_data = mysqli_fetch_assoc($old_task);
    $old_status = $old_task_data['status'] ?? '';
    if ($task_status !== $old_status) {
      $description = 'Moved this card to ' . $task_status;
      date_default_timezone_set('Asia/Manila');
      $date_stamp = date("m/d/Y h:i a");
      $queryCom = "INSERT INTO task_comments (task_id, user_id, description, date_stamp)
        VALUES ('$m_taskId', '$user_id', '$description', '$date_stamp')";
      mysqli_query($con, $queryCom);
    }

    $file_update = '';
    if ($uploadedFileIds) {
      $existingFileIds = array_filter(array_map('intval', explode(',', $old_task_data['files'] ?? '')));
      $allFileIds = array_merge($existingFileIds, $uploadedFileIds);
      $file_update = ", files='" . implode(',', $allFileIds) . "'";
    }
    $sql = "UPDATE projects_task SET task_title='$task_title', description='$task_description',
      start_date='$start_date', deadline='$deadline', status='$task_status', orders='$newOrder'$file_update
      WHERE task_id = '$m_taskId' AND project_id = '$project_id'";
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
    date_default_timezone_set('Asia/Manila');
    $createdDate = date("m/d/Y h:i a");
    $newOrder = date('m-d-Y H:i:s');




    if (!empty($uploadedFileIds)) {
      $fileIdsString = implode(',', $uploadedFileIds);
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

  if (!in_array($user_role, ['admin', 'super_admin'], true)) {
    http_response_code(403);
    echo json_encode(['status' => 403]);
    exit;
  }

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



    function formatBytes($bytes, $precision = 2)
    {
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
