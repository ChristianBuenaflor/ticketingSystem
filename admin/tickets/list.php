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



if($userRole == 'super_admin'){
    $userProfile = file_get_contents('images/adminpfp.png');
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>User - Tickets</title>
  <?php include("../header.php") ?>
  <style>
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


        <div class="row mt-4 card shadow-sm">
          <div class="card-header d-flex <?php if($userRole == 'super_admin'){ echo 'py-4'; } ?>" style="justify-content: space-between;">
            <h6>User - Tickets</h6>
            <?php if($userRole != 'super_admin'){ ?>
            <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addTicketM"><i class="ri-add-circle-fill"></i> Add Ticket</button>
          <?php } ?>
          </div>
          <div class="card-body" style="position: relative;" id="ticket-results">

            <div class="d-flex" style="position:absolute; width: 100px; top:10px; right:320px; z-index:999999999">
              <select class="type_filter" id="ticketTypeFilter">
                <option value="">-</option>
                <option value=" ">All</option>
                <option value="High - Support">High - Support</option>
                <option value="Mid - Support">Mid - Support</option>
                <option value="Low - Support">Low - Support</option>
                <option value="Request">Request</option>
              </select>
            </div>

            <div class="d-flex" style="position:absolute; width: 100px; top:10px; right:200px; z-index:999999999">
              <select class="status_filter" id="statusFilter">
                <option value="">-</option>
                <option value=" ">All</option>
                <option value="Open">Open</option>
                <option value="In progress">In progress</option>
                <option value="Closed">Closed</option>
              </select>
            </div>

            <table class="table table_display table-bordered">
              <thead>
                <tr>
                  <th>Ticket ID</th>
                  <th>Title</th>
                  <th>Project</th>
                  <th>Ticket Type</th>
                  <th>Created</th>
                  <th>Last Activity</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $query = "SELECT * FROM tickets";
                $result = mysqli_query($con, $query);
                if (mysqli_num_rows($result) > 0) {
                  while ($row = $result->fetch_assoc()) {
                    $ticket_id = $row['ticket_id'];
                    $tickets_userID = $row['user_id'];
                    $ticket_title = $row['title'];
                    $project_id = $row['project_id'];
                    $type_id = $row['type_id'];
                    $ticket_created = $row['created'];
                    $last_activity = $row['last_activity'];
                    $ticket_status = $row['status'];

                    $qproject = "Select project_title from projects where project_id = $project_id";
                    $resultp = mysqli_query($con, $qproject);
                    if (mysqli_num_rows($resultp) > 0) {
                      while ($rowp = $resultp->fetch_assoc()) {
                        $ticket_project = $rowp['project_title'];
                      }
                    }else{
                      $ticket_project = '-';
                    }
                    $qtype = "Select type_name from ticket_type where type_id = $type_id";
                    $resultp = mysqli_query($con, $qtype);
                    if (mysqli_num_rows($resultp) > 0) {
                      while ($rowp = $resultp->fetch_assoc()) {
                        $ticket_type = $rowp['type_name'];
                      }
                    }

                ?>
                    <tr>
                      <td class="td<?= $ticket_id ?>"><a href='view.php?ticketID=<?= $ticket_id ?>'>Ticket #<?= sprintf("%04d", $ticket_id); ?></a></td>
                      <td class="td<?= $ticket_id ?>"><a href='view.php?ticketID=<?= $ticket_id ?>'><?= $ticket_title ?></a></td>
                      <td class="td<?= $ticket_id ?>"><?php if($project_id!=0){ echo $ticket_project; }else{echo "-";}?></td>
                      <td class="td<?= $ticket_id ?>"><?= $ticket_type ?></td>
                      <td class="td<?= $ticket_id ?>"><?= $ticket_created ?></td>
                      <td class="td<?= $ticket_id ?>"><?= $last_activity ?></td>
                      <td class="td<?= $ticket_id ?>" style="position:relative"><span class="badge <?= $ticket_status ?>"><?= $ticket_status ?></span>
                        <?php
                        if ($tickets_userID == $user_id || $userRole == 'super_admin') {
                        ?>
                          <!-- Delete Button -->
                          <div class="btn-group dropstart" style="padding:0; position:absolute; top:0; right:12px">
                            <button type="button" class="btn" style="padding:0; border:0" data-bs-toggle="dropdown" aria-expanded="false">
                              <i class="ri-more-2-line"></i>
                            </button>
                            <ul class="dropdown-menu mt-1" style="border-radius: 0; padding:0">
<?php if($userRole!='super_admin'){ ?>
                              <a href="#" class="updateTicket" data-ticket-id='<?= $ticket_id ?>'>
                                <li style="font-size:.7rem; padding:6px 12px;" class="dropdown-item"><i style="font-weight: 1000; color:darkorange" class="ri-file-edit-line"></i> UPDATE TICKET</li>
                              </a>
<?php } ?>
                              <a href="../delete.php?deleteID=<?= $ticket_id ?>&from=tickets">
                                <li style="font-size:.7rem; padding:6px 12px;" class="dropdown-item"><i style="font-weight: 1000; color:red" class="ri-delete-bin-line"></i> DELETE</li>
                              </a>
                            </ul>
                          </div>
                          <!-- Delete Button -->
                        <?php
                        }
                        ?>
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

  <div class="modal fade" id="addTicketM" data-bs-backdrop="static" tabindex="-1" data-bs-keyboard="false" aria-labelledby="addTicketMLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title" id="addTicketMLabel">Add Ticket</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id='post-ticket' enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="user_id" value="<?= $user_id ?>">
          <input type="hidden" name="m_ticketId" id="m_ticketId">
          <div class="modal-body">
            <div class="row">
              <div class="col-3">
                <label class='form-label'>Title</label>
              </div>
              <div class="col">
                <input name="ticket_title" id="ticketTitle" autocomplete="off" placeholder="Title" class="form-inputs form-control form-control-sm focus-ring focus-ring-light" type="text">
              </div>
            </div>
            <!-- -->
            <div class="row mt-2">
              <div class="col-3">
                <label class='form-label'>Project</label>
              </div>
              <div class="col">
                <select name="project" id="selectProject" class="select_project selectpicker">
                  <option value="">-</option>
                  <?php
                  $query_project = "SELECT * FROM projects";
                  $result_project = mysqli_query($con, $query_project);
                  if (mysqli_num_rows($result_project) > 0) {
                    while ($row_project = $result_project->fetch_assoc()) {
                  ?>
                      <option value="<?= $row_project['project_id']; ?>"><?= $row_project['project_title'] ?></option>
                  <?php
                    }
                  }
                  ?>

                </select>
              </div>
            </div>
            <!-- -->
            <div class="row mt-2">
              <div class="col-3">
                <label class='form-label'>Ticket Type</label>
              </div>
              <div class="col">
                <select name="ticket_type" id="selectType" class="select_type selectpicker">
                  <?php
                  $query_project = "SELECT * FROM ticket_type";
                  $result_project = mysqli_query($con, $query_project);
                  if (mysqli_num_rows($result_project) > 0) {
                    while ($row_project = $result_project->fetch_assoc()) {
                  ?>
                      <option value="<?= $row_project['type_id']; ?>"><?= $row_project['type_name'] ?></option>
                  <?php
                    }
                  }
                  ?>

                </select>
              </div>
            </div>
            <!-- -->
            <div class="row mt-2">
              <div class="col-3" style="display:flex;align-self: start;">
                <label class='form-label'>Description</label>
              </div>
              <div class="col">
                <textarea class="form-control form-control-sm focus-ring focus-ring-light" name="ticket_description" id="ticketDescription" cols="30" rows="4" placeholder="Description" autocomplete="off"></textarea>
              </div>
            </div>
            <!-- -->
            <div class="row mt-2">
              <div class="col-3">
                <label class='form-label'>Status</label>
              </div>
              <div class="col">
                <select name="ticket_status" id="selectStatus" class="select_status selectpicker">
                  <option value="">-</option>
                  <option selected value="Open">Open</option>
                  <option value="In progress">In Progress</option>
                  <option value="Closed">Closed</option>
                </select>
              </div>
            </div>
            <!-- -->

            <div id="invalidProject" class="alert alert-danger mt-2 mb-0 text-center d-none" style="padding:0px 12px; font-size:.75rem; color:darkred; border-radius:3px">Select Project&nbsp;&nbsp;<i style="font-size:.8rem" class="ri-error-warning-line"></i></div>
            <div id="invalidStatus" class="alert alert-danger mt-2 mb-0 text-center d-none" style="padding:0px 12px; font-size:.75rem; color:darkred; border-radius:3px">Select Status&nbsp;&nbsp;<i style="font-size:.8rem" class="ri-error-warning-line"></i></div>

          </div>
          <div class="modal-footer" style="justify-content: start;">

            <div class="file-preview"></div>
            <div style="display:flex; justify-content:space-between; width:100%">
              <div>
                <button class="upload-file-btn btn" type="button"><i style="font-size:.9rem" class="ri-camera-fill"></i>&nbsp; Upload File</button>
                <input style="display:none" name="ticket_files[]" type="file" id="fileInput" multiple>
              </div>
              <div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i style="font-weight: 1000;" class="ri-close-fill"></i> Close</button>
                <button type="submit" id="addTicketBtn" class="btn btn-primary"><i class="ri-checkbox-circle-fill"></i> Save</button>
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
        Added Successfully! Added ticket ID : <span id="added-ticket-id"></span>
      </div>
    </div>
  </div>

  <?php include("../footer.php") ?>

</body>

<script>
  $(document).ready(function() {
    // Listen for the modal's hidden.bs.modal event
    $('#addTicketM').on('hidden.bs.modal', function(e) {

      $('#addTicketBtn').html('<i class="ri-checkbox-circle-fill"></i> Save');

      // Reset the form or input values here
      $('#m_ticketId').val('');
      $('#ticketTitle').val('');
      $('#selectProject').val('').trigger('change'); // Reset Select2 dropdown
      $('#ticketDescription').val('');
      $('#selectStatus').val('Open').trigger('change'); // Reset Select2 dropdown
      $('.file-preview').empty();
      $('#fileInput').val('');
    });
  });
</script>

<script>
  $(document).on('click', '.updateTicket', function(e) {
    var ticket_id = $(this).data('ticket-id');

    $('#addTicketBtn').html('<i class="ri-checkbox-circle-fill"></i> Update');

    console.log("Ticket ID:", ticket_id);
    $.ajax({
      type: "GET",
      url: "ajax.php?updateid=" + ticket_id,

      success: function(response) {

        var res = jQuery.parseJSON(response);

        if (res.status == 200) {

          $('#m_ticketId').val(res.data.ticket_id);
          $('#ticketTitle').val(res.data.title);
          if(res.data.project_id!=0)
          {
            $('#selectProject').val(res.data.project_id).trigger('change');
          }
          $('#selectType').val(res.data.type_id).trigger('change');
          $('#ticketDescription').val(res.data.description);
          $('#selectStatus').val(res.data.status).trigger('change');
          $('#addTicketM').modal('show');
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

    $('#selectProject').change(function() {
      $('#invalidProject').addClass('d-none');
    });

    $('#selectStatus').change(function() {
      $('#invalidStatus').addClass('d-none');
    });


  });
</script>

<script>
  $(document).ready(function() {
    $('#post-ticket').submit(function(event) {
      // Prevent default form submission
      event.preventDefault();



      const toastLiveExample = document.getElementById('liveToast')


      function showToast() {
        const toastBootstrap = new bootstrap.Toast(toastLiveExample)
        toastBootstrap.show()
      }

      var btn = $('#addTicketBtn');
      btn.html('<i class="ri-loader-2-line"></i> Loading').attr('disabled', true);
      setTimeout(function() {
        btn.html('<i class="ri-checkbox-circle-fill"></i> Save').attr('disabled', false);
      }, 1000);



      // Get form data
      var formData = new FormData(this);
      formData.append("post-ticket", true);

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
            $('#post-ticket')[0].reset();
            $('#added-ticket-id').text(res.id);
            $('#selectProject').val('').prop('selected', true).trigger('change');
            $('#selectStatus').val('').prop('selected', true).trigger('change');
            $('.file-preview').empty();
            $('#addTicketM').modal('hide');
            $('#ticket-results').load("list.php #ticket-results", function() {

              $('.status_filter').select2({
                width: '100%',
                placeholder: 'Status',
              });
              $('.type_filter').select2({
                width: '100%',
                placeholder: 'Type',
              });

              var table = $('.table_display').DataTable({
                "lengthMenu": [
                  [10, 25, 50, -1],
                  [10, 25, 50, "All"]
                ],
                "pageLength": 10,
                "order": [
                  [0, 'desc']
                ],

              });

              $('#statusFilter').on('change', function() {
                var selectedStatus = $(this).val();

                table.column(6).search(selectedStatus).draw();
              });

              $('#ticketTypeFilter').on('change', function() {
                var selectedStatus = $(this).val();

                table.column(3).search(selectedStatus).draw();
              });


            });
            setTimeout(showToast, 500);




          } else if (res.status == 200) {
            $('#post-ticket')[0].reset();
            $('#selectProject').val('').prop('selected', true).trigger('change');
            $('#selectStatus').val('').prop('selected', true).trigger('change');
            $('.file-preview').empty();
            $('#addTicketM').modal('hide');
            $('#ticket-results').load("list.php #ticket-results", function() {

              $('.status_filter').select2({
                width: '100%',
                placeholder: 'Status',
              });
              $('.type_filter').select2({
                width: '100%',
                placeholder: 'Type',
              });

              var table = $('.table_display').DataTable({
                "lengthMenu": [
                  [10, 25, 50, -1],
                  [10, 25, 50, "All"]
                ],
                "pageLength": 10,
                "order": [
                  [0, 'desc']
                ],

              });

              $('#statusFilter').on('change', function() {
                var selectedStatus = $(this).val();

                table.column(6).search(selectedStatus).draw();
              });

              $('#ticketTypeFilter').on('change', function() {
                var selectedStatus = $(this).val();
                table.column(3).search(selectedStatus).draw();
              });


            });



            setTimeout(function() {
              $('.td' + res.id).addClass('bg bg-dark-subtle');
              setTimeout(function() {
                $('.td' + res.id).removeClass('bg bg-dark-subtle');
              }, 1000);
            }, 100);

          } else if (res.status == 101) {
            $('#ticketTitle').removeClass('is-invalid');
            $('#invalidProject').addClass('d-none');
            $('#invalidStatus').addClass('d-none');
            setTimeout(function() {
              $('#ticketTitle').addClass('is-invalid');
            }, 100);
          } else if (res.status == 102) {
            $('#ticketTitle').removeClass('is-invalid');
            $('#invalidProject').addClass('d-none');
            $('#invalidStatus').addClass('d-none');
            setTimeout(function() {
              $('#invalidProject').removeClass('d-none');
            }, 50);
          } else if (res.status == 103) {
            $('#ticketTitle').removeClass('is-invalid');
            $('#invalidStatus').addClass('d-none');
            $('#invalidProject').addClass('d-none');
            setTimeout(function() {
              $('#invalidStatus').removeClass('d-none');
            }, 50);
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

<script>
  $(document).ready(function() {
    $('.select_project').select2({
      width: '100%',
      placeholder: 'Project',
      dropdownParent: $("#addTicketM"),
    });

    $('.select_type').select2({
      width: '100%',
      dropdownParent: $("#addTicketM"),
    });

    $('.select_status').select2({
      width: '100%',
      placeholder: 'Status',
      dropdownParent: $("#addTicketM"),
    });

    $('.status_filter').select2({
      width: '100%',
      placeholder: 'Status',
    });
    $('.type_filter').select2({
      width: '100%',
      placeholder: 'Type',
    });





  });
</script>
<script>
  $(document).ready(function() {
    var table = $('.table_display').DataTable({
      "lengthMenu": [
        [10, 25, 50, -1],
        [10, 25, 50, "All"]
      ],
      "pageLength": 10,
      "order": [
        [0, 'desc']
      ],

    });
    $('#statusFilter').on('change', function() {
      var selectedStatus = $(this).val();

      table.column(6).search(selectedStatus).draw(); // Assuming status column is the third column (index 2)
    });

    $('#ticketTypeFilter').on('change', function() {
      var selectedStatus = $(this).val();

      table.column(3).search(selectedStatus).draw(); // Assuming status column is the third column (index 2)
    });




  });
</script>

</html>