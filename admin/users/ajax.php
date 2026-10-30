<?php
include("../../includes/connections.php");
session_start();

if (empty($_SESSION['logged_in']) || !isManagementRole((string) ($_SESSION['role'] ?? ''))) {
  http_response_code(403);
  exit('Forbidden.');
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

if($user_type == ''){
    $res = [
        'status' => 101,
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


$randomized_password = $special1."".$upper1."".$characters8."".$number1;


$password = password_hash($randomized_password, PASSWORD_DEFAULT);


date_default_timezone_set('Asia/Manila');
$createdDate = date("m/d/Y h:i a");

$subject = 'Ticketing System Account registration';
$message = 'Wellcome new user!<br>
<br>
Your just registered in Our system as <b>'.$user_type.'<b>.<br>
<br>
Name:'.$last_name.', '.$first_name.'
<br><br>
--login credentials--
<br><br>
email:'.$email_address.'<br>
password:'.$randomized_password.'';
$new_email = $email_address;
include('../settings/send.php');


    $query = "INSERT INTO users (email, password, role) 
              VALUES ('$email_address', '$password', '$user_type')";
$result = mysqli_query($con, $query);







if ($result) {

    $last_id = $con->insert_id;

    
    $usersInfoQuery = "INSERT INTO users_info (user_id, last_name, first_name, phone) 
                              VALUES ('$last_id', '$last_name', '$first_name', '$phone')";
    $usersInfoResult = mysqli_query($con, $usersInfoQuery);
   
    $res = [
        'status' => 100,
        'id' => sprintf("%04d", $last_id),
    ];
    echo json_encode($res);
    return;
} else {
    echo "error";
}


mysqli_close($con);
}





?>
