<?php 
include("../../includes/connections.php");
session_start();

if (!empty($_SESSION['logged_in'])) {
  
} else {
    header("location:../../index.php");
    die;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];



if ($user_role == 'admin' || $user_role == 'super_admin') {
  $sql = "SELECT COUNT(project_id) AS projectCount FROM projects";
} else {
  $sql = "SELECT COUNT(DISTINCT projects.project_id) AS projectCount
    FROM projects INNER JOIN project_shares
    ON project_shares.project_id = projects.project_id
    WHERE project_shares.user_id = $user_id";
}
$result = $con->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $projectCount = $row['projectCount'];
}

if($user_role == 'technician')
{
  $sqlp = "SELECT COUNT(ticket_id) AS ticketCount FROM tickets where technician = $user_id";
  $resultp = $con->query($sqlp);
  
  if ($resultp->num_rows > 0) {
      $rowp = $resultp->fetch_assoc();
      $ticketCount = $rowp['ticketCount'];
  }
}else if($user_role == 'client')
{
  $sqlp = "SELECT COUNT(ticket_id) AS ticketCount FROM tickets where user_id = $user_id";
  $resultp = $con->query($sqlp);
  
  if ($resultp->num_rows > 0) {
      $rowp = $resultp->fetch_assoc();
      $ticketCount = $rowp['ticketCount'];
  }
}else{

  $sqltech = "SELECT COUNT(ticket_id) AS ticketCount FROM tickets";
  $resulttech = $con->query($sqltech);
  
  if ($resulttech->num_rows > 0) {
      $rowtech = $resulttech->fetch_assoc();
      $ticketCount = $rowtech['ticketCount'];
  }

}


$sqlu = "SELECT COUNT(user_id) AS clientCount FROM users where role = 'client'";
$resultu = $con->query($sqlu);

if ($resultu->num_rows > 0) {
    $rowu = $resultu->fetch_assoc();
    $clientCount = $rowu['clientCount'];
}

$sqlt = "SELECT COUNT(user_id) AS technicianCount FROM users where role = 'technician'";
$resultt = $con->query($sqlt);

if ($resultt->num_rows > 0) {
    $rowt = $resultt->fetch_assoc();
    $technicianCount = $rowt['technicianCount'];
}

$sqla = "SELECT COUNT(user_id) AS adminCount FROM users where role = 'admin'";
$resulta = $con->query($sqla);

if ($resulta->num_rows > 0) {
    $rowa = $resulta->fetch_assoc();
    $adminCount = $rowa['adminCount'];
}




?>
<!DOCTYPE html>
<html lang="en" >
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
<?php include("../header.php") ?>

<style>



.counter.animate {
  animation: count 2s ease-out;
}

@keyframes count {
  from { opacity: 0; transform: translateY(-20px); }
  to { opacity: 1; transform: translateY(0); }
}



  .col.card.dash{
  background-color: #f5ab2c;
  color:#a60212;
  text-align: end;
  height:100px;
  justify-content: end;
  border: 0;
  position: relative;
}

.col.card.dash i{
  position: absolute;
  font-size: 2.5rem;
  top:0;
  opacity: .7;
}

.col.card.dash:hover{
background-color:#a60212;
   color:#ffffff;
  cursor: pointer;
  box-shadow: 0 0px 10px 0px rgba(0, 0, 0, 0.05);
}

.col.card.dash2{
background-color:#a60212;
  color:#fff;
  text-align: end;
  height:100px;
  justify-content: end;
  border: 0;
  position: relative;
}

.col.card.dash2:hover{
background-color: #f5ab2c;
  color:#a60212;
  cursor: pointer;
  box-shadow: 0 0px 10px 0px rgba(0, 0, 0, 0.05);
}

.col.card.dash2 i{
  position: absolute;
  font-size: 2.5rem;
  top:0;
  opacity: .7;
}

