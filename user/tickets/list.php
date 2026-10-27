<?php
include("../../includes/connections.php");
session_start();

if (!empty($_SESSION['logged_in'])) {
  $user_id = $_SESSION['user_id'];
  $user_role = $_SESSION['role'];

  // Add verification check (NEW CODE)
  $verify_query = mysqli_query($con, "SELECT verified FROM users WHERE user_id = '$user_id'");
  if ($verify_query && mysqli_num_rows($verify_query) > 0) {
    $user_data = mysqli_fetch_assoc($verify_query);
    if ($user_data['verified'] != 1 && $user_role == 'client') {
      $_SESSION['error'] = "Please verify your email before accessing the system";
      header("location:../../index.php");
      exit;
    }
  }
}

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
  <title>User - Tickets</title>
  <?php include("../header.php") ?>
  <style>
    .dropdown-menu {
      --bs-dropdown-link-active-bg: rgb(140, 136, 102);
    }

    table.dataTable tbody td {
      vertical-align: middle;
    }

    .notEvaluated:hover {
      cursor: pointer;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
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
          <div class="card-header d-flex" style="justify-content: space-between; background-color:#a60212;<?php if ($user_role != 'client') { ?> padding-top:28px; padding-bottom:28px; <?php } ?>">
            <?php if ($user_role == 'client'): ?>
              <?php
              $stmt = $con->prepare("SELECT verified FROM users WHERE user_id = ?");
              $stmt->bind_param("i", $user_id);
              $stmt->execute();
              $result = $stmt->get_result();
              $verified = $result->fetch_assoc();
              ?>

              <?php if ($verified && $verified['verified'] == 1): ?>
                <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addTicketM">
                  <i class="ri-add-circle-fill"></i> Add Ticket
                </button>
              <?php else: ?>
                <div class="d-inline-block position-relative">
                  <button class="btn btn-add disabled">
                    <i class="ri-add-circle-fill"></i> Add Ticket
                  </button>
                  <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark ms-1"
                    data-bs-toggle="tooltip"
                    data-bs-placement="right"
                    title="Please verify your email to create tickets">
                    <i class="ri-alert-line"></i> Verify First
                  </span>
                </div>
              <?php endif; ?>
            <?php endif; ?>

          </div>
          <div class="card-body" style="position: relative;">
            <div id="ticket-results">

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

              <div class="d-flex" style="position:absolute; width: 120px; top:10px; right:445px; z-index:99999999999">
                <select class="expertise_filter" id="expertiseFilter">
                  <option value="">-</option>
                  <option value=" ">All</option>

                  <?php
                  $queryExp = "SELECT * FROM ticket_category";
                  $resultExp = mysqli_query($con, $queryExp);
                  if (mysqli_num_rows($resultExp) > 0) {
                    while ($rowExp = $resultExp->fetch_assoc()) {

                      $expertise_title = $rowExp['category'];




                  ?>
                      <option value="<?= $expertise_title ?>"><?= $expertise_title ?></option>
                  <?php
                    }
                  }
                  ?>

                </select>
              </div>

              <table class="table table_display table-bordered">
                <thead>
                  <tr>
                    <th>Ticket ID</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Ticket Type</th>
                    <th>Technician</th>
                    <th>Created<br>Last Activity</th>
                    <th>Priority</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  if ($user_role == 'client') {
                    $query = "SELECT * FROM tickets where user_id = $user_id";
                    $result = mysqli_query($con, $query);
                  } else if ($user_role == 'technician') {
                    $query = "SELECT * FROM tickets where technician = '$user_id'";
                    $result = mysqli_query($con, $query);
                  } else {
                    $query = "SELECT * FROM tickets ";
                    $result = mysqli_query($con, $query);
                  }
                  if (mysqli_num_rows($result) > 0) {
                    while ($row = $result->fetch_assoc()) {
                      $ticket_id = $row['ticket_id'];
                      $tickets_userID = $row['user_id'];
                      $ticket_title = $row['title'];
                      $category_id = $row['category_id'];
                      $type_id = $row['type_id'];
                      $technician_id = $row['technician'];
                      $ticket_created = $row['created'];
                      $last_activity = $row['last_activity'];
                      $ticket_priority = $row['priority_score'];
                      $ticket_status = $row['status'];

                      $qproject = "Select category from ticket_category where category_id = $category_id";
                      $resultp = mysqli_query($con, $qproject);
                      if (mysqli_num_rows($resultp) > 0) {
                        while ($rowp = $resultp->fetch_assoc()) {
                          $ticket_category = $rowp['category'];
                        }
                      } else {
                        $ticket_category = '-';
                      }
                      $qtype = "Select type_name from ticket_type where type_id = $type_id";
                      $resultp = mysqli_query($con, $qtype);
                      if (mysqli_num_rows($resultp) > 0) {
                        while ($rowp = $resultp->fetch_assoc()) {
                          $ticket_type = $rowp['type_name'];
                        }
                      }


                      $qtechnician = "Select * FROM users_info where user_id = '$technician_id'";
                      $resultqt = mysqli_query($con, $qtechnician);
                      if (mysqli_num_rows($resultqt) > 0) {
                        while ($rowqt = $resultqt->fetch_assoc()) {
                          $technician = $rowqt['last_name'] . ', ' . $rowqt['first_name'];
                        }
                      } else {
                        $technician = '-';
                      }

                      $priority_score = 0;


                      if (strtolower($ticket_category) == 'School Management System - Student') {
                        $priority_score += 5;
                      } else if (strtolower($ticket_category) == 'Microsoft Account') {
                        $priority_score += 4;
                      } else if (strtolower($ticket_category) == 'Google Account Issue') {
                        $priority_score += 3;
                      } else if (strtolower($ticket_category) == 'System Unit / Application') {
                        $priority_score += 2;
                      } else if (strtolower($ticket_category) == 'Networking / Internet') {
                        $priority_score += 1;
                      }
                              
/*


                      if (strtolower($ticket_category) == 'hardware') {
                        $priority_score += 5;
                      } else if (strtolower($ticket_category) == 'system unit') {
                        $priority_score += 4;
                      } else if (strtolower($ticket_category) == 'reformat') {
                        $priority_score += 3;
                      } else if (strtolower($ticket_category) == 'program') {
                        $priority_score += 2;
                      } else if (strtolower($ticket_category) == 'debug') {
                        $priority_score += 1;
                      }

*/


                      if (strtolower($ticket_type) == 'high - support') {
                        $priority_score += 10;
                      } else if (strtolower($ticket_type) == 'mid - support') {
                        $priority_score += 5;
                      } else if (strtolower($ticket_type) == 'low - support') {
                        $priority_score += 1;
                      }


                      if ($priority_score >= 7) {
                        $ticket_priority = "<span class='badge bg-danger'>High</span>";
                      } else if ($priority_score >= 4) {
                        $ticket_priority = "<span class='badge bg-warning text-dark'>Medium</span>";
                      } else {
                        $ticket_priority = "<span class='badge bg-secondary'>Low</span>";
                      }







                  ?>
                      <tr>
                        <td class="td<?= $ticket_id ?>"><a href='view.php?ticketID=<?= $ticket_id ?>'>Ticket #<?= sprintf("%04d", $ticket_id); ?></a></td>
                        <td class="td<?= $ticket_id ?>"><a href='view.php?ticketID=<?= $ticket_id ?>'><?= $ticket_title ?></a></td>
                        <td class="td<?= $ticket_id ?>"><?php if ($category_id != 0) {
                                                          echo $ticket_category;
                                                        } else {
                                                          echo "-";
                                                        } ?></td>
                        <td class="td<?= $ticket_id ?>"><?= $ticket_type ?></td>
                        <td class="td<?= $ticket_id ?>"><a href='../users/profile.php?id=<?= $technician_id ?>'><?= $technician ?></a></td>
                        <td class="td<?= $ticket_id ?>">
                          <?= !empty($ticket_created) ? date("m/d/Y h:i a", strtotime($ticket_created)) : '-' ?>
                          <br>
                          <?= !empty($last_activity) ? date("m/d/Y h:i a", strtotime($last_activity)) : '-' ?>
                        </td>




                        <td class="td<?= $ticket_id ?>"><?= $ticket_priority ?></td>
                        <td class="td<?= $ticket_id ?>" style="position:relative">
                          <?php
                          if ($ticket_status == 'Closed') {


                            $qChecks = "SELECT * FROM evaluation WHERE ticket_id = $ticket_id";
                            $rChecks = mysqli_query($con, $qChecks);
                            if (mysqli_num_rows($rChecks) == 0) {
                          ?>

                              <span><i title="No Evaluation" style="font-size:12px;background-color:#fff3cd; color:#856404; border:1px solid #ffeeba; padding:2px; border-radius:50%" class="notEvaluated ri-error-warning-line"></i></span>&nbsp;
                          <?php

                            }
                          }
                          ?>
                          <span class="badge <?= $ticket_status ?>"><?= $ticket_status ?></span>

                          <!-- Delete Button -->
                          <div class="btn-group dropstart" style="padding:0; position:absolute; top:0; right:12px">
                            <button type="button" class="btn" style="padding:0; border:0" data-bs-toggle="dropdown" aria-expanded="false">
                              <i class="ri-more-2-line"></i>
                            </button>
                            <ul class="dropdown-menu mt-1" style="border-radius: 0; padding:0">

                              <a href="#" class="updateTicket" data-ticket-id='<?= $ticket_id ?>'>
                                <li style="font-size:.7rem; padding:6px 12px;" class="dropdown-item"><i style="font-weight: 1000; color:darkorange" class="ri-file-edit-line"></i> UPDATE TICKET</li>
                              </a>

                              <?php
                              if ($user_role != 'technician') {
                              ?>
                                <a href="../delete.php?deleteID=<?= $ticket_id ?>&from=tickets">
                                  <li style="font-size:.7rem; padding:6px 12px;" class="dropdown-item"><i style="font-weight: 1000; color:red" class="ri-delete-bin-line"></i> DELETE</li>
                              </a>
                              <?php
                              }
                              ?>
                            </ul>
                          </div>
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
    <input type="hidden" name="user_id" value="<?= $user_id ?>">
    <input type="hidden" name="m_ticketId" id="m_ticketId">
    <div class="modal-body">
        <div class="row">
            <div class="col-3">
                <label class='form-label'>Title</label>
            </div>
            <div class="col">
                <input <?php if ($user_role == 'technician') { echo 'disabled'; } ?> 
                    name="ticket_title" id="ticketTitle" autocomplete="off" 
                    placeholder="Title" class="form-inputs form-control form-control-sm focus-ring focus-ring-light" 
                    type="text">
            </div>
        </div>
            <!-- -->
            <div class="row mt-2">
              <div class="col-3">
                <label class='form-label'>Category</label>
              </div>
              <div class="col">
                <select <?php if ($user_role == 'technician') {
                          echo 'disabled';
                        } ?> name="project" id="selectProject" class="select_project selectpicker">
                  <option value="">-</option>
                  <?php
                  $query_project = "SELECT * FROM ticket_category";
                  $result_project = mysqli_query($con, $query_project);
                  if (mysqli_num_rows($result_project) > 0) {
                    while ($row_project = $result_project->fetch_assoc()) {
                      $expertise_id = $row_project['category_id'];

                      $queryTech = "SELECT * FROM users right join users_info on users.user_id = users_info.user_id where users_info.expertise = '$expertise_id'";
                      $resultTech = mysqli_query($con, $queryTech);
                      if (mysqli_num_rows($resultTech) > 0) {
                        while ($rowTech = $resultTech->fetch_assoc()) {
                          $avail = 'true';
                        }
                      } else {
                        $avail = 'false';
                      }

                  ?>

                      <option <?php if ($avail != 'true') {
                                echo 'disabled';
                              } ?> value="<?= $row_project['category_id']; ?>"><?= $row_project['category'] ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php if ($avail != 'true') {
                                                                                                                                                                                            echo '(no technician)';
                                                                                                                                                                                          } ?></option>
                  <?php
                    }
                  }
                  ?>

                </select>
              </div>
            </div>
                      <!-- Technician Selection (only for admins/managers) -->
 <!-- Replace the existing technician dropdown with this: -->
<?php if ($user_role != 'client'): ?>
<div class="row mt-2">
    <div class="col-3">
        <label class='form-label'>Technician</label>
    </div>
    <div class="col">
        <select name="ticket_technician" id="selectTechnician" class="select_technician selectpicker">
            <option value="">- Assign Technician -</option>
            <!-- Technicians will be loaded dynamically via JavaScript -->
        </select>
    </div>
</div>
<?php endif; ?>

            <!-- -->
            <div class="row mt-2">
              <div class="col-3">
                <label class='form-label'>Ticket Type</label>
              </div>
              <div class="col">
                <select <?php if ($user_role == 'technician') {
                          echo 'disabled';
                        } ?> name="ticket_type" id="selectType" class="select_type selectpicker">
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
                <textarea <?php if ($user_role == 'technician') {
                            echo 'disabled';
                          } ?> class="form-control form-control-sm focus-ring focus-ring-light" name="ticket_description" id="ticketDescription" cols="30" rows="4" placeholder="Description" autocomplete="off"></textarea>
              </div>
            </div>
            <!-- -->
            <div class="row mt-2" id="selectStatusdiv">
              <div class="col-3">
                <label class='form-label'>Status</label>
              </div>
              <div class="col">
                <select name="ticket_status" id="selectStatus" class="select_status selectpicker">
                  <option value="">-</option>
                  <option selected value="Open">Open</option>
                  <?php
                  if ($user_role != 'client') {

                  ?>
                    <option value="In progress">In Progress</option>
                    <option value="Closed">Closed</option>
                  <?php
                  }
                  ?>
                </select>
              </div>
            </div>
            <!-- -->



            <div id="invalidCategory" class="alert alert-danger mt-2 mb-0 text-center d-none" style="opacity:.7; padding:0px 12px; font-size:.75rem; color:darkred; border-radius:3px">Select Category&nbsp;&nbsp;<i style="font-size:.8rem" class="ri-error-warning-line"></i></div>
            <div id="invalidStatus" class="alert alert-danger mt-2 mb-0 text-center d-none" style="padding:0px 12px; font-size:.75rem; color:darkred; border-radius:3px">Select Status&nbsp;&nbsp;<i style="font-size:.8rem" class="ri-error-warning-line"></i></div>

          </div>
          <div class="modal-footer" style="justify-content: start;">

            <div class="file-preview"></div>
            <div style="display:flex; justify-content:space-between; width:100%">
              <div>
                <?php
                if ($user_role == 'client') {
                ?>
                  <button class="upload-file-btn btn" type="button"><i style="font-size:.9rem" class="ri-camera-fill"></i>&nbsp; Upload File</button>
                <?php } ?>
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
        $('#addTicketMLabel').text('Add Ticket');
        
        // Reset all form fields
        $('#m_ticketId').val('');
        $('#ticketTitle').val('');
        $('#selectProject').val('').trigger('change');
        $('#selectTechnician').val('').trigger('change'); // Reset technician dropdown
        $('#selectType').val('').trigger('change');
        $('#selectStatus').val('Open').trigger('change');
        $('#ticketDescription').val('');
        $('#selectStatusdiv').removeClass('d-none');
        $('.file-preview').empty();
        $('#fileInput').val('');
    });
});
</script>

