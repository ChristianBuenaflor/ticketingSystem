<?php
include "../includes/connections.php";

if (isset($_GET['deleteID'])) {
  $id = $_GET['deleteID'];
$from = $_GET['from'];
if(!empty($_GET['urlID'])){
    $url = $_GET['urlID'];
}


if($from == 'projectComments'){

  $sql = "delete from `project_comments` where comment_id =$id";
  $result = mysqli_query($con, $sql);
  echo '<script>window.close();</script>';
}


if($from == 'taskComments'){

  $sql = "delete from `task_comments` where comment_id =$id";
  $result = mysqli_query($con, $sql);
  echo '<script>window.close();</script>';
}


if($from == 'files'){

  
$project_id = $_GET['project_id'];

  $qpfiles = "SELECT * FROM projects WHERE project_id = $project_id";
$rpfiles = mysqli_query($con, $qpfiles);

if (mysqli_num_rows($rpfiles) > 0) {
  while ($rowpfiles = $rpfiles->fetch_assoc()) {
    $file_list = $rowpfiles['files'];
  }
}
$files = explode(",", $file_list);

$index = array_search($id, $files);

// If the ID is found, remove it from the array
if ($index !== false) {
    unset($files[$index]);
}
$newFiles = implode(",", $files);


$sql = "UPDATE projects SET files = '$newFiles'  WHERE project_id = '$project_id'";
$result = mysqli_query($con, $sql);


  $sql = "delete from `files` where id =$id";
  $result = mysqli_query($con, $sql);
  echo '<script>window.close();</script>';



}






if($from == 'projects'){

  $qproject = "Select * from projects where project_id = $id";
  $rproject = mysqli_query($con, $qproject);
  if (mysqli_num_rows($rproject) > 0) {
    while ($rowproject = $rproject->fetch_assoc()) {
      $files = $rowproject['files'];
    }
  }
  
  if(!empty($files)){
  
          $file = explode(',', $files);
          $file_count = count($file);
          for ($j = 0; $j < $file_count; $j++) {
            $project_file = $file[$j];
            $sqlf = "delete from `files` where id =$project_file";
            $resultf = mysqli_query($con, $sqlf);
          }
  }

  $sql = "delete from `projects` where project_id =$id";
  $result = mysqli_query($con, $sql);

  

  header("Location:projects/list.php");
}



if($from == 'users'){

  $sql = "delete from `users_info` where user_id =$id";
  $result = mysqli_query($con, $sql);

  
  $sql = "delete from `users` where user_id =$id";
  $result = mysqli_query($con, $sql);

  header("Location:users/list.php");
}


if($from == 'comment')
{

    $qcomment = "Select * from ticket_comments where comment_id = $id";
    $rcomment = mysqli_query($con, $qcomment);
    if (mysqli_num_rows($rcomment) > 0) {
      while ($rowcomment = $rcomment->fetch_assoc()) {
        $files = $rowcomment['files'];
        $comment_ticket_id = $rowcomment['ticket_id'];
      }
    }
    
    if(!empty($files)){
    
            $file = explode(',', $files);
            $file_count = count($file);
            for ($j = 0; $j < $file_count; $j++) {
              $comment_file = $file[$j];
              $sqlf = "delete from `files` where id =$comment_file";
              $resultf = mysqli_query($con, $sqlf);
            }
    }

    $last_activity = date("m/d/Y h:i a");
    $sqlup = "update `tickets` set last_activity='$last_activity' where ticket_id='$comment_ticket_id'";
                $updateresult = mysqli_query($con, $sqlup);
    

  $sql = "delete from `ticket_comments` where comment_id =$id";
  $result = mysqli_query($con, $sql);

  header("Location:tickets/view.php?ticketID=$url");
}

if($from == 'tickets'){

    $qticket = "Select * from tickets where ticket_id = $id";
    $rticket = mysqli_query($con, $qticket);
    if (mysqli_num_rows($rticket) > 0) {
      while ($rowticket = $rticket->fetch_assoc()) {
        $files = $rowticket['files'];
      }
    }
    
    if(!empty($files)){
    
            $file = explode(',', $files);
            $file_count = count($file);
            for ($j = 0; $j < $file_count; $j++) {
              $ticket_file = $file[$j];
              $sqlf = "delete from `files` where id =$ticket_file";
              $resultf = mysqli_query($con, $sqlf);
            }
    }


  $sql = "delete from `tickets` where ticket_id =$id";
  $result = mysqli_query($con, $sql);

  header("Location:tickets/list.php");




}



 
}