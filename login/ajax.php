<?php

include("../includes/connections.php");
session_start();


if (isset($_POST['login-submit']))
 {
   
  $login_username = mysqli_real_escape_string($con, $_POST['login_username']);
  $login_password = mysqli_real_escape_string($con, $_POST['login_password']);
  $return_url = mysqli_real_escape_string($con, $_POST['return_url']);


  $query = "select * from users where email = '$login_username' limit 1";
  $result = mysqli_query($con, $query);

  if ($result) {
      if ($result && mysqli_num_rows($result) > 0) {

          $user_data = mysqli_fetch_assoc($result);
          if (password_verify($login_password, $user_data['password'])) {
    
              $role = $user_data['role'];
              $_SESSION['role'] = $user_data['role'];
              $_SESSION['user_id'] = $user_data['user_id'];
              $_SESSION['logged_in'] = true;
              if ($role == "super_admin") {
                  $res = [
                    'status' => 99,
                    'return_url' => urlencode($return_url),
                  ];
                  echo json_encode($res);
                  return;  
               
                 
              }else if ($role == "client" || $role == 'technician' || $role == 'admin') {
                $res = [
                  'status' => 99,
                  'return_url' => urlencode($return_url),
                ];
                echo json_encode($res);
                return;
            }
          }else{
            $res = [
              'status' => 101,
        
            ];
            echo json_encode($res);
            return;
          }
      } else{
        $res = [
            'status' => 101,
      
          ];
          echo json_encode($res);
          return;
      }
  }
 

  
  

}

?>