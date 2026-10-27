<?php
include "../../includes/connections.php";

date_default_timezone_set('Asia/Manila');

if (isset($_GET['ticketID'])) {
  $id = $_GET['ticketID'];

if(!empty($_GET['urlID'])){
    $url = $_GET['urlID'];
}

$qAction = "Select * from tickets where ticket_id = $id";
$rAction = mysqli_query($con, $qAction);
if (mysqli_num_rows($rAction) > 0) {
  while ($rowAction = $rAction->fetch_assoc()) {
    $status = $rowAction['status'];
  }
}

if($status == 'In progress'){
  $status = 'Closed';
}else if($status == 'Open')
{
  $status = 'In progress';
}
else{
  $status = 'Open';
}


    $last_activity = date("m/d/Y h:i a");
    $sqlup = "update `tickets` set last_activity='$last_activity', status='$status' where ticket_id='$id'";
                $updateresult = mysqli_query($con, $sqlup);
    

  header("Location:view.php?ticketID=$url");
}



 