</style>

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

          <div class="row mt-4 gap-2">
            <a href="../projects/list.php" class="col card dash">
              <h6 class="counter" id="projectCount">0</h6>
              <h5>Projects</h5>
              <i class="ri-server-line"></i>
            </a>
            
            <a href="../tickets/list.php" class="col card dash">
            <h6 class="counter" id="ticketCount">0</h6>
              <h5>Tickets</h5>
              <i class="ri-aed-electrodes-line"></i>
            </a>

           <?php if ($user_role != 'technician' && $user_role != 'client') : ?>
              <a href="../users/clients.php" class="col card dash2">
                <h6 class="counter" id="clientCount">0</h6>
                <h5>Clients</h5>
                <i class="ri-group-line"></i>
              </a>

              <a href="../users/technicians.php" class="col card dash2">
                <h6 class="counter" id="technicianCount">0</h6>
                <h5>Technicians</h5>
                <i class="ri-user-settings-line"></i>
              </a>

              <a href="../users/admins.php" class="col card dash2">
                <h6 class="counter" id="adminCount">0</h6>
                <h5>Admins</h5>
                <i class="ri-admin-line"></i>
              </a>
            <?php endif; ?>
          </div>
        
        











          <div class="row mt-4 card shadow-sm">
          <div class="card-header d-flex" style="justify-content: space-between; padding: 24px 12px; background-color:#a60212; color:#fff; border-radius:0">
            <h6>Projects</h6>
             </div>
          <div class="card-body" id="project_results">

            <table class="table table_display table-bordered table-hover">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Title</th>
                  <th>Start date</th>
                  <th>Deadline</th>
                  <?php 
                  if($user_role != 'client'){
                  ?>
                  <th>Progress</th>
                  <?php } ?>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php
                if ($user_role == 'admin' || $user_role == 'super_admin') {
                  $query = "SELECT * FROM projects";
                } else {
                  $query = "SELECT projects.* FROM projects
                    INNER JOIN project_shares ON projects.project_id = project_shares.project_id
                    WHERE project_shares.user_id = $user_id";
                }
                $result = mysqli_query($con, $query);
                if (mysqli_num_rows($result) > 0) {
                  while ($row = $result->fetch_assoc()) {
                    $project_id = $row['project_id'];
                    $project_title = $row['project_title'];
                    $start_date = $row['date_start'];
                    $deadline = $row['deadline'];
                    $status = $row['status'];



                    if ($start_date == '') {
                      $start_date = '-';
                    }
                    if ($deadline == '') {
                      $deadline = '-';
                    }
                    if ($status == '') {
                      $status = '-';
                    }
                    $queryPTask = "SELECT * FROM projects_task where project_id='$project_id'";
                    $resultpTask = mysqli_query($con, $queryPTask);
                    $projectTaskDone = 0;
                    $projectTaskCount = 0;
                    if (mysqli_num_rows($resultpTask) > 0) {
                      while ($rowPTask = $resultpTask->fetch_assoc()) {
                        if($rowPTask['status']=='Completed')
                        {
                          $projectTaskDone ++;
                        }
                      }
                    }
                    
                    $projectTaskCount = mysqli_num_rows($resultpTask);
                    $avarageProgress = ($projectTaskCount > 0) ? ($projectTaskDone / $projectTaskCount) * 100 : 0;
                    


                ?>
                    <tr>
                      <td class="td<?= $project_id ?>" style="width: 30px;"><?= $project_id ?></td>
                      <td class="td<?= $project_id ?>"><a href="../projects/view.php?project=<?= $project_id ?>"><?= $project_title ?></a></td>
                      <td class="td<?= $project_id ?>" style="width: 150px;"><?= $start_date ?></td>
                      <td class="td<?= $project_id ?>" style="width: 150px;"><?= $deadline ?></td>
                      <?php 
                  if($user_role != 'client'){
                  ?>
                      <td title="<?=  number_format($avarageProgress, 0) . "%" ?>" class="td<?= $project_id ?>" style="width: 300px;">
                        <div style="height:8px;" class="progress" role="progressbar" aria-label="project progress" aria-valuenow="<?=  number_format($avarageProgress, 0) . "%" ?>" aria-valuemin="0" aria-valuemax="100">
                          <div class="progress-bar" style="width: <?=  number_format($avarageProgress, 0) . "%" ?>; background-color:#a60212;"></div>
                        </div>
                      </td>
                      <?php } ?>
                      <td style="width: 100px;" class="position-relative td<?= $project_id ?>"><?= $status ?></td>
                    </tr>

                <?php




                  }
                }

                ?>

              </tbody>
            </table>
          </div>


        </div>

          



         






    
        </main>
        <div class="overlay"></div>
      </div>
      
    </div>
<!-- partial -->



 <?php include("../footer.php") ?>

</body>
<script>
  $(document).ready(function() {


  animateCounter('#projectCount',<?php echo json_encode($projectCount) ?>);
  animateCounter('#ticketCount',<?php echo json_encode($ticketCount) ?>);
  animateCounter('#clientCount',<?php echo json_encode($clientCount) ?>);
  animateCounter('#technicianCount',<?php echo json_encode($technicianCount) ?>);
  animateCounter('#adminCount',<?php echo json_encode($adminCount) ?>);

  function animateCounter(selector, value) {
    var element = $(selector);
    var start = 0;
    var end = value;
    var duration = 2000; // Animation duration in milliseconds
    var difference = end - start;
    var step = Math.ceil(duration / difference);
    var current = start;
    var interval = setInterval(function() {
      element.text(current);
      current += 1; // Increment by 1
      if (current >= end) {
        element.text(end);
        clearInterval(interval);
      } 



            }, step);
    element.addClass('animate');
          }

          
  $('.table_display').DataTable({
                "lengthMenu": [
                  [10, 25, 50, -1],
                  [10, 25, 50, "All"]
                ],
                "pageLength": 10,
                "order": [
                  [0, 'desc']
                ],

              });


});

</script>
</html>