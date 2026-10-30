<?php
include("../../includes/connections.php");
session_start();

if (empty($_SESSION['logged_in']) || !isManagementRole((string) ($_SESSION['role'] ?? ''))) {
  http_response_code(403);
  exit('Forbidden.');
}

if (isset($_GET['moveTicket'])) {
  $id = $_GET['moveTicket'];

  $ticket_id = 'Ticket #' . sprintf("%04d", $id);

  $res = [
    'status' => 200,
    'tickets_id' => $ticket_id,
    'ticket_id' => $id,
  ];
  echo json_encode($res);
  return false;
}



if (isset($_POST['selected_date'], $_POST['user_ids'])) {
  $selectedDate = mysqli_real_escape_string($con, $_POST['selected_date']);
  $id = mysqli_real_escape_string($con, $_POST['user_ids']);

  $sql = "UPDATE evaluation_date SET date_month = '$selectedDate' WHERE user_id = $id";

  if (mysqli_query($con, $sql)) {
    echo "Data updated successfully";
  } else {
    http_response_code(500); // Internal Server Error
    echo "Error updating data: " . mysqli_error($con);
  }
}












if (isset($_POST['move-ticket'])) {

  $technician =  mysqli_real_escape_string($con, $_POST['technician']);
  $moveTicketsId =  mysqli_real_escape_string($con, $_POST['moveTicketsId']);


  $sql = "UPDATE tickets SET technician=$technician WHERE ticket_id = $moveTicketsId";
  $result = mysqli_query($con, $sql);

  $res = [
    'status' => 100,
  ];
  echo json_encode($res);
  return false;

}

if (isset($_POST['add-user'])) {


  $last_name =  mysqli_real_escape_string($con, $_POST['last_name']);
  $first_name = mysqli_real_escape_string($con, $_POST['first_name']);
  $email_address = mysqli_real_escape_string($con, $_POST['email_address']);
  $phone = mysqli_real_escape_string($con, $_POST['phone']);
  $user_type = mysqli_real_escape_string($con, $_POST['user_type']);
  if (!in_array($user_type, ['admin', 'director', 'head', 'clerk'], true)) {
    http_response_code(400);
    echo json_encode(['status' => 101]);
    return;
  }
  $expertise = '';

$queryEChek = "Select * from users where email = '$email_address'";
  $resultEChek = mysqli_query($con, $queryEChek);


  if (substr($email_address, -4) != ".com") {
    $res = [
      'status' => 102,
    ];
    echo json_encode($res);
    return;
  }


  if (mysqli_num_rows($resultEChek) > 0) {
    $res = [
      'status' => 103,
    ];
    echo json_encode($res);
    return;
  }




  if (mysqli_num_rows($resultEChek) > 0) {
    $res = [
      'status' => 103,
    ];
    echo json_encode($res);
    return;
  }

  $c = 5;
  function getChar($c)
  {
    $characters = 'abcdefghijklmnopqrstuvwxyz';
    $randomString = '';

    for ($i = 0; $i < $c; $i++) {
      $index = rand(0, strlen($characters) - 1);
      $randomString .= $characters[$index];
    }
    return $randomString;
  }
  $s = 1;
  function getSpec($s)
  {
    $characters = '$%&*@#?>';
    $randomString = '';

    for ($i = 0; $i < $s; $i++) {
      $index = rand(0, strlen($characters) - 1);
      $randomString .= $characters[$index];
    }
    return $randomString;
  }
  $u = 1;
  function getUpp($u)
  {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';

    for ($i = 0; $i < $u; $i++) {
      $index = rand(0, strlen($characters) - 1);
      $randomString .= $characters[$index];
    }
    return $randomString;
  }
  $num = 1;
  function getNumber($num)
  {
    $characters = '1234567890';
    $randomString = '';

    for ($i = 0; $i < $num; $i++) {
      $index = rand(0, strlen($characters) - 1);
      $randomString .= $characters[$index];
    }
    return $randomString;
  }
  $characters8 = getChar($c);
  $special1 = getSpec($s);
  $upper1 = getUpp($u);
  $number1 = getNumber($num);


  $randomized_password = $special1 . "" . $upper1 . "" . $characters8 . "" . $number1;


  $password = password_hash($randomized_password, PASSWORD_DEFAULT);


  date_default_timezone_set('Asia/Manila');
  $createdDate = date("m/d/Y h:i a");

  $subject = 'Ticketing System Account registration';
  $message = 'Wellcome new user!<br>
<br>
Your just registered in Our system.<br>
<br>
Name:' . $last_name . ', ' . $first_name . '
<br><br>
--login credentials--
<br><br>
email:' . $email_address . '<br>
password:' . $randomized_password . '';
  $new_email = $email_address;



  $query = "INSERT INTO users (email, password, role) 
              VALUES ('$email_address', '$password', '$user_type')";
  $result = mysqli_query($con, $query);



  $success_message = $last_name . ', ' . $first_name . ' as ' . $user_type;



  if ($result) {

    $last_id = $con->insert_id;



    $usersInfoQuery = "INSERT INTO users_info (user_id, last_name, first_name, phone, expertise) 
                              VALUES ('$last_id', '$last_name', '$first_name', '$phone', '$expertise')";
    $usersInfoResult = mysqli_query($con, $usersInfoQuery);

    $res = [
      'status' => 100,
      'id' => $success_message,
      'email' => $new_email,  
      'subject' => $subject,
      'message' => $message,
    ];
    echo json_encode($res);
    return;
  } else {
    echo "error";
  }


  mysqli_close($con);
};