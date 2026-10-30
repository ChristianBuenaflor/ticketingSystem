<?php

include("../includes/connections.php");
require_once("../includes/role_permissions.php");
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
    
              $role = normalizeUserRole((string) $user_data['role']);
              if ($role === '') {
                echo json_encode(['status' => 101]);
                return;
              }

              if ($user_data['role'] !== $role) {
                $roleStmt = $con->prepare("UPDATE users SET role = ? WHERE user_id = ?");
                if (!$roleStmt) {
                  http_response_code(500);
                  exit('Unable to update account role.');
                }
                $roleStmt->bind_param('si', $role, $user_data['user_id']);
                if (!$roleStmt->execute()) {
                  $roleStmt->close();
                  http_response_code(500);
                  exit('Unable to update account role.');
                }
                $roleStmt->close();
              }

              $_SESSION['role'] = $role;
              $_SESSION['user_id'] = $user_data['user_id'];
              $_SESSION['logged_in'] = true;
              echo json_encode([
                'status' => 99,
                'return_url' => urlencode($return_url),
              ]);
              return;
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