<?php
include("../../includes/connections.php");
session_start();

if (!empty($_SESSION['logged_in'])) {
  
} else {
  header("location:../../index.php");
  die;
}

if (!isManagementRole((string) ($_SESSION['role'] ?? ''))) {
  http_response_code(403);
  exit('Forbidden.');
}

$user_id = $_SESSION['user_id'];
$user_role = normalizeUserRole((string) $_SESSION['role']);



?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Ticket Category</title>
  <?php include("../header.php") ?>
  <style>
    .dropdown-menu {
      --bs-dropdown-link-active-bg: rgb(140, 136, 102);
    }    .btn-add{
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
          <div class="card-header d-flex" style="justify-content: space-between;background-color:#a60212; <?php if (isManagementRole($user_role)) { ?> padding-top:28px; padding-bottom:28px; <?php } ?>">
            <h6 style="color:#ffffff;">User - Ticket Category</h6>
            <?php
            if (isManagementRole($user_role)) {
            ?>
              <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addCategoryM"><i class="ri-add-circle-fill"></i> Add Category</button>
            <?php
            }
            ?>
          </div>
          <div id="category-results">
          <div class="card-body" style="position: relative;">


            <table class="table table_display table-bordered">
              <thead>
                <tr>
                  <th style="width: 70px;">Category ID</th>
                  <th>Title</th>
                  <th style="width: 100px;">Tickets</th>
                </tr>
              </thead>
              <tbody>
                <?php
                
                  $query = "SELECT * FROM ticket_category ";
                  $result = mysqli_query($con, $query);
                
                if (mysqli_num_rows($result) > 0) {
                  while ($row = $result->fetch_assoc()) {
                    $category_id = $row['category_id'];
                    $category = $row['category'];
                    

                    $qproject = "Select * from tickets where category_id = $category_id";
                    $resultp = mysqli_query($con, $qproject);
                    $tickets = mysqli_num_rows($resultp);
                     
                    

                ?>
                    <tr>
                      <td class="td<?= $category_id ?>">#<?= sprintf("%04d", $category_id); ?></td>
                      <td class="td<?= $category_id ?>"><?= $category ?></td>
                      <td class="td<?= $category_id ?>" style="position:relative"><?=$tickets?>

                        <!-- Delete Button -->
                        <div class="btn-group dropstart" style="padding:0; position:absolute; top:0; right:12px">
                          <button type="button" class="btn" style="padding:0; border:0" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ri-more-2-line"></i>
                          </button>
                          <ul class="dropdown-menu mt-1" style="border-radius: 0; padding:0">

                            <?php if (isManagementRole($user_role)) { ?>
                              <a href="#" class="updateCategory" data-category-id='<?= $category_id ?>'>
                                <li style="font-size:.7rem; padding:6px 12px;" class="dropdown-item"><i style="font-weight: 1000; color:darkorange" class="ri-file-edit-line"></i> UPDATE CATEGORY</li>
                              </a>
                            <?php } ?>

                            <?php
                            if (isManagementRole($user_role)) {
                            ?>
                              <a href="../delete.php?deleteID=<?= $category_id ?>&from=category">
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


        </div></div>













      </main>
      <div class="overlay"></div>
    </div>

  </div>
  <!-- partial -->

  <div class="modal fade" id="addCategoryM" data-bs-backdrop="static" tabindex="-1" data-bs-keyboard="false" aria-labelledby="addCategoryMLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title" id="addCategoryMLabel">Add Category</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id='post-category'>
          <input type="hidden" name="m_categoryId" id="m_categoryId">
          <div class="modal-body">
            <div class="row">
              <div class="col-3">
                <label class='form-label'>Category</label>
              </div>
              <div class="col">
                <input name="category_title" id="categoryTitle" required autocomplete="off" placeholder="Category" class="form-inputs form-control form-control-sm focus-ring focus-ring-light" type="text">
              </div>
            </div>
            
            <div id="invalidCategory" class="alert alert-danger mt-2 mb-0 text-center d-none" style="opacity:.7; padding:0px 12px; font-size:.75rem; color:darkred; border-radius:3px">Category Already Exist!&nbsp;&nbsp;<i style="font-size:.8rem" class="ri-error-warning-line"></i></div>

          </div>
          <div class="modal-footer" style="justify-content: start;">

          
            <div style="display:flex; justify-content:space-between; width:100%">
            <div></div>
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
        <img style="height:.9rem; border-radius:50%" src="../tickets/images/success.png" alt="success">
        <strong style="font-size:.8rem" class="me-auto">&nbsp;SUCCESS</strong>
        <small>Just now</small>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body" style="font-size: .8rem;">
        Added Successfully! Added Category ID : <span id="added-category-id"></span>
      </div>
    </div>
  </div>

  <?php include("../footer.php") ?>

</body>

<script>
  $(document).ready(function() {
    // Listen for the modal's hidden.bs.modal event
    $('#addCategoryM').on('hidden.bs.modal', function(e) {

      $('#addCategoryBtn').html('<i class="ri-checkbox-circle-fill"></i> Save');

      $('#invalidCategory').addClass('d-none');
      $('#addCategoryMLabel').text('Add Category');
      // Reset the form or input values here
      $('#m_categoryId').val('');
      $('#categoryTitle').val('');
    });
  });
</script>

<script>
  $(document).on('click', '.updateCategory', function(e) {
    var category_id = $(this).data('category-id');

    $('#addCategoryBtn').html('<i class="ri-checkbox-circle-fill"></i> Update');
    $('#addCategoryMLabel').text('Update Category');

    console.log("Category ID:", category_id);
    $.ajax({
      type: "GET",
      url: "ajax.php?updateid=" + category_id,

      success: function(response) {

        var res = jQuery.parseJSON(response);

        if (res.status == 200) {


          
          $('#m_categoryId').val(res.data.category_id);
          $('#categoryTitle').val(res.data.category);
          


          $('#addCategoryM').modal('show');
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
      $('#invalidTechnician').addClass('d-none');
    });

    $('#selectStatus').change(function() {
      $('#invalidStatus').addClass('d-none');
    });


  });
</script>

<script>
  $(document).ready(function() {
    $('#post-category').submit(function(event) {
      // Prevent default form submission
      event.preventDefault();



      const toastLiveExample = document.getElementById('liveToast')


      function showToast() {
        const toastBootstrap = new bootstrap.Toast(toastLiveExample)
        toastBootstrap.show()
      }

      var btn = $('#addCategoryBtn');
      btn.html('<i class="ri-loader-2-line"></i> Loading').attr('disabled', true);
      setTimeout(function() {
        btn.html('<i class="ri-checkbox-circle-fill"></i> Save').attr('disabled', false);
      }, 1000);



      // Get form data
      var formData = new FormData(this);
      formData.append("post-category", true);

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
            $('#post-category')[0].reset();
            $('#added-category-id').text(res.id);
           
            $('#invalidCategory').addClass('d-none');
            $('#addCategoryM').modal('hide');
            $('#category-results').load("ticket-category.php #category-results", function() {

             
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


            });
            setTimeout(showToast, 500);




          } else if (res.status == 200) {
            $('#post-category')[0].reset();
            
            $('#invalidCategory').addClass('d-none');
           
            $('#addCategoryM').modal('hide');
            $('#category-results').load("ticket-category.php #category-results", function() {

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
             
            });



            setTimeout(function() {
              $('.td' + res.id).addClass('bg bg-dark-subtle');
              setTimeout(function() {
                $('.td' + res.id).removeClass('bg bg-dark-subtle');
              }, 1000);
            }, 100);

          } else if (res.status == 101) {
            $('#invalidCategory').addClass('d-none');
            setTimeout(function() {
              $('#invalidCategory').removeClass('d-none');
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
   

  });
</script>

</html>