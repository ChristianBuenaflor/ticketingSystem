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


$quserP = "Select * from users_info where user_id = $user_id";
$resultuserP = mysqli_query($con, $quserP);
if (mysqli_num_rows($resultuserP) > 0) {
  while ($rowuP = $resultuserP->fetch_assoc()) {
    $userProfile = $rowuP['profile'];
    $userProfileType = $rowuP['profile_type'];
    if (empty($userProfile)) {
      $userProfile = file_get_contents('images/Default_pfp.png');
    }
  }
}




if (isset($_GET['ticketID'])) {
  $ticket_id = $_GET['ticketID'];

  $query = "SELECT * FROM tickets WHERE ticket_id = $ticket_id";
  $result = mysqli_query($con, $query);
  if (mysqli_num_rows($result) > 0) {
    while ($row = $result->fetch_assoc()) {
      $users_id = $row['user_id'];
      $ticket_title = $row['title'];
      $project_id = $row['category_id'];
      $ticket_description = $row['description'];
      $ticket_files = $row['files'];
      $ticket_technician = $row['technician'];
      $type_id = $row['type_id'];
      $ticket_created = $row['created'];
      $last_activity = $row['last_activity'];
      $ticket_status = $row['status'];


      $querys = "SELECT * FROM users WHERE user_id = $users_id";
      $results = mysqli_query($con, $querys);
      if (mysqli_num_rows($results) > 0) {
        while ($rows = $results->fetch_assoc()) {
          $roles = $rows['role'];
        }
      }

      $queTechnician = "SELECT * FROM users_info WHERE user_id = $ticket_technician";
      $resTechnician = mysqli_query($con, $queTechnician);
      if (mysqli_num_rows($resTechnician) > 0) {
        while ($rowTechnician = $resTechnician->fetch_assoc()) {
          $technicianName = $rowTechnician['first_name'] . ' ' . $rowTechnician['last_name'];
        }
      }



      if ($project_id != 0) {
        $qproject = "Select category from ticket_category where category_id = $project_id";
        $resultp = mysqli_query($con, $qproject);
        if (mysqli_num_rows($resultp) > 0) {
          while ($rowp = $resultp->fetch_assoc()) {
            $ticket_project = $rowp['category'];
          }
        }
      } else {
        $ticket_project = "none";
      }



      $qtype = "Select type_name from ticket_type where type_id = $type_id";
      $resultp = mysqli_query($con, $qtype);
      if (mysqli_num_rows($resultp) > 0) {
        while ($rowp = $resultp->fetch_assoc()) {
          $ticket_type = $rowp['type_name'];
        }
      }



      if (!empty($roles)) {

        if ($roles == 'super_admin') {
          $profile_dp = file_get_contents('images/Default_pfp.png');
          $profile_lname = 'Administrator';
          $profile_fname = '';
        } else {


          $qprofile = "Select * from users_info where user_id = $users_id";
          $resultqp = mysqli_query($con, $qprofile);
          if (mysqli_num_rows($resultqp) > 0) {
            while ($rowqp = $resultqp->fetch_assoc()) {
              $profile_lname = $rowqp['last_name'];
              $profile_fname = $rowqp['first_name'];
              $profile_dp = $rowqp['profile'];
              $dp_type = $rowqp['profile_type'];
              if (empty($profile_dp)) {
                $profile_dp = file_get_contents('images/Default_pfp.png');
              }
            }
          }
        }
      } else {
        $profile_dp = file_get_contents('images/Default_pfp.png');
        $profile_lname = 'Account';
        $profile_fname = 'Deleted';
      }
    }
  }
} else {
  header('location:list.php');
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>User - Ticket #<?= sprintf("%04d", $ticket_id); ?></title>
  <?php include("../header.php") ?>
  <link rel="stylesheet" href="magnific-popup/magnific-popup.css">
  <style>
    img.profiles {
      width: 55px;
      height: 55px;
      object-fit: cover;
      border-radius: 50%;
    }

    img.attached-files {
      width: 380px;
      height: 280px;
      object-fit: cover;
      margin-bottom: 12px;
    }

    .gallery:hover img {
      filter: grayscale(80%);
    }



    .gallery {
      width: 380px;
      position: relative;
    }



    .more-span {
      position: absolute;
      top: 0;
      right: 0;
      background-color: black;
      padding: 6px 12px;
      color: #fff;
      border-left: 5px solid rgb(140, 136, 102);
    }

    .download a,
    a.files {
      color: #092A2F;
    }

    .download a:hover {
      color: rgb(140, 136, 102);
    }

    .dropdown-menu {
      --bs-dropdown-link-active-bg: rgb(140, 136, 102);
    }


    .modal-content {
      max-height: 90vh;
      overflow: hidden;
    }

    .modal-body {
      overflow-y: auto;
      max-height: calc(90vh - 120px);
      /* Adjust the height to account for header and footer */
    }

    .modal-body::-webkit-scrollbar {
      width: 6px;
    }

    .modal-body::-webkit-scrollbar-track {
      background: #e0e0e0;
    }

    .modal-body::-webkit-scrollbar-thumb {
      background-color: rgb(140, 136, 102);
      border-radius: 10px;
      border: 2px solid rgb(140, 136, 102);
    }

    .modal-body::-webkit-scrollbar-thumb:hover {
      background-color: #092a2f;
      border: 2px solid #092a2f;
      cursor: pointer;
    }

    .form-check-input:checked {
      background-color: rgb(140, 136, 102);
      border-color: #092a2f;
    }

    .form-check,
    .form-check-input,
    .form-check-label {
      cursor: pointer;
    }

    .form-check-input:hover,
    .form-check-label:hover {
      background-color: rgb(140, 136, 102);
      color: #fff;
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


        <div class="row mt-4 mb-5 card shadow-sm">
          <div class="card-header d-flex" style="justify-content: space-between;<?php if ($user_role == 'client' && $ticket_status != 'Closed') { ?> padding-top:28px; padding-bottom:28px; <?php } ?>">
            <h6>Ticket #<?= sprintf("%04d", $ticket_id); ?> - <?= $ticket_title ?></h6>


            <?php
            if ($user_role != 'client') {
            ?>
              <div class="btn-group">
                <button type="button" class="btn btn-action" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="ri-equalizer-line"></i>&nbsp;&nbsp; Actions</button>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">

                  <li><a href="actions.php?ticketID=<?= $ticket_id ?>&usersID=<?= $users_id ?>" class="dropdown-item" type="button">
                      <?php if ($ticket_status == 'In progress') { ?>
                        <i style="color:rgb(140, 136, 102); font-size:.9rem" class="ri-close-circle-fill"></i> Mark as closed
                      <?php } else if ($ticket_status == 'Open') {
                      ?>
                        <i style="color:rgb(140, 136, 102); font-size:.9rem" class="ri-loader-2-line"></i> Mark as in progress
                      <?php
                      } else {
                      ?>
                        <i style="color:darkolivegreen; font-size:.9rem" class="ri-checkbox-circle-fill"></i> Mark as open
                      <?php
                      } ?>
                    </a></li>




                </ul>
              </div>
            <?php
            } else {
            ?>
            <?php 
            if($ticket_status == 'Closed')
            {
?>
<div id="evaluationInfo">
              <div style="display:flex; align-items:center;">
                  

                <?php
                $qChecks = "SELECT * FROM evaluation WHERE ticket_id = $ticket_id";
                $rChecks = mysqli_query($con, $qChecks);
                if (mysqli_num_rows($rChecks) > 0) {
                ?>
                <a style="font-size:20px;background-color:#d4edda; color:#155724; border:1px solid #c3e6cb; padding-left:6px; padding-right:5px; border-radius:50%" href="../evaluations/list.php"><i class="ri-file-check-line"></i></a>
                 
                <?php
                } else {
                ?>
                  <i style="font-size:20px;background-color:#fff3cd; color:#856404; border:1px solid #ffeeba; padding-left:6px; padding-right:6px; border-radius:50%" class="ri-file-warning-line"></i>
                <?php
                }
                ?>





                &nbsp;&nbsp;
                <button type="button" class="btn btn-action evaluateTechnician" data-bs-toggle="modal" data-bs-target="#evaluateTechnician" aria-expanded="false">
                  <i class="ri-survey-line"></i>&nbsp;&nbsp; Evaluate</button>
              
                </div>
                </div>
<?php
            }
            ?>
            
            <?php
            }
            ?>

          </div>
          <div class="card-body" id="ticket-results" style="padding-top:0">
            <div class="d-flex gap-4" style="padding: 14px 32px; border-bottom: 1px solid rgba(0,0,0,0.1);">
              <span>Status : <span class="badge" <?php if ($ticket_status == 'In progress') {
                                                    echo 'style="
        background-color: #FFF3CD;
        color: #664D03;
        border: solid 1px #664D0320;
        font-size: .6rem;"';
                                                  } else if ($ticket_status == 'Open') {
                                                    echo 'style="
                                                    background-color: #f2f2f2;
                                                    color: #0A3622;
                                                    border: solid 1px #0A362220;
                                                    font-size: .6rem;"';
                                                  } else {
                                                    echo "style='
          background-color: #D1E7DD;
          color: #0A3622;
          border: solid 1px #0A362220;
          font-size: .6rem;'";
                                                  } ?>><?= $ticket_status ?></span></span>
              <?php if ($ticket_project != "none") { ?><span>Category : <span><?= $ticket_project ?></span></span><?php } ?>
              <span>Created : <span><?= $ticket_created ?></span></span>
              <?php if ($ticket_status == 'Closed') { ?> <span>Closed : <span> <?php echo $last_activity; ?></span></span> <?php } ?>
              <span>Ticket type : <span><?= $ticket_type ?></span></span>
            </div>


            <!-- Commenting -->
            <div class="d-flex gap-4" style="padding: 14px 32px; border-bottom: 1px solid rgba(0,0,0,0.1);">
              <div class="d-flex gap-3" style="width: 100%;">
                <div>

                  <?php echo '<img class="profiles" src="data:' . $userProfileType . ';base64,' . base64_encode($userProfile) . '" alt="profile">'; ?>
                </div>
                <div style="width: 100%;">

                  <form id="ticket-comment-form">
                    <input type="hidden" name="users_id" value="<?= $user_id ?>">
                    <input type="hidden" name="comment_ticket_id" value="<?= $ticket_id ?>">
                    <div>
                      <textarea class="form-control form-control-sm focus-ring focus-ring-light comment" name="comment_textarea" id="commentTextarea" rows="4" placeholder="Write a comment..." autocomplete="off"></textarea>
                    </div>
                    <div class="modal-footer" style="justify-content: start; background-color: #f2f4f6; padding:8px 16px">
                      <div class="file-preview"></div>
                      <div style="display:flex; justify-content:space-between; width:100%;">
                        <div>
                          <button class="upload-file-btn btn comment" type="button"><i style="font-size:.9rem" class="ri-camera-fill"></i>&nbsp; Upload File</button>
                          <input style="display:none" name="comment_files[]" type="file" id="fileInput" multiple>
                        </div>
                        <div>

                          <button type="submit" class="btn btn-primary" style="padding:3px 9px"><i class="ri-send-plane-fill"></i> Post Comment</button>
                        </div>
                      </div>
                    </div>
                  </form>


                </div>

              </div>

            </div>
            <!-- Commenting -->




            <!-- other coment -->
            <div id="comments-results">

              <?php
              $qComments = "SELECT * FROM ticket_comments WHERE ticket_id = $ticket_id ORDER BY comment_id DESC";
              $resultC = mysqli_query($con, $qComments);
              if (mysqli_num_rows($resultC) > 0) {
                while ($rowC = $resultC->fetch_assoc()) {
                  $comment_id = $rowC['comment_id'];
                  $commenter_user_id = $rowC['user_id'];
                  $commenter_comment = $rowC['description'];
                  $commenter_files = $rowC['files'];
                  $comment_date = $rowC['date_stamp'];

                  $qrcommenter = "Select * from users where user_id = $commenter_user_id";
                  $rrCommenter = mysqli_query($con, $qrcommenter);
                  if (mysqli_num_rows($rrCommenter) > 0) {
                    while ($rowrCommenter = $rrCommenter->fetch_assoc()) {
                      $users_role = $rowrCommenter['role'];
                    }
                  }


                  $qcommenter = "Select * from users_info where user_id = $commenter_user_id";
                  $rCommenter = mysqli_query($con, $qcommenter);
                  if (mysqli_num_rows($rCommenter) > 0) {
                    while ($rowCommenter = $rCommenter->fetch_assoc()) {
                      $commenter_lname = $rowCommenter['last_name'];
                      $commenter_fname = $rowCommenter['first_name'];
                      $commenter_dp = $rowCommenter['profile'];
                      $commenter_dp_type = $rowCommenter['profile_type'];
                      if (empty($commenter_dp)) {
                        $commenter_dp = file_get_contents('images/Default_pfp.png');
                      }
                    }
                  } else {
                    $commenter_dp_type = 'image/*';
                    $commenter_dp = file_get_contents('images/Default_pfp.png');
                    $commenter_lname = 'Account';
                    $commenter_fname = 'Deleted';
                  }
              ?>
                  <div class="d-flex gap-4" style="padding: 14px 32px; border-bottom: 1px solid rgba(0,0,0,0.05); position:relative">
                    <div class="d-flex gap-3">
                      <div>
                        <?php echo '<img class="profiles" src="data:' . $commenter_dp_type . ';base64,' . base64_encode($commenter_dp) . '" alt="profile">'; ?>
                      </div>
                      <div class="d-block">
                        <div class="d-flex gap-3 mb-1">
                          <span><b><?= $commenter_fname . " " . $commenter_lname ?><?php if ($users_role == 'admin' || $users_role == 'super_admin') { ?> (admin) <?php } ?></b></span><span><?= $comment_date ?></span>

                          <?php
                          if ($user_role == 'admin' || $user_role == 'super_admin') {
                          ?>
                            <!-- Delete Button -->
                            <div class="btn-group dropstart" style="padding:0; position:absolute; top:0; right:12px">
                              <button type="button" class="btn" style="padding:0; border:0" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ri-more-2-line"></i>
                              </button>
                              <ul class="dropdown-menu mt-1" style="border-radius: 0; padding:0">
                                <a href="../delete.php?deleteID=<?= $comment_id ?>&from=comment&urlID=<?= $ticket_id ?>">
                                  <li style="font-size:.7rem; padding:6px 12px;" class="dropdown-item"><i style="font-weight: 1000; color:red" class="ri-delete-bin-line"></i> DELETE</li>
                                </a>
                              </ul>
                            </div>
                            <!-- Delete Button -->
                          <?php
                          }
                          ?>


                        </div>
                        <div class="mb-2">
                          <span><?= nl2br($commenter_comment) ?></span>
                        </div>
                        <div class="mb-2">

                          <div class="gallery">
                            <?php

                            if (!empty($commenter_files)) {
                              $files = explode(',', $commenter_files);
                              $comment_image_count = 0;
                              $comment_file_count = count($files);
                              $images = 0;

                              // Separate image and non-image files
                              $image_files = array();
                              $non_image_files = array();
                              foreach ($files as $file_id) {
                                $qfile = "SELECT * FROM `files` WHERE id=$file_id";
                                $fileresult = mysqli_query($con, $qfile);
                                $filerow = mysqli_fetch_assoc($fileresult);
                                if (strpos($filerow['file_type'], 'image/') === 0) {
                                  $comment_image_count++;
                                  $image_files[] = $filerow;
                                } else {
                                  $non_image_files[] = $filerow;
                                }
                              }

                              // Display image files first
                              foreach ($image_files as $file) {
                                $comment_file = $file['file_name'];
                                $comment_file_type = $file['file_type'];
                                $images++;
                                echo '<a ';
                                if ($images > 1) {
                                  echo 'class="d-none" ';
                                }
                                echo 'href="../../files/ticket/' . $comment_file . '"><img class="attached-files" src="../../files/ticket/' . $comment_file . '">';
                                if ($comment_image_count > 1) {
                            ?><span class="more-span">+<?= $comment_image_count - 1; ?> More</span><?php
                                                                                                  }
                                                                                                  echo '</a>';
                                                                                                }
















                                                                                                    ?>




                          </div>
                          <div class="commentfileList">
                            <?php
                              // Display non-image files
                              foreach ($non_image_files as $file) {
                                echo '<a target="_blank" class="files" href="../../files/ticket/' . $file['file_name'] . '">' . $file['file_name'] . '</a><br>';
                              }
                            ?>
                          </div>

                        </div>
                        <div class="d-flex" style="justify-content: space-between; width:380px">
                          <div><i style="font-size:.9rem;" class="ri-attachment-2"></i></div>
                          <div class="download"><a style="font-size:.6rem;" href="download.php?files=<?= urlencode($commenter_files) ?>&ticketID=0">Download <?= $comment_file_count ?> files</a></div>

                        </div>
                      <?php } else {
                      ?>
                      </div>
                    </div>
                  <?php
                            } ?>
                  </div>

            </div>

          </div>
      <?php

                }
              }
      ?>
        </div>
        <!-- other coment -->


        <!-- ticket description and post informations -->
        <div class="d-flex gap-4" style="padding: 14px 32px; border-bottom: 1px solid rgba(0,0,0,0.1);">
          <div class="d-flex gap-3">
            <div>
              <?php echo '<img class="profiles" src="data:$dp_type;base64,' . base64_encode($profile_dp) . '" alt="profile">'; ?>
            </div>
            <div class="d-block">
              <div class="d-flex gap-3 mb-1">
                <span><b><?= $profile_fname . " " . $profile_lname ?></b></span><span><?= $ticket_created ?></span>
              </div>
              <div class="mb-2">
                <span><?= nl2br($ticket_description) ?></span>
              </div>
              <div class="mb-2">

                <div class="gallery">

                  <?php
                  if (!empty($ticket_files)) {
                    $files = explode(',', $ticket_files);
                    $image_count = 0;
                    $file_count = count($files);
                    $images = 0;

                    // Separate image and non-image files
                    $image_files = array();
                    $non_image_files = array();
                    foreach ($files as $file_id) {
                      $qfile = "SELECT * FROM `files` WHERE id=$file_id";
                      $fileresult = mysqli_query($con, $qfile);
                      $filerow = mysqli_fetch_assoc($fileresult);
                      if (strpos($filerow['file_type'], 'image/') === 0) {
                        $image_count++;
                        $image_files[] = $filerow;
                      } else {
                        $non_image_files[] = $filerow;
                      }
                    }

                    // Display image files first
                    foreach ($image_files as $file) {
                      $ticket_file = $file['file_name'];
                      $ticket_file_type = $file['file_type'];
                      $images++;
                      echo '<a ';
                      if ($images > 1) {
                        echo 'class="d-none" ';
                      }
                      echo 'href="../../files/ticket/' . $ticket_file . '"><img class="attached-files" src="../../files/ticket/' . $ticket_file . '">';
                      if ($image_count > 1) {
                  ?><span class="more-span">+<?= $image_count - 1; ?> More</span><?php
                                                                                }
                                                                                echo '</a>';
                                                                              }



                                                                                  ?>


                </div>
                <div>
                  <?php
                    // Display non-image files
                    foreach ($non_image_files as $file) {
                      echo '<a target="_blank" class="files" href="../../files/ticket/' . $file['file_name'] . '">' . $file['file_name'] . '</a><br>';
                    }
                  ?>
                </div>

              </div>
              <div class="d-flex" style="justify-content: space-between; width:380px">
                <div><i style="font-size:.9rem;" class="ri-attachment-2"></i></div>
                <div class="download"><a style="font-size:.6rem;" href="download.php?files=<?= urlencode($ticket_files) ?>&ticketID=<?= urlencode($ticket_id) ?>">Download <?= $file_count ?> files</a></div>

              </div>
            <?php } else {
            ?>
            </div>
          </div>
        <?php
                  } ?>
        </div>













    </div>

  </div>
  <!-- ticket description and post informations -->

  </div>


  </div>
















  </main>
  <div class="overlay"></div>
  </div>

  </div>
  <!-- partial -->






  <div class="modal fade" id="evaluateTechnician" data-bs-backdrop="static" tabindex="-1" data-bs-keyboard="false" aria-labelledby="evaluateTechnicianLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title" id="evaluateTechnicianLabel">Assess <span style="font-weight:bold" id="technicianName"><?= $technicianName ?></span> work.</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="evaluateTechnicianForm">
          <input type="hidden" name="ticketId" id="ticketId" value="<?= $ticket_id ?>">
          <input type="hidden" name="ticketTechnician" id="ticketTechnician" value="<?= $ticket_technician ?>">
          <input type="hidden" name="ticketClient" id="ticketClient" value="<?= $user_id ?>">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">• Resolution :<br>How would you rate the technician's resolution of your issue?</label>
              <div class="form-check">
                <input class="form-check-input focus-ring focus-ring-dark" type="radio" name="solutionSatisfaction" value="1" id="solutionSatisfaction1">
                <label class="form-check-label" for="solutionSatisfaction1">[ 1 ] Highly Acceptable</label>
              </div>
              <div class="form-check">
                <input class="form-check-input focus-ring focus-ring-dark" type="radio" name="solutionSatisfaction" value="2" id="solutionSatisfaction2">
                <label class="form-check-label" for="solutionSatisfaction2">[ 2 ] Acceptable</label>
              </div>
              <div class="form-check">
                <input class="form-check-input focus-ring focus-ring-dark" type="radio" name="solutionSatisfaction" value="3" id="solutionSatisfaction3">
                <label class="form-check-label" for="solutionSatisfaction3">[ 3 ] Moderately Acceptable</label>
              </div>
              <div class="form-check">
                <input class="form-check-input focus-ring focus-ring-dark" type="radio" name="solutionSatisfaction" value="4" id="solutionSatisfaction4">
                <label class="form-check-label" for="solutionSatisfaction4">[ 4 ] Unacceptable</label>
              </div>
              <div class="form-check">
                <input class="form-check-input focus-ring focus-ring-dark" type="radio" name="solutionSatisfaction" value="5" id="solutionSatisfaction5">
                <label class="form-check-label" for="solutionSatisfaction5">[ 5 ] Highly Unacceptable</label>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">• Customer Satisfaction :<br>How do you rate your satisfaction with the technician's work?</label>
              <div class="form-check">
                <input class="form-check-input focus-ring focus-ring-dark" type="radio" name="customerSatisfaction" value="1" id="customerSatisfaction1">
                <label class="form-check-label" for="customerSatisfaction1">[ 1 ] Highly Acceptable</label>
              </div>
              <div class="form-check">
                <input class="form-check-input focus-ring focus-ring-dark" type="radio" name="customerSatisfaction" value="2" id="customerSatisfaction2">
                <label class="form-check-label" for="customerSatisfaction2">[ 2 ] Acceptable</label>
              </div>
              <div class="form-check">
                <input class="form-check-input focus-ring focus-ring-dark" type="radio" name="customerSatisfaction" value="3" id="customerSatisfaction3">
                <label class="form-check-label" for="customerSatisfaction3">[ 3 ] Moderately Acceptable</label>
              </div>
              <div class="form-check">
                <input class="form-check-input focus-ring focus-ring-dark" type="radio" name="customerSatisfaction" value="4" id="customerSatisfaction4">
                <label class="form-check-label" for="customerSatisfaction4">[ 4 ] Unacceptable</label>
              </div>
              <div class="form-check">
                <input class="form-check-input focus-ring focus-ring-dark" type="radio" name="customerSatisfaction" value="5" id="customerSatisfaction5">
                <label class="form-check-label" for="customerSatisfaction5">[ 5 ] Highly Unacceptable</label>
              </div>
            </div>

           

            <div class="mb-3">
              <label for="comments" class="form-label">Comments or Suggestions:</label>
              <textarea class="form-control focus-ring focus-ring-dark" style="font-size: 12px;" id="comments" name="comments" rows="3"></textarea>
            </div>
          </div>
          <div class="modal-footer" style="justify-content: start;">
            <div style="display:flex; justify-content:space-between; width:100%; align-items:center">
              <div>
                <span id="invalid-answers" class="d-none alert alert-danger" style="border-radius:3px; padding:3px 12px; margin:0; line-height:0;"><i style="font-size:12px" class="ri-error-warning-line"></i>&nbsp;&nbsp;ANSWER ALL QUESTIONS</span>
              </div>
              <div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i style="font-weight: 1000;" class="ri-close-fill"></i> Close</button>
                <button id="addEvaluationBtn" type="submit" class="btn btn-primary"><i class="ri-checkbox-circle-fill"></i> Save</button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>











  <div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header">
        <img style="height:.9rem; border-radius:50%" src="images/success.png" alt="success">
        <strong style="font-size:.8rem" class="me-auto">&nbsp;SUCCESS</strong>
        <small>Just now</small>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body" style="font-size: .8rem;">
        Added Successfully! Added Evaluation for : <span><?= $technicianName ?></span>
      </div>
    </div>
  </div>








  <?php include("../footer.php") ?>
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>

  <!-- Magnific Popup core JS file -->
  <script src="magnific-popup/jquery.magnific-popup.js"></script>
  <script src="magnific-popup/jquery.magnific-popup.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.7.1/jszip.min.js"></script>
</body>

<script>
  $(document).ready(function() {
    $('#evaluateTechnicianForm').submit(function(event) {
      // Prevent default form submission
      event.preventDefault();

      const toastLiveExample = document.getElementById('liveToast')

      function showToast() {
        const toastBootstrap = new bootstrap.Toast(toastLiveExample)
        toastBootstrap.show()
      }

      var btn = $('#addEvaluationBtn');
      btn.html('<i class="ri-loader-2-line"></i> Loading').attr('disabled', true);
      setTimeout(function() {
        btn.html('<i class="ri-checkbox-circle-fill"></i> Save').attr('disabled', false);
      }, 1000);



      // Get form data
      var formData = new FormData(this);
      formData.append("evaluateTechnicianForm", true);

      // Send Ajax request
      $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {

          var res = jQuery.parseJSON(response);


          if (res.status == 100) {
            $('#evaluateTechnicianForm')[0].reset();


            $('#evaluateTechnician').modal('hide');

            location.reload('view.php');



          } else if (res.status == 101) {
            $('#invalid-answers').addClass('d-none');
            setTimeout(function() {
              $('#invalid-answers').removeClass('d-none');
              setTimeout(function() {
                $('#invalid-answers').addClass('d-none');
              }, 1000);
            }, 100);
          }
        },
        error: function(xhr, status, error) {
          // Handle error
          alert('Error saving ticket: ' + error);
        }
      });
    });
  });