<script>
$(document).on('click', '.updateTicket', function(e) {
    var ticket_id = $(this).data('ticket-id');

    $('#addTicketBtn').html('<i class="ri-checkbox-circle-fill"></i> Update');
    $('#addTicketMLabel').text('Update Ticket');

    $.ajax({
        type: "GET",
        url: "ajax.php?updateid=" + ticket_id,
        success: function(response) {
            var res = jQuery.parseJSON(response);

            if (res.status == 200) {
                // Show/hide fields based on role
                if (<?php echo json_encode($user_role) ?> == 'client') {
                    $('#selectStatusdiv').addClass('d-none');
                    $('.select_technician').closest('.row').hide(); // Hide technician field for clients
                } else {
                    $('#selectStatusdiv').removeClass('d-none');
                    $('.select_technician').closest('.row').show(); // Show technician field for admins/managers
                }

                // Populate form fields
                $('#m_ticketId').val(res.data.ticket_id);
                $('#ticketTitle').val(res.data.title);
                
                if (res.data.category_id != 0) {
                    $('#selectProject').val(res.data.category_id).trigger('change');
                }
                
                $('#selectType').val(res.data.type_id).trigger('change');
                $('#ticketDescription').val(res.data.description);
                
                // Set technician value if exists, otherwise leave empty
                if (res.data.technician) {
                    $('#selectTechnician').val(res.data.technician).trigger('change');
                } else {
                    $('#selectTechnician').val('').trigger('change');
                }
                
                if (<?php echo json_encode($user_role) ?> != 'client') {
                    $('#selectStatus').val(res.data.status).trigger('change');
                }

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
      $('#invalidCategory').addClass('d-none');
    });

    $('#selectStatus').change(function() {
      $('#invalidStatus').addClass('d-none');
    });


  });
</script>

<script>
$(document).ajaxComplete(function(event, xhr, settings) {
    try {
        var response = JSON.parse(xhr.responseText);
        if (response.status == 403) {
            // Show error using your existing toast system
            $('#liveToast .toast-body').html(
                '<div class="alert alert-warning">' +
                '<i class="ri-alarm-warning-fill"></i> ' + 
                response.message +
                '</div>'
            );
            const toast = new bootstrap.Toast(document.getElementById('liveToast'));
            toast.show();
            
            // Close any open modals
            $('#addTicketM').modal('hide');
        }
    } catch(e) {
        // Not a JSON response, ignore
    }
});


</script>


<!-- handle category -->

<script>
$(document).ready(function() {
    // Function to load technicians based on selected category
    function loadTechnicians(categoryId) {
        if (!categoryId) {
            $('#selectTechnician').empty().append('<option value="">- Assign Technician -</option>');
            return;
        }

        $.ajax({
            url: 'ajax.php?action=get_technicians&category_id=' + categoryId,
            type: 'GET',
            success: function(response) {
                var res = jQuery.parseJSON(response);
                if (res.status == 200) {
                    $('#selectTechnician').empty().append('<option value="">- Assign Technician -</option>');
                    $.each(res.technicians, function(index, tech) {
                        $('#selectTechnician').append(
                            $('<option></option>').val(tech.user_id).text(tech.last_name + ', ' + tech.first_name)
                        );
                    });
                }
            }
        });
    }

    // When category changes, update technicians list
    $('#selectProject').on('change', function() {
        var categoryId = $(this).val();
        loadTechnicians(categoryId);
    });

    // When updating a ticket, load technicians for its category
    $(document).on('click', '.updateTicket', function(e) {
        var ticket_id = $(this).data('ticket-id');
        
        $.ajax({
            type: "GET",
            url: "ajax.php?updateid=" + ticket_id,
            success: function(response) {
                var res = jQuery.parseJSON(response);
                if (res.status == 200) {
                    // ... existing update code ...
                    
                    // Load technicians for this ticket's category
                    if (res.data.category_id != 0) {
                        loadTechnicians(res.data.category_id);
                    }
                    
                    // ... rest of your update code ...
                }
            }
        });
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

              $('.expertise_filter').select2({
                width: '100%',
                placeholder: 'Expertise',
              });

              $('#expertiseFilter').on('change', function() {
                var selectedroles = $(this).val();

                table.column(2).search(selectedroles).draw();
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

              $('.expertise_filter').select2({
                width: '100%',
                placeholder: 'Expertise',
              });

              $('#expertiseFilter').on('change', function() {
                var selectedroles = $(this).val();

                table.column(2).search(selectedroles).draw();
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
            $('#invalidCategory').addClass('d-none');
            $('#invalidStatus').addClass('d-none');
            setTimeout(function() {
              $('#ticketTitle').addClass('is-invalid');
            }, 100);
          } else if (res.status == 102) {
            $('#ticketTitle').removeClass('is-invalid');
            $('#invalidCategory').addClass('d-none');
            $('#invalidStatus').addClass('d-none');
            setTimeout(function() {
              $('#invalidCategory').removeClass('d-none');
            }, 50);
          } else if (res.status == 103) {
            $('#ticketTitle').removeClass('is-invalid');
            $('#invalidStatus').addClass('d-none');
            $('#invalidCategory').addClass('d-none');
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

<!-- tooltips -->
<script>
  $(document).ready(function(){
    $('[data-bs-toggle="tooltip"]').tooltip();
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
      placeholder: 'Select Category',
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

    $('.select_technician').select2({
      width: '100%',
      placeholder: 'Technician',
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


    $('.select_expertise').select2({
      width: '100%',
      placeholder: 'Select Expertise',
      dropdownParent: $("#addUserM"),
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


    $('.expertise_filter').select2({
      width: '100%',
      placeholder: 'Expertise',
    });



    $('#expertiseFilter').on('change', function() {
      var selectedroles = $(this).val();

      table.column(2).search(selectedroles).draw();
    });



  });
</script>

</html>