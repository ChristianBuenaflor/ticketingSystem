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
$isAdminDashboard = in_array($user_role, ['admin', 'super_admin'], true);
$dashboardLinks = [
  'projects' => '../projects/list.php',
  'tickets' => '../tickets/list.php',
  'clients' => '../users/clients.php',
  'technicians' => '../users/technicians.php',
  'admins' => '../users/admins.php',
  'categories' => '../settings/ticket-category.php',
  'profile' => '../settings/my-profile.php',
];

$projectCount = 0;
$ticketCount = 0;
$clientCount = 0;
$technicianCount = 0;
$adminCount = 0;
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
    $projectCount = (int) $row['projectCount'];
}

if($user_role == 'technician')
{
  $sqlp = "SELECT COUNT(ticket_id) AS ticketCount FROM tickets where technician = $user_id";
  $resultp = $con->query($sqlp);
  
  if ($resultp->num_rows > 0) {
      $rowp = $resultp->fetch_assoc();
      $ticketCount = (int) $rowp['ticketCount'];
  }
}else if($user_role == 'client')
{
  $sqlp = "SELECT COUNT(ticket_id) AS ticketCount FROM tickets where user_id = $user_id";
  $resultp = $con->query($sqlp);
  
  if ($resultp->num_rows > 0) {
      $rowp = $resultp->fetch_assoc();
      $ticketCount = (int) $rowp['ticketCount'];
  }
}else{

  $sqltech = "SELECT COUNT(ticket_id) AS ticketCount FROM tickets";
  $resulttech = $con->query($sqltech);
  
  if ($resulttech->num_rows > 0) {
      $rowtech = $resulttech->fetch_assoc();
      $ticketCount = (int) $rowtech['ticketCount'];
  }

}


$sqlu = "SELECT COUNT(user_id) AS clientCount FROM users where role = 'client'";
$resultu = $con->query($sqlu);

if ($resultu->num_rows > 0) {
    $rowu = $resultu->fetch_assoc();
    $clientCount = (int) $rowu['clientCount'];
}

$sqlt = "SELECT COUNT(user_id) AS technicianCount FROM users where role = 'technician'";
$resultt = $con->query($sqlt);

if ($resultt->num_rows > 0) {
    $rowt = $resultt->fetch_assoc();
    $technicianCount = (int) $rowt['technicianCount'];
}

$sqla = "SELECT COUNT(user_id) AS adminCount FROM users where role = 'admin'";
$resulta = $con->query($sqla);

if ($resulta->num_rows > 0) {
    $rowa = $resulta->fetch_assoc();
    $adminCount = (int) $rowa['adminCount'];
}

$taskStatusCounts = ['To do' => 0, 'In progress' => 0, 'Completed' => 0, 'Other' => 0];

$taskQuery = 'SELECT projects_task.status, COUNT(*) AS total FROM projects_task';
if (!$isAdminDashboard) {
  $taskQuery .= " INNER JOIN project_shares ON project_shares.project_id = projects_task.project_id WHERE project_shares.user_id = $user_id";
}
$taskQuery .= ' GROUP BY projects_task.status';
$taskStatusResult = mysqli_query($con, $taskQuery);
if (!$taskStatusResult) {
  throw new RuntimeException('Unable to load project task metrics.');
}
while ($row = mysqli_fetch_assoc($taskStatusResult)) {
  $status = strtolower(trim($row['status']));
  if ($status === 'to do') {
    $taskStatusCounts['To do'] += (int) $row['total'];
  } elseif ($status === 'in progress') {
    $taskStatusCounts['In progress'] += (int) $row['total'];
  } elseif ($status === 'completed') {
    $taskStatusCounts['Completed'] += (int) $row['total'];
  } else {
    $taskStatusCounts['Other'] += (int) $row['total'];
  }
}
$recentTasksQuery = "SELECT projects_task.task_id, projects_task.project_id, projects_task.task_title,
    projects_task.status, projects_task.date_created, projects.project_title
  FROM projects_task
  INNER JOIN projects ON projects.project_id = projects_task.project_id";
if (!$isAdminDashboard) {
  $recentTasksQuery .= " INNER JOIN project_shares ON project_shares.project_id = projects_task.project_id
    WHERE project_shares.user_id = $user_id";
}
$recentTasksQuery .= ' ORDER BY projects_task.task_id DESC LIMIT 6';
$recentTasksResult = mysqli_query($con, $recentTasksQuery);
if (!$recentTasksResult) {
  throw new RuntimeException('Unable to load recent project tasks.');
}
$recentTasks = [];
while ($row = mysqli_fetch_assoc($recentTasksResult)) {
  $recentTasks[] = $row;
}
require_once __DIR__ . '/../../includes/recent_task_shares.php';
$recentTaskShares = loadRecentTaskShares($con, $recentTasks);

$dashboardCounts = [
  'projects' => $projectCount,
  'tickets' => $ticketCount,
  'clients' => $clientCount,
  'technicians' => $technicianCount,
  'admins' => $adminCount,
];



?>
<!DOCTYPE html>
<html lang="en" >
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
<?php include("../header.php") ?>
<link rel="stylesheet" href="../../links/dashboard-admin.css">
  </head>
  <body class="admin-dashboard-page">
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

          <?php include(__DIR__ . '/../../includes/admin_dashboard_widgets.php'); ?>
        
        











          <div class="row mt-4 card shadow-sm">
          <div class="card-header d-flex align-items-center" style="justify-content: space-between; padding: 20px 16px; background-color:<?= $isAdminDashboard ? '#fff' : '#a60212' ?>; color:<?= $isAdminDashboard ? '#092a2f' : '#fff' ?>; border-radius:0">
            <h6><?= $isAdminDashboard ? 'Project overview' : 'Your projects' ?></h6>
            <a class="dashboard-text-link" href="<?= htmlspecialchars($dashboardLinks['projects'], ENT_QUOTES, 'UTF-8') ?>">View projects <i class="ri-arrow-right-line" aria-hidden="true"></i></a>
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