<?php
include("../../includes/connections.php");
session_start();

if (!empty($_SESSION['logged_in'])) {
 
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




?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>User - Admins</title>
  <?php include("../header.php") ?>
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
        .btn-add{
    font-weight: 600;
    color:#a60212;
    background-color:#f5ab2c !important;
}
.btn-add:hover{
    font-weight: 600;
    color:#a60212;
    background-color:#f5ab2c !important;
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
          <div class="card-header d-flex" style="justify-content: space-between;background-color:#a60212;" <?php if ($userRole != 'admin') {
                                                                                    echo ' padding-top:28px; padding-bottom:28px;';
                                                                                  } ?>">
            <h6 style="color:#ffffff;">Admins</h6>
            <?php if ($userRole == 'super_admin') { ?>
              <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addUserM"><i class="ri-add-circle-fill"></i> Add User</button>
            <?php } ?>
          </div>
          <div class="card-body" style="position: relative;">
            <div id="user-results">

            <table class="table table_display table-bordered table-hover">
              <thead>
                <tr>
                  <th></th>
                  <th>Name</th>
                  <th>Job Title</th>
                  <th>Email</th>
                  <th>Phone</th>
                  <th>Skype</th>
                  <th>Role</th>
                </tr>
              </thead>
              <tbody>
                <?php

                $query = "SELECT * FROM users where role = 'admin'";
                $result = mysqli_query($con, $query);




                if (mysqli_num_rows($result) > 0) {
                  while ($row = $result->fetch_assoc()) {
                    $roles = $row['role'];
                    $users_id = $row['user_id'];
                    $emails = $row['email'];

                    $queryinfo = "SELECT * FROM users_info where user_id = '$users_id'";
                    $resultinfo = mysqli_query($con, $queryinfo);
                    if (mysqli_num_rows($resultinfo) > 0) {
                      while ($rowinfo = $resultinfo->fetch_assoc()) {
                        $last_name = $rowinfo['last_name'];
                        $first_name = $rowinfo['first_name'];
                        $job_title = $rowinfo['job_title'];
                        $phone = $rowinfo['phone'];
                        $skype = $rowinfo['skype'];
                        $profiles = $rowinfo['profile'];
                        $profiles_type = $rowinfo['profile_type'];
                        if (empty($profiles)) {
                          $profiles = file_get_contents('../tickets/images/Default_pfp.png');
                        }
                      }

                ?>
                      <tr>
                        <td class="text-center"><?php echo '<img class="user-profiles" src="data:' . $profiles_type . ';base64,' . base64_encode($profiles) . '" alt="' . $first_name . ' ' . $last_name . '">'; ?></td>
                        <td><?= $first_name . ' ' . $last_name ?></td>
                        <td><?php if ($job_title == '') {
                              echo '-';
                            } else { ?><?= $job_title ?><?php } ?></td>
                        <td><?= $emails ?></td>
                        <td><?= $phone ?></td>
                        <td><?php if ($job_title == '') {
                              echo '-';
                            } else { ?><?= $skype ?><?php } ?></td>
                        <td class="position-relative"><?= $roles ?>
                          <?php
                          if ($userRole == 'admin' && $users_id != $user_id || $userRole == 'super_admin') {
                          ?>
                            <!-- Delete Button -->
                            <div class="btn-group dropstart" style="padding:0; position:absolute; top:20px; right:12px">
                              <button type="button" class="btn" style="padding:0; border:0" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ri-more-2-line"></i>
                              </button>
                              <ul class="dropdown-menu mt-1" style="border-radius: 0; padding:0">
                                <a onclick="javascript:return confirm('Delete this user? you cannot undo this action.');" href="../delete.php?deleteID=<?= $users_id ?>&from=admins">
                                  <li style="font-size:.7rem; padding:6px 12px;" class="dropdown-item"><i style="font-weight: 1000; color:red" class="ri-user-unfollow-fill"></i> DELETE USER</li>
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

  <div class="modal fade" id="addUserM" data-bs-backdrop="static" tabindex="-1" data-bs-keyboard="false" aria-labelledby="addUserMLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title" id="addUserMLabel">Add User</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id='add-users'>
          <div class="modal-body">
            <div class="row">
              <div class="col-3">
                <label class='form-label'>Last Name</label>
              </div>
              <div class="col">
                <input required name="last_name" id="lastName" autocomplete="off" placeholder="Last Name" class="form-inputs form-control form-control-sm focus-ring focus-ring-light" type="text">
              </div>
            </div>
            <div class="row mt-2">
              <div class="col-3">
                <label class='form-label'>First Name</label>
              </div>
              <div class="col">
                <input required name="first_name" id="firstName" autocomplete="off" placeholder="First Name" class="form-inputs form-control form-control-sm focus-ring focus-ring-light" type="text">
              </div>
            </div>

            <div class="row mt-2">
              <div class="col-3">
                <label class='form-label'>Email Address</label>
              </div>
              <div class="col">
                <input required name="email_address" id="emailAddress" autocomplete="off" placeholder="Email Address" class="form-inputs form-control form-control-sm focus-ring focus-ring-light" type="text">
              </div>
            </div>

            <div class="row mt-2">
              <div class="col-3">
                <label class='form-label'>Phone</label>
              </div>
              <div class="col">
                <input required name="phone" id="phone" autocomplete="off" placeholder="Phone" class="form-inputs form-control form-control-sm focus-ring focus-ring-light" type="text" oninput="this.value = this.value.replace(/[^ -+0-9]/g, '')">
              </div>
            </div>

            <div class="row mt-2">
              <div class="col-3">
                <label class='form-label'>User Type</label>
              </div>
              <div class="col">
                <select name="user_type" id="user_type" class="select_userType selectpicker">
                  <option value="">-</option>
                  <option value="client">Client</option>
                  <option value="technician">Technician</option>
                  <option selected value="admin">Admin</option>
                </select>
              </div>
            </div>


            <div class="row mt-2 expertise-row d-none">
              <div class="col-3">
                <label class='form-label'>Expertise</label>
              </div>
              <div class="col">
                <select name="expertise" id="expertise" class="select_expertise selectpicker">
                  <option value="">-</option>

                  <?php

                  $queryExp = "SELECT * FROM ticket_category";
                  $resultExp = mysqli_query($con, $queryExp);
                  if (mysqli_num_rows($resultExp) > 0) {
                    while ($rowExp = $resultExp->fetch_assoc()) {
                      $expertise_id = $rowExp['category_id'];
                      $expertise_title = $rowExp['category'];
                  ?>
                      <option value="<?= $expertise_id ?>"><?= $expertise_title ?></option>
                  <?php
                    }
                  }

                  ?>

                </select>
              </div>
            </div>


          </div>
          <div class="modal-footer" style="justify-content: start;">


            <div style="display:flex; justify-content:space-between; width:100%">
              <div><span id="default-message" class="alert alert-secondary" style="padding:0px 6px; font-size:.7rem; border-radius:0"><i class="ri-refresh-line"></i> Auto generated password will send to email.</span>
                <span id="select-userType" class="alert alert-warning d-none" style="padding:0px 6px; font-size:.7rem; border-radius:0"><i class="ri-error-warning-fill"></i> Please select user type</span>
                <span id="invalid-email" class="alert alert-danger d-none" style="padding:0px 6px; font-size:.7rem; border-radius:0"><i class="ri-error-warning-fill"></i> Invalid email address!</span>
                <span id="existed-email" class="alert alert-danger d-none" style="padding:0px 6px; font-size:.7rem; border-radius:0"><i class="ri-error-warning-fill"></i> User with the email address already exist.</span>
                <span id="invalid-expertise" class="alert alert-danger d-none" style="padding:0px 6px; font-size:.7rem; border-radius:0"><i class="ri-error-warning-fill"></i> Select technician expertise!</span>

              </div>
              <div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i style="font-weight: 1000;" class="ri-close-fill"></i> Close</button>
                <button id="addUserBtn" type="submit" class="btn btn-primary"><i class="ri-checkbox-circle-fill"></i> Add</button>
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
        Added Successfully! Added : <span id="added-ticket-id"></span>
      </div>
    </div>
  </div>

  <?php include("../footer.php") ?>

</body>

<script>
  $(document).ready(function() {
    function toggleExpertiseRow() {
      if ($('#user_type').val() === 'technician') {
        $('.expertise-row').removeClass('d-none');
      } else {
        $('.expertise-row').addClass('d-none');
      }
    }

    // Initial check on page load
    toggleExpertiseRow();

    // Check on user type change
    $('#user_type').change(function() {
      toggleExpertiseRow();
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
    $('#add-users').submit(function(event) {
      // Prevent default form submission
      event.preventDefault();



      const toastLiveExample = document.getElementById('liveToast')


      function showToast() {
        const toastBootstrap = new bootstrap.Toast(toastLiveExample)
        toastBootstrap.show()
      }

      var btn = $('#addUserBtn');
      btn.html('<i class="ri-loader-2-line"></i> Checking').attr('disabled', true);
      setTimeout(function() {
        btn.html('<i class="ri-checkbox-circle-fill"></i> Save').attr('disabled', false);
      }, 1400);



      // Get form data
      var formData = new FormData(this);
      formData.append("add-user", true);




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
            $('#add-users')[0].reset();
            $('#added-ticket-id').text(res.id);
            $('#user_type').val('').prop('selected', true).trigger('change');
            $('#addUserM').modal('hide');
            $('#user-results').load("admins.php #user-results", function() {

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

            var email = res.email;
            var subject = res.subject;
            var message = res.message;
            $.ajax({
              url: 'send.php',
              method: 'POST',
              data: {
                email: email,
                subject: subject,
                message: message
              },
              success: function(response) {},
              error: function(xhr, status, error) {
                console.error(xhr.responseText); // Log any error

              }
            });

            setTimeout(showToast, 500);

          } else if (res.status == 101) {
            $('#select-userType').addClass('d-none');
            $('#invalid-email').addClass('d-none');
            $('#existed-email').addClass('d-none');
            $('#invalid-expertise').addClass('d-none');
            setTimeout(function() {
              $('#default-message').addClass('d-none');
              $('#select-userType').removeClass('d-none');
              setTimeout(function() {
                $('#default-message').removeClass('d-none');
                $('#select-userType').addClass('d-none');
              }, 1400);
            }, 100);
          } else if (res.status == 102) {
            $('#select-userType').addClass('d-none');
            $('#invalid-email').addClass('d-none');
            $('#existed-email').addClass('d-none');
            $('#invalid-expertise').addClass('d-none');
            setTimeout(function() {
              $('#default-message').addClass('d-none');
              $('#invalid-email').removeClass('d-none');
              setTimeout(function() {
                $('#default-message').removeClass('d-none');
                $('#invalid-email').addClass('d-none');
              }, 1400);
            }, 100);
          } else if (res.status == 103) {
            $('#select-userType').addClass('d-none');
            $('#invalid-email').addClass('d-none');
            $('#existed-email').addClass('d-none');
            $('#invalid-expertise').addClass('d-none');
            setTimeout(function() {
              $('#default-message').addClass('d-none');
              $('#existed-email').removeClass('d-none');
              setTimeout(function() {
                $('#default-message').removeClass('d-none');
                $('#existed-email').addClass('d-none');
              }, 1400);
            }, 100);
          } else if (res.status == 104) {
            $('#select-userType').addClass('d-none');
            $('#invalid-email').addClass('d-none');
            $('#existed-email').addClass('d-none');
            $('#invalid-expertise').addClass('d-none');
            setTimeout(function() {
              $('#default-message').addClass('d-none');
              $('#invalid-expertise').removeClass('d-none');
              setTimeout(function() {
                $('#default-message').removeClass('d-none');
                $('#invalid-expertise').addClass('d-none');
              }, 1400);
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
    $('.select_userType').select2({
      width: '100%',
      placeholder: 'User Type',
      dropdownParent: $("#addUserM"),
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

    $('.roles_filter').select2({
      width: '100%',
      placeholder: 'Roles',
    });



    $('#rolesFilter').on('change', function() {
      var selectedroles = $(this).val();

      table.column(6).search(selectedroles).draw();
    });

  });
</script>

</html>