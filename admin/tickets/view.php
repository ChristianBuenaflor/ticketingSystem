<?php
include("../../includes/connections.php");
session_start();

if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

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

$quserP = "Select * from users where user_id = $user_id";
$resultuserP = mysqli_query($con, $quserP);
if (mysqli_num_rows($resultuserP) > 0) {
  while ($rowuP = $resultuserP->fetch_assoc()) {
    $userRole = $rowuP['role'];
  }
}

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
      $project_id = $row['project_id'];
      $ticket_description = $row['description'];
      $ticket_files = $row['files'];
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



if($project_id!=0)
{
  $qproject = "Select project_title from projects where project_id = $project_id";
  $resultp = mysqli_query($con, $qproject);
  if (mysqli_num_rows($resultp) > 0) {
    while ($rowp = $resultp->fetch_assoc()) {
      $ticket_project = $rowp['project_title'];
    }
  }
}else{
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
          <div class="card-header d-flex" style="justify-content: space-between;">
            <h6>Ticket #<?= sprintf("%04d", $ticket_id); ?> - <?= $ticket_title ?></h6>


            <div class="btn-group">
              <button type="button" class="btn btn-action" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="ri-equalizer-line"></i>&nbsp;&nbsp; Actions</button>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">

                <li><form method="post" action="actions.php" class="m-0">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                  <input type="hidden" name="ticketID" value="<?= (int) $ticket_id ?>">
                  <button class="dropdown-item" type="submit">
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
                  </button>
                </form></li>




              </ul>
            </div>

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
              <?php if($ticket_project!="none"){ ?><span>Project : <span><?= $ticket_project ?></span></span><?php }?>
              <span>Created : <span><?= $ticket_created ?></span></span>
              <?php if ($ticket_status == 'Closed') { ?> <span>Closed : <span> <?php echo $last_activity; ?></span></span> <?php } ?>
              <span>Ticket type : <span><?= $ticket_type ?></span></span>
            </div>

            <?php 
            if($userRole!='super_admin')
            {
            ?>

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
<?php } ?>



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
                  }else{
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
                          <span><b><?= $commenter_fname . " " . $commenter_lname ?></b></span><span><?= $comment_date ?></span>

                          <?php
                          if ($user_id == $commenter_user_id || $userRole == 'super_admin') {
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
                                  $comment_image_count ++;
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
                                 echo 'href="file.php?fileID=' . (int) $file['id'] . '&amp;ticketID=' . (int) $ticket_id . '&amp;inline=1" target="_blank" rel="noopener"><img class="attached-files" src="file.php?fileID=' . (int) $file['id'] . '&amp;ticketID=' . (int) $ticket_id . '&amp;inline=1">';
                                 if ($comment_image_count > 1) {
                                     ?><span class="more-span">+<?= $comment_image_count -1 ; ?> More</span><?php
                                 }
                                 echo '</a>';
                             }
                         
                          
    













                          ?>




                          </div>
                          <div class="commentfileList">
                            <?php 
                               // Display non-image files
                               foreach ($non_image_files as $file) {
                                echo '<a target="_blank" rel="noopener" class="files" href="file.php?fileID=' . (int) $file['id'] . '&amp;ticketID=' . (int) $ticket_id . '">' . htmlspecialchars($file['file_name'], ENT_QUOTES, 'UTF-8') . '</a><br>';
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
                        $image_count ++;
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
                      echo 'href="file.php?fileID=' . (int) $file['id'] . '&amp;ticketID=' . (int) $ticket_id . '&amp;inline=1" target="_blank" rel="noopener"><img class="attached-files" src="file.php?fileID=' . (int) $file['id'] . '&amp;ticketID=' . (int) $ticket_id . '&amp;inline=1">';
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
                        echo '<a target="_blank" rel="noopener" class="files" href="file.php?fileID=' . (int) $file['id'] . '&amp;ticketID=' . (int) $ticket_id . '">' . htmlspecialchars($file['file_name'], ENT_QUOTES, 'UTF-8') . '</a><br>';
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

  <?php include("../footer.php") ?>
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>

  <!-- Magnific Popup core JS file -->
  <script src="magnific-popup/jquery.magnific-popup.js"></script>
  <script src="magnific-popup/jquery.magnific-popup.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.7.1/jszip.min.js"></script>
</body>

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