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



?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>User - Projects</title>
  <?php include("../header.php") ?>
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css">
  
  <style>
    .dropdown-menu {
      --bs-dropdown-link-active-bg: rgb(140, 136, 102);
    }

    .user-profiles {
      width: 50px;
      height: 50px;
      object-fit: cover;
      border-radius: 50%;
    }

    table.dataTable tbody td {
      vertical-align: middle;
    }

     /* datepicker */
     .ui-state-highlight,
        .ui-widget-content .ui-state-highlight,
        .ui-widget-header .ui-state-highlight {
            border: 1px solid rgb(140, 136, 102);
            background: rgb(140, 136, 102);
            color: #fff;
        }

        .ui-state-active,
        .ui-widget-content .ui-state-active,
        .ui-widget-header .ui-state-active,
        a.ui-button:active,
        .ui-button:active,
        .ui-button.ui-state-active:hover {
            border: 1px solid #092a2f;
            background: #092a2f;
            font-weight: normal;
            color: #ffffff;
        }

        .ui-corner-all,
        .ui-corner-bottom,
        .ui-corner-right,
        .ui-corner-br {

            font-size: .8rem;
        }

        .ui-datepicker select.ui-datepicker-month,
        .ui-datepicker select.ui-datepicker-year {
            border: 1px solid rgba(0, 0, 0, 0.1);
            padding: 3px 6px;
            margin: 2px;
        }