</script>


<script>
  $(document).ready(function() {
    $('#ticket-comment-form').submit(function(event) {
      // Prevent default form submission
      event.preventDefault();



      // Get form data
      var formData = new FormData(this);
      formData.append("post-ticket-comment", true);

      // Send Ajax request
      $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {

          var res = jQuery.parseJSON(response);


          if (res.status == 100) {
            $('#ticket-comment-form')[0].reset();
            $('.file-preview').empty();
            location.reload();

          } else if (res.status == 101) {

          }
        },
        error: function(xhr, status, error) {
          // Handle error
          alert('Error saving ticket: ' + error);
        }
      });
    });
  });
</script>


<script>
  $(document).ready(function() {
    $('.gallery').each(function() {
      $(this).magnificPopup({
        delegate: 'a',
        type: 'image',
        gallery: {
          enabled: true,
          preload: [0, 2],
          navigateByImgClick: true,
          arrowMarkup: '<button title="%title%" type="button" class="mfp-arrow mfp-arrow-%dir%"></button>',
          tPrev: 'Previous',
          tNext: 'Next',
          tCounter: '<span class="mfp-counter">%curr% of %total%</span>'
        },

      });
    });
  });
</script>





<script>
  $(document).ready(function() {
    // Function to handle file upload
    $('.upload-file-btn').click(function() {
      $('#fileInput').click();
    });

    $('#fileInput').change(function() {
      var files = this.files;

      for (var i = 0; i < files.length; i++) {
        (function(file) { // Use closure to capture the correct file reference
          var reader = new FileReader();

          reader.onload = function(e) {
            var filePreview = $('<div style="margin-bottom:6px;" class="file-preview"></div>');
            var removeButton = $('<span class="remove-file">&times;</span>');

            if (file.type.match('image.*')) {
              var img = $('<img src="' + e.target.result + '">');
              filePreview.append(img);
            } else {
              var iconImg = $('<img src="' + getFileIcon(file.type) + '">'); // Get default icon based on file type
              filePreview.append(iconImg);
            }

            filePreview.append(removeButton);
            $('.modal-footer').prepend(filePreview);

            removeButton.click(function() {
              // Remove file preview
              filePreview.remove();

              // Remove the corresponding file from the file input
              var indexToRemove = Array.from($('#fileInput').prop('files')).indexOf(file);
              if (indexToRemove !== -1) {
                var filesArray = Array.from($('#fileInput').prop('files'));
                filesArray.splice(indexToRemove, 1);
                $('#fileInput').prop('files', new FileListWrapper(filesArray));
              }
            });
          };

          reader.readAsDataURL(file);
        })(files[i]); // Pass the current file to the closure
      }
    });
  });

  // Function to get default icon based on file type
  function getFileIcon(fileType) {
    // Define default icons for different file types
    var defaultIcons = {
      'application/pdf': 'images/pdf-icon.png', // Example: Provide mapping between file types and default icons
      'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'images/word-icon.png',
      'text/plain': 'images/txt-icon.png',
      // Add more mappings as needed
    };
    // Return default icon corresponding to the file type, or a generic default icon if not found
    return defaultIcons[fileType] || 'images/default-icon.png';
  }

  // Helper function to wrap files array into FileList object
  function FileListWrapper(array) {
    var fileList = Object.create(FileList.prototype);
    Object.defineProperty(fileList, 'length', {
      value: array.length
    });
    array.forEach(function(file, index) {
      Object.defineProperty(fileList, index, {
        value: file
      });
    });
    return fileList;
  }
</script>



</html>