<?php
include "../../includes/connections.php";
session_start();

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
date_default_timezone_set('Asia/Manila');

if (isset($_GET['ticketID'])) {
  $id = $_GET['ticketID'];
  $url = $_GET['ticketID'];

  
  $client = $_GET['usersID'];
  $clientEmail = "Select * from users where user_id = $client";
  $resultEmail = mysqli_query($con, $clientEmail);
  if(mysqli_num_rows($resultEmail) > 0){
    while ($rowEmail = $resultEmail->fetch_assoc()) {
      $client_email = $rowEmail['email'];
    }
  }
  

$ticket_url = "https://ticketing.slarenasitsolutions.com/user/tickets/view.php?ticketID=" . $id;
$return_url = urlencode($ticket_url);
$login_url = "https://ticketing.slarenasitsolutions.com/index.php?return_url=" . $return_url;


    $queryTech = "Select * from users_info where user_id = $user_id";
  $resultTech = mysqli_query($con, $queryTech);
  if(mysqli_num_rows($resultTech) > 0){
    while ($rowTech = $resultTech->fetch_assoc()) {
      $technicianInfo = $rowTech['first_name'].' '.$rowTech['last_name'].' ( '.$user_role.')';
    }
  }
  




$qAction = "Select * from tickets where ticket_id = $id";
$rAction = mysqli_query($con, $qAction);
if (mysqli_num_rows($rAction) > 0) {
  while ($rowAction = $rAction->fetch_assoc()) {
    $ticket_id = $rowAction['ticket_id'];
    $status = $rowAction['status'];
    $title = $rowAction['title'];
    $description = $rowAction['description'];
  }
}


$subject = 'Ticket Update';



if($status == 'In progress'){
  $status = 'Closed';

  $message = $technicianInfo . ' Marked your ticket as "Closed".<br>
  <br>
  Ticket ID : ' . $ticket_id.'<br>
  Ticket Title : ' . $title.'<br>
  Ticket Description : ' . $description .'<br>
  Status  : '. $status.'<br>
  <br>
  <br>
  <a href="' . $login_url . '">View Ticket</a>';

}else if($status == 'Open')
{
  $status = 'In progress';

  $message = $technicianInfo . ' Marked your ticket as "In progress".<br>
  <br>
  Ticket ID : ' . $ticket_id.'<br>
  Ticket Title : ' . $title.'<br>
  Ticket Description : ' . $description .'<br>
  Status  : '. $status.'<br>
  <br>
  <br>
  <a href="' . $login_url . '">View Ticket</a>';

}
else{
  $status = 'Open';

  $message = $technicianInfo . ' Marked your ticket as "Open".<br>
  <br>
  Ticket ID : ' . $ticket_id.'<br>
  Ticket Title : ' . $title.'<br>
  Ticket Description : ' . $description .'<br>
  Status  : '. $status.'<br>
  <br>
  <br>
  <a href="' . $login_url . '">View Ticket</a>';

}

include('send.php');


    $last_activity = date("m/d/Y h:i a");
    $sqlup = "update `tickets` set last_activity='$last_activity', status='$status' where ticket_id='$id'";
                $updateresult = mysqli_query($con, $sqlup);
    

  header("Location:view.php?ticketID=$url");
}



 
