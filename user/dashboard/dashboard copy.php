<?php 
include("../../includes/connections.php");
session_start();

if (!empty($_SESSION['logged_in'])) {
  if($_SESSION['role'] == 'super_admin'){
    header("location:../../admin/dashboard/dashboard.php");
  }
} else {
    header("location:../../index.php");
    die;
}

$user_id = $_SESSION['user_id'];


?>
<!DOCTYPE html>
<html lang="en" >
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
<?php include("../header.php") ?>

</head>
<body>
<!-- partial:index.partial.html -->
<div class="layout has-sidebar fixed-sidebar fixed-header">
      <?php include("../sidebar.php"); ?>

      <div class="layout">
        <main class="content">
          <div>
            <a id="btn-toggle" href="#" class="sidebar-toggler break-point-sm">
              <i class="ri-menu-line ri-xl"></i>
            </a>
          </div>

          <div class="row mt-4 card shadow-sm">
            <div class="card-header">
            <h6>User - Dashboard</h6>
            </div>
            <div class="card-body ">
         
            
            </div>
           
          </div>

          



         






    
        </main>
        <div class="overlay"></div>
      </div>
      
    </div>
<!-- partial -->



 <?php include("../footer.php") ?>

</body>

</html>