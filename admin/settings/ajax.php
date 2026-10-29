<?php
include("../../includes/connections.php");
session_start();

if (empty($_SESSION['logged_in']) || (int) ($_SESSION['user_id'] ?? 0) < 1) {
  http_response_code(401);
  exit('Authentication required.');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && (!isset($_POST['csrf_token'], $_SESSION['csrf_token'])
      || !hash_equals($_SESSION['csrf_token'], (string) $_POST['csrf_token']))) {
  http_response_code(403);
  exit('Invalid security token.');
}
$user_id = (int) $_SESSION['user_id'];

if (isset($_POST['update-my-profile'])) {
 

  $first_name =  mysqli_real_escape_string($con, $_POST['first_name']);
  $last_name =  mysqli_real_escape_string($con, $_POST['last_name']);
  $phone =  mysqli_real_escape_string($con, $_POST['phone']);
  $skype =  mysqli_real_escape_string($con, $_POST['skype']);
  $job_title =  mysqli_real_escape_string($con, $_POST['job_title']);
  $gender =  mysqli_real_escape_string($con, $_POST['gender']);
  $company_name =  mysqli_real_escape_string($con, $_POST['company_name']);
  $address =  mysqli_real_escape_string($con, $_POST['address']);
  $city =  mysqli_real_escape_string($con, $_POST['city']);
  $state =  mysqli_real_escape_string($con, $_POST['state']);
  $zip =  mysqli_real_escape_string($con, $_POST['zip']);
  $country =  mysqli_real_escape_string($con, $_POST['country']);
  $c_phone =  mysqli_real_escape_string($con, $_POST['c_phone']);
  $website =  mysqli_real_escape_string($con, $_POST['website']);
  $vat_number =  mysqli_real_escape_string($con, $_POST['vat_number']);
  $telegram_1 =  mysqli_real_escape_string($con, $_POST['telegram_1']);
  $telegram_link_1 =  mysqli_real_escape_string($con, $_POST['telegram_link_1']);
  $telegram_2 =  mysqli_real_escape_string($con, $_POST['telegram_2']);
  $telegram_link_2 =  mysqli_real_escape_string($con, $_POST['telegram_link_2']);
  $pinterest =  mysqli_real_escape_string($con, $_POST['pinterest']);
  $instagram =  mysqli_real_escape_string($con, $_POST['instagram']);
  $github =  mysqli_real_escape_string($con, $_POST['github']);
  $tumbir =  mysqli_real_escape_string($con, $_POST['tumbir']);



  $password =  mysqli_real_escape_string($con, $_POST['password']);
  $retype_password =  mysqli_real_escape_string($con, $_POST['retype_password']);


  $email =  mysqli_real_escape_string($con, $_POST['email']);
  $email_verification =  mysqli_real_escape_string($con, $_POST['email_verification']);




  $image = $_FILES['profile_picture']['tmp_name'];
  $imageType = $_FILES['profile_picture']['type'];



  $query = "Select * from users where user_id = $user_id";
  $result = mysqli_query($con, $query);
  if (mysqli_num_rows($result) > 0) {
    while ($row = $result->fetch_assoc()) {
      $roles = $row['role'];
      $existing_password = $row['password'];
      $existing_email = $row['email'];
      $verification_code = $row['verification_code'];
    }
  }

  

  

  if ($existing_email != $email) {

    if($email_verification == ''){

  




    $queryEChek = "Select * from users where email = '$email'";
    $resultEChek = mysqli_query($con, $queryEChek);


    if (substr($email, -4) != ".com") {

      $res = [
        'status' => 151,
      ];
      echo json_encode($res);
      return;

      
    }


    if (mysqli_num_rows($resultEChek) > 0) {

      $res = [
        'status' => 152,
      ];
      echo json_encode($res);
      return;
    }

    $n = 10;
    function getName($n)
    {
      $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
      $randomString = '';

      for ($i = 0; $i < $n; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
      }

      return $randomString;
    }
    $subject = "Verification Code";
    $message = getName($n);
    $new_email = $email;
    if (!include("send.php")) {
      http_response_code(500);
      echo json_encode(['status' => 154, 'message' => 'Unable to send the verification email.']);
      exit;
    }


    $queryCode = "UPDATE users set verification_code = '$message' where user_id = '$user_id'";
    $codeResult = mysqli_query($con, $queryCode);

    $res = [
      'status' => 150,
    ];
    echo json_encode($res);
    return;



  }else{
    if($email_verification != $verification_code)
    {

      $res = [
        'status' => 153,
      ];
      echo json_encode($res);
      return;
  
    }else{
      $upEmail = "UPDATE users set email = '$email', verification_code = '' where user_id = '$user_id'";
      $upEmailResult = mysqli_query($con, $upEmail);
    }
  }


  }




  if ($password != '') {


    function validatePassword($password)
    {
      if (strlen($password) < 8) {
        return false;
      }
      if (!preg_match('/[0-9]/', $password)) {
        return false;
      }
      if (!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $password)) {
        return false;
      }
      if (!preg_match('/[A-Z]/', $password)) {
        return false;
      }
      return true;
    }
    if (validatePassword($password)) {
    } else {
      $res = [
        'status' => 102,
      ];
      echo json_encode($res);
      return;
    }


    if ($retype_password == '') {
      $res = [
        'status' => 103,
      ];
      echo json_encode($res);
      return;
    }

    function validaterePassword($retype_password)
    {
      if (strlen($retype_password) < 8) {
        return false;
      }
      if (!preg_match('/[0-9]/', $retype_password)) {
        return false;
      }
      if (!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $retype_password)) {
        return false;
      }
      if (!preg_match('/[A-Z]/', $retype_password)) {
        return false;
      }
      return true;
    }
    if (validaterePassword($retype_password)) {
    } else {
      $res = [
        'status' => 102,
      ];
      echo json_encode($res);
      return;
    }



    if ($password == $retype_password) {

      $password = password_hash($password, PASSWORD_DEFAULT);
      $passQuery = "UPDATE users set password = '$password' where user_id = '$user_id'";
      $passResult = mysqli_query($con, $passQuery);
    } else {
      $res = [
        'status' => 104,
      ];
      echo json_encode($res);
      return;
    }
  }






  if (!empty($image)) {
    $imageData = file_get_contents($image);

    // Prepare the SQL statement
    $sql = "UPDATE users_info SET profile = ?, profile_type = ? WHERE user_id = ?";
    $stmt = $con->prepare($sql);

    // Bind parameters and execute the statement
    $stmt->bind_param("ssi", $imageData, $imageType, $user_id);
    $stmt->execute();

    // Close the statement
    $stmt->close();
  }



  // general info 
  date_default_timezone_set('Asia/Manila');
  $last_updated = date("m/d/Y h:i a");
  $upQuery = "UPDATE users_info set last_name = '$last_name', first_name = '$first_name', phone = '$phone',
    skype = '$skype', job_title = '$job_title', gender = '$gender', 
    company_name = '$company_name', address = '$address', city = '$city', state = '$state', zip = '$zip', country = '$country', c_phone = '$c_phone', 
    website = '$website', vat_number = '$vat_number',
    telegram_1 = '$telegram_1', telegram_link_1 = '$telegram_link_1', telegram_2 = '$telegram_2', telegram_link_2 = '$telegram_link_2', 
    pinterest = '$pinterest', instagram = '$instagram',  github = '$github',  tumbir = '$tumbir',
    last_updated = '$last_updated' where user_id = '$user_id'";
  $upResult = mysqli_query($con, $upQuery);



  // general info 


  $res = [
    'status' => 100,
  ];
  echo json_encode($res);
  return;
}