.btn-add{
    color:#a60212;
    background-color:#f5ab2c;
}
.btn-add:hover{
    color:#f5ab2c;
    background-color:#a60212 !important;
}
.btn-primary{
    color:#f5ab2c !important;
    background-color:#f5ab2c !important;
    
}
.btn-primary{
     color:#a60212;
    background-color:#a60212 !important;
    
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


        <div class="row mt-4 card shadow-sm">
          <div class="card-header d-flex" style="justify-content: space-between;background-color:
#a60212;color:#ffffff; <?php
                                    if($user_role != 'super_admin'|| $user_role != 'admin')
                                    {
                                    ?> padding-top:28px; padding-bottom:28px; <?php } ?>">
            <h6>User - Projects</h6>
            <?php 
            if($user_role == 'super_admin' || $user_role == 'admin'){
            ?>
            <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addProjectM"><i class="ri-add-circle-fill"></i> Add Project</button>
            <?php 
          }
            ?>
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
                      <td class="td<?= $project_id ?>"><a href="view.php?project=<?= $project_id ?>"><?= $project_title ?></a></td>
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
                      <td style="width: 100px;" class="position-relative td<?= $project_id ?>"><?= $status ?>

                      <?php if($user_role == 'super_admin' || $user_role == 'admin'){ ?>
                        <!-- Delete Button -->
                        <div class="btn-group dropstart" style="padding:0; position:absolute; top:4px; right:12px">
                          <button type="button" class="btn" style="padding:0; border:0" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ri-more-2-line"></i>
                          </button>
                          <ul class="dropdown-menu mt-1" style="border-radius: 0; padding:0">

                            <a href="#" class="updateProject" data-project-id='<?= $project_id ?>'>
                              <li style="font-size:.7rem; padding:6px 12px;" class="dropdown-item"><i style="font-weight: 1000; color:darkorange" class="ri-file-edit-line"></i> UPDATE PROJECT</li>
                            </a>

                            <a href="../delete.php?deleteID=<?= $project_id ?>&from=projects">
                              <li style="font-size:.7rem; padding:6px 12px;" class="dropdown-item"><i style="font-weight: 1000; color:darkred" class="ri-chat-delete-line"></i> DELETE PROJECT</li>
                            </a>

                          </ul>
                        </div>
                        <?php 
                      }
                        ?>
                        <!-- Delete Button -->
                      </td>
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

  <div class="modal fade" id="addProjectM" data-bs-backdrop="static" tabindex="-1" data-bs-keyboard="false" aria-labelledby="addProjectMLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title" id="addProjectMLabel">Add Project</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id='add-projects'>
          <input type="hidden" name="m_projectId" id="m_projectId">
          <div class="modal-body">
            <div class="row">
              <div class="col-3">
                <label class='form-label'>Title</label>
              </div>
              <div class="col">
                <input required name="project_title" id="projectTitle" autocomplete="off" placeholder="Project Title" class="form-inputs form-control form-control-sm focus-ring focus-ring-light" type="text">
              </div>
            </div>
            <!-- -->
            <div class="row mt-2">
              <div class="col-3" style="display:flex;align-self: start;">
                <label class='form-label'>Description</label>
              </div>
              <div class="col">
                <textarea class="form-control form-control-sm focus-ring focus-ring-light" name="project_description" id="projectDescription" cols="30" rows="4" placeholder="Description" autocomplete="off"></textarea>
              </div>
            </div>
            <div class="row mt-2">
              <div class="col-3">
                <label class='form-label'>Start Date</label>
              </div>
              <div class="col">
              <input name="start_date" id="startDate" autocomplete="off" placeholder="MM/DD/YYYY" class="form-inputs form-control form-control-sm focus-ring focus-ring-light" type="text">
              </div>
            </div>

            <div class="row mt-2">
              <div class="col-3">
                <label class='form-label'>Deadline</label>
              </div>
              <div class="col">
              <input name="deadline" id="deadline" autocomplete="off" placeholder="MM/DD/YYYY" class="form-inputs form-control form-control-sm focus-ring focus-ring-light" type="text">
              </div>
            </div>


            <div class="row mt-2">
              <div class="col-3">
                <label class='form-label'>Status</label>
              </div>
              <div class="col">
                <select name="project_status" id="projectStatus" class="select_projectStatus selectpicker">
                  <option value="">-</option>
                  <option selected value="Open">Open</option>
                  <option value="Closed">Closed</option>
                </select>
              </div>
            </div>



          </div>
          <div class="modal-footer" style="justify-content: start;">
          <div class="file-preview"></div>
            <div style="display:flex; justify-content:space-between; width:100%">
              <div>
              <button class="upload-file-btn btn" type="button"><i style="font-size:.9rem" class="ri-camera-fill"></i>&nbsp; Upload File</button>
                <input style="display:none" name="project_files[]" type="file" id="fileInput" multiple>

              </div>
              <div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i style="font-weight: 1000;" class="ri-close-fill"></i> Close</button>
                <button id="addProjectBtn" type="submit" class="btn btn-primary"><i class="ri-checkbox-circle-fill"></i> Save</button>
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
        <img style="height:.9rem; border-radius:50%" src="../tickets/images/success.png" alt="success">
        <strong style="font-size:.8rem" class="me-auto">&nbsp;SUCCESS</strong>
        <small>Just now</small>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body" style="font-size: .8rem;">
        Added Successfully! Added project ID : <span id="added-project-id"></span>
      </div>
    </div>
  </div>





  <?php include("../footer.php") ?>

  <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script>
</body>




<script>
  $(document).ready(function() {
    // Listen for the modal's hidden.bs.modal event
    $('#addProjectM').on('hidden.bs.modal', function(e) {

      $('#addProjectBtn').html('<i class="ri-checkbox-circle-fill"></i> Save');
      // Reset the form or input values here
      $('#m_projectId').val('');
      $('#projectTitle').val('');
      $('#projectDescription').val('');
      $('.file-preview').empty();
      $('#startDate').val('');
      $('#deadline').val('');
      $('#projectStatus').val('Open').trigger('change'); // Reset Select2 dropdown


    });
  });
</script>

<script>
    $(function() {
        $("#startDate").datepicker({
            showOtherMonths: true,
            selectOtherMonths: true,
            dateFormat: "mm/dd/yy",
            changeMonth: true,
            changeYear: true,
            yearRange: "2000:2100"
        });
        $("#startDate").datepicker();
        $("#startDate").datepicker("widget").addClass("custom-datepicker");
        $("#startDate").on("input", function() {
            var inputDate = $(this).val();
            var currentDate = $.datepicker.formatDate("mm/dd/yy", new Date());
            if (!isValidDate(inputDate)) {
                $(this).val(currentDate);
            }
        });

        function isValidDate(dateString) {
            var regex = /^\d{1,2}-\d{1,2}-\d{4}$/;
            return regex.test(dateString);
        }
    });
</script>
<script>
    $(function() {

        $("#deadline").datepicker({
            showOtherMonths: true,
            selectOtherMonths: true,
            dateFormat: "mm/dd/yy", // Date format
            changeMonth: true,
            changeYear: true,
            yearRange: "2000:2100"
        });



        $("#deadline").datepicker();
        $("#deadline").datepicker("widget").addClass("custom-datepicker");

        // Listen for input event on the datepicker input field
        $("#deadline").on("input", function() {
            var inputDate = $(this).val();
            var currentDate = new Date(); // Get current date
            currentDate.setDate(currentDate.getDate() + 1); // Add one day

            // Format currentDate as mm/dd/yy
            var currentDate = $.datepicker.formatDate("mm/dd/yy", currentDate);

            // Validate input date
            if (!isValidDate(inputDate)) {
                // Set input value to current date
                $(this).val(currentDate);
            }
        });

        // Function to validate date format
        function isValidDate(dateString) {
            var regex = /^\d{1,2}-\d{1,2}-\d{4}$/;
            return regex.test(dateString);
        }



    });
</script>

<script>
  $(document).on('click', '.updateProject', function(e) {
    var project_id = $(this).data('project-id');

    $('#addProjectBtn').html('<i class="ri-checkbox-circle-fill"></i> Update');

    console.log("Project ID:", project_id);
    $.ajax({
      type: "GET",
      url: "ajax.php?updateid=" + project_id,

      success: function(response) {

        var res = jQuery.parseJSON(response);

        if (res.status == 200) {

          $('#m_projectId').val(res.data.project_id);
          $('#projectTitle').val(res.data.project_title);
          $('#projectDescription').val(res.data.project_description);
          $('#startDate').val(res.start_date);
          $('#deadline').val(res.deadline);
          $('#projectStatus').val(res.data.status).trigger('change');
          $('#addProjectM').modal('show');
        }


      }
    });


  });
</script>

<script>
  $(document).ready(function() {
    // Function to set z-index and focus on select2 search input


    // Function to ensure focus on modal's first input when shown
    $('.modal').on('shown.bs.modal', function() {
      $(this).find('input[type="text"]').first().focus();
    });


    $('.selectpicker').on('select2:open', function() {
      $('.select2-dropdown').css('z-index', 9999);
      $(this).siblings('.select2').find('.select2-search__field').focus();
    });


  });
</script>

<script>
  $(document).ready(function() {
    $('.form-inputs').on('input', function() {
      var inputValue = $(this).val().trim();
      if (inputValue.length > 0) {
        $(this).removeClass('is-invalid');
      } else {
        $(this).addClass('is-invalid');
      }
    });






  });
</script>

<script>
  $(document).ready(function() {
    $('#add-projects').submit(function(event) {
      // Prevent default form submission
      event.preventDefault();



      const toastLiveExample = document.getElementById('liveToast')


      function showToast() {
        const toastBootstrap = new bootstrap.Toast(toastLiveExample)
        toastBootstrap.show()
      }



      var btn = $('#addProjectBtn');
      btn.html('<i class="ri-loader-2-line"></i> Loading').attr('disabled', true);
      setTimeout(function() {
        btn.html('<i class="ri-checkbox-circle-fill"></i> Save').attr('disabled', false);
      }, 1000);



      // Get form data
      var formData = new FormData(this);
      formData.append("add-project", true);




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
            $('#add-projects')[0].reset();
            $('.file-preview').empty();
            $('#added-project-id').text(res.id);
            $('#projectStatus').val('Open').prop('selected', true).trigger('change');
            $('#addProjectM').modal('hide');
            $('#project_results').load("list.php #project_results", function() {

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
            setTimeout(showToast, 500);

          }
          if (res.status == 200) {
            $('#add-projects')[0].reset();
            $('#projectStatus').val('Open').prop('selected', true).trigger('change');
            $('#addProjectM').modal('hide');
            $('#project_results').load("list.php #project_results", function() {

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


            setTimeout(function() {
              $('.td' + res.id).addClass('bg bg-dark-subtle');
              setTimeout(function() {
                $('.td' + res.id).removeClass('bg bg-dark-subtle');
              }, 1000);
            }, 100);


          }
        },
        error: function(xhr, status, error) {
          // Handle error
          alert('Error adding user: ' + error);
        }
      });
    });
  });
</script>



<script>
  $(document).ready(function() {
    $('.select_projectStatus').select2({
      width: '100%',
      placeholder: 'Status',
      dropdownParent: $("#addProjectM"),
    });






  });
</script>
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
            var filePreview = $('<div class="file-preview"></div>');
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