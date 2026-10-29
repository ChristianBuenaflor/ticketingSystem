<?php 
include("../../includes/connections.php");
session_start();

if (!empty($_SESSION['logged_in'])) {
  if($_SESSION['role'] != 'super_admin'){
    header("location:../../user/dashboard/dashboard.php");
    exit;
  }
} else {
    header("location:../../index.php");
    die;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$dashboardLinks = [
  'projects' => '../projects/list.php',
  'tickets' => '../tickets/list.php',
  'clients' => '../users/list.php',
  'technicians' => '../users/list.php',
  'admins' => '../users/list.php',
  'categories' => '../../user/settings/ticket-category.php',
];

$counts = [
  'projects' => 'SELECT COUNT(*) AS total FROM projects',
  'tickets' => 'SELECT COUNT(*) AS total FROM tickets',
  'clients' => "SELECT COUNT(*) AS total FROM users WHERE role = 'client'",
  'technicians' => "SELECT COUNT(*) AS total FROM users WHERE role = 'technician'",
  'admins' => "SELECT COUNT(*) AS total FROM users WHERE role = 'admin'",
];
$dashboardCounts = [];
foreach ($counts as $key => $query) {
  $result = mysqli_query($con, $query);
  if (!$result) {
    throw new RuntimeException('Unable to load dashboard metrics.');
  }
  $dashboardCounts[$key] = (int) mysqli_fetch_assoc($result)['total'];
}

$taskStatusCounts = ['To do' => 0, 'In progress' => 0, 'Completed' => 0, 'Other' => 0];
$taskStatusResult = mysqli_query($con, 'SELECT status, COUNT(*) AS total FROM projects_task GROUP BY status');
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
$recentTasksResult = mysqli_query($con, "SELECT projects_task.task_id, projects_task.project_id, projects_task.task_title,
    projects_task.status, projects_task.date_created, projects.project_title
  FROM projects_task
  INNER JOIN projects ON projects.project_id = projects_task.project_id
  ORDER BY projects_task.task_id DESC
  LIMIT 6");
if (!$recentTasksResult) {
  throw new RuntimeException('Unable to load recent project tasks.');
}
$recentTasks = [];
while ($row = mysqli_fetch_assoc($recentTasksResult)) {
  $recentTasks[] = $row;
}
require_once __DIR__ . '/../../includes/recent_task_shares.php';
$recentTaskShares = loadRecentTaskShares($con, $recentTasks);
?>
<!DOCTYPE html>
<html lang="en" >
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
<?php include("../header.php") ?>
<link rel="stylesheet" href="../../links/dashboard-admin.css">

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

          <?php include(__DIR__ . '/../../includes/admin_dashboard_widgets.php'); ?>


          <div class="row mt-4 card shadow-sm">
          <div class="card-header d-flex align-items-center" style="justify-content: space-between; padding: 20px 16px; background-color:#fff; color:#092a2f; border-radius:0">
            <h6>Project overview</h6>
            <a class="dashboard-text-link" href="../projects/list.php">All projects <i class="ri-arrow-right-line" aria-hidden="true"></i></a>
             </div>
          <div class="card-body" id="project_results">

            <table class="table table_display table-bordered table-hover">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Title</th>
                  <th>Start date</th>
                  <th>Deadline</th>
                  <th>Progress</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $query = "SELECT * FROM projects";
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
                      <td title="<?=  number_format($avarageProgress, 0) . "%" ?>" class="td<?= $project_id ?>" style="width: 300px;">
                        <div style="height:8px;" class="progress" role="progressbar" aria-label="project progress" aria-valuenow="<?=  number_format($avarageProgress, 0) . "%" ?>" aria-valuemin="0" aria-valuemax="100">
                          <div class="progress-bar" style="width: <?=  number_format($avarageProgress, 0) . "%" ?>; background-color:rgb(140, 136, 102)"></div>
                        </div>
                      </td>
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