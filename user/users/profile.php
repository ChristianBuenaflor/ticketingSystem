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



if (isset($_GET['id'])) {

    $profile_id = $_GET['id'];

    $query = "Select * from users where user_id = $profile_id";
    $result = mysqli_query($con, $query);
    if (mysqli_num_rows($result) > 0) {
        while ($row = $result->fetch_assoc()) {
            $roles = $row['role'];
            $email = $row['email'];
        }
    }

    $queryPf = "Select * from users_info where user_id = $profile_id";
    $resultPf = mysqli_query($con, $queryPf);
    if (mysqli_num_rows($resultPf) > 0) {
        while ($rowPf = $resultPf->fetch_assoc()) {
            $last_name = $rowPf['last_name'];
            $first_name = $rowPf['first_name'];
            $phone = $rowPf['phone'];
            $profile = $rowPf['profile'];
            $profile_type = $rowPf['profile_type'];
            if (empty($profile)) {
                $profile = file_get_contents('Default_pfp.png');
            }
            $skype = $rowPf['skype'];
            $job_title = $rowPf['job_title'];
            $expertise = $rowPf['expertise'];


           $technician_expertise = '';
            if (!empty($expertise)) {
            $expertise_ids = array_filter(array_map('intval', explode(',', $expertise))); // clean and convert to integers

            if (!empty($expertise_ids)) {
                $in_clause = implode(',', $expertise_ids);
                $queryExpe = "SELECT category FROM ticket_category WHERE category_id IN ($in_clause)";
                $resultExpe = mysqli_query($con, $queryExpe);

                $categories = [];
                while ($rowExpe = mysqli_fetch_assoc($resultExpe)) {
                    $categories[] = $rowExpe['category'];
                }

                $technician_expertise = implode(', ', $categories); // e.g. "Networking, CCTV, Printer"
            }
        }




            $gender = $rowPf['gender'];
            $company_name = $rowPf['company_name'];
            $address = $rowPf['address'];
            $city = $rowPf['city'];
            $state = $rowPf['state'];
            $zip = $rowPf['zip'];
            $country = $rowPf['country'];
            $c_phone = $rowPf['c_phone'];
            $website = $rowPf['website'];
            $vat_number = $rowPf['vat_number'];
            $telegram_1 = $rowPf['telegram_1'];
            $telegram_link_1 = $rowPf['telegram_link_1'];
            $telegram_2 = $rowPf['telegram_2'];
            $telegram_link_2 = $rowPf['telegram_link_2'];
            $pinterest = $rowPf['pinterest'];
            $instagram = $rowPf['instagram'];
            $github = $rowPf['github'];
            $tumbir = $rowPf['tumbir'];
        }
    }
}







?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>User Profile</title>
    <?php include("../header.php") ?>
    <style>
        .profile {
            height: 200px;
            padding-top: 5px;
           background-color:#a60212;"
            color: #fff;
            border-left: 1px solid #ffffff;
        }
        
        span.email, span.label, span.company, span.address{
             color: #fff;
    font-weight: 600 !important;  
        }

.ms-5{
    color: #fff;
    font-weight: 600 !important;
}
.mt-2{
       color: #fff;
    font-weight: 600 !important;
}
        .nav-item .nav-link {
            font-size: .7rem;
        }

        .nav-item .nav-link {
            padding: 14px;
            position: relative;
            cursor: pointer;
        }


        .nav-item .nav-link::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background-color: #43727a;
            transition: width 0.3s ease;
        }

        .nav-item .nav-link:hover::before,
        .nav-item .nav-link.active::before {
            width: 100%;

        }

        h5 {
            margin: 0;
            font-size: 1rem;
        }

        .card-footer {
            border-top: 0;
            background-color: #fff;
        }

        .card-header {
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding-left: 0;
        }

        .card-body label {
            font-size: .7rem;
            padding-left: 15px;
        }

        .card-body .row {
            padding: 12px 0px;
            border-bottom: 1px dashed rgba(0, 0, 0, 0.1);
        }

        input.form-control-sm.focus-ring-light,
        textarea.form-control-sm.focus-ring-light {
            border: 1px solid transparent;
            font-size: .7rem;
        }

        .profile .btn i {
            font-size: 1.2rem;
        }

        .profile .btn {
            color: #fff;
        }

        .profile .btn:hover {
            color: #c2c2c2;

        }

        .profile-picture img {
            height: 120px;
            width: 120px;
            background-color: transparent;
            border-radius: 50%;
            object-fit: cover;
        }

        .profile span {
            font-size: .8rem;
            letter-spacing: .5px;
            font-weight: 100;
        }

        .badge {
            font-size: .7rem !important;
        }

        span.email,
        span.label,
        span.company,
        span.address {
            font-size: .65rem;
        }


        .social-icons i {
            font-size: 1.7rem;
            color: #f5ab2c;
            border-radius: 50%;
            padding: 2px;
            border: 1px solid rgb(140, 136, 102);

        }

        .social-icons:hover i {
            color: #fff;
            border: 1px solid #fff;
        }

     
  .gen-info-social i {
            font-size: 1.2rem;
            line-height: 0;
            color: #f5ab2c;
        }

        .gen-info-social:hover i {
            color: #fff;
        }


        .invalid-pass {
            color: darkred;
        }

        .valid-pass {
            color: darkslategray;

        }

        table.dataTable tbody td {
            vertical-align: middle;
        }

        .notEvaluated:hover{
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
            <main class="content" style="padding:0px 12px">
                <div>
                    <a id="btn-toggle" href="#" class="sidebar-toggler break-point-sm">
                        <i class="ri-menu-line ri-xl"></i>
                    </a>
                </div>


                <div id="my-profile">
                    <!-- form -->
                    <div class="refresh-my-profile">
                        <div class="row profile d-flex">
                            <div class="col d-flex align-items-start gap-4">
                                <div class="text-center mt-3" style="max-width:200px">
                                    <div class="profile-picture mb-2 ms-5">
                                        <?php echo '<img id="profile-image" src="data:' . $profile_type . ';base64,' . base64_encode($profile) . '" alt="profile">'; ?>
                                    </div>
                                    <span class="ms-5" style='white-space:nowrap'><?= $first_name . " " . $last_name ?></span><br><span style="font-size:10px" class="ms-5">(<?= $roles ?>)</span>
                                </div>
                                <div class="col-3 mt-4 ps-4">
                                    <div class="d-flex">
                                        <?php
                                        function generateSkypeLink($skype)
                                        {
                                            return "skype:$skype?chat";
                                        }
                                        $skypeLink = generateSkypeLink($skype);
                                        ?>
                                        <span class="badge mb-3 me-2"><?= $job_title ?></span>
                                        <?php if ($phone != "") { ?>
                                            <span class="mb-3 gen-info-social me-1"><a title="<?= $phone ?>" href="tel:<?= $phone ?>"><i class="ri-smartphone-line"></i></a></span>
                                        <?php }
                                        if ($skype != "") { ?>
                                            <span class="mb-3 gen-info-social"><a title="<?= $skype ?>" href="<?= 'skype:' . $skype . '?chat'; ?>"><i class="ri-skype-fill"></i></a></span>
                                        <?php } ?>
                                    </div>
                                    <div>
                                        <a style="color:#fff;" href="mailto:<?= $email ?>"><span class="email"><i class="ri-mail-line"></i>&nbsp; <?= $email ?></span></a>
                                    </div>
                                    <div class="mt-3 d-block">
                                        <?php
                                        if ($telegram_link_1 != "") {
                                        ?>
                                            <a title="<?= $telegram_1 ?>" href="<?php if (substr($telegram_link_1, 0, 8) == 'https://') {
                                                                                    echo $telegram_link_1;
                                                                                } else {
                                                                                    echo 'https://' . $telegram_link_1;
                                                                                } ?>" target="_blank" class="me-2 social-icons"><i class="ri-telegram-fill"></i></a>
                                        <?php
                                        }
                                        if ($telegram_link_2 != "") {
                                        ?>
                                            <a title="<?= $telegram_2 ?>" href="<?php if (substr($telegram_link_2, 0, 8) == 'https://') {
                                                                                    echo $telegram_link_2;
                                                                                } else {
                                                                                    echo 'https://' . $telegram_link_2;
                                                                                } ?>" target="_blank" class="me-2 social-icons"><i class="ri-telegram-fill"></i></a>

                                        <?php
                                        }
                                        if ($pinterest != "") {
                                        ?>
                                            <a title="<?= $pinterest ?>" href="<?php if (substr($pinterest, 0, 8) == 'https://') {
                                                                                    echo $pinterest;
                                                                                } else {
                                                                                    echo 'https://' . $pinterest;
                                                                                } ?>" target="_blank" class="me-2 social-icons"><i class="ri-pinterest-fill"></i></a>

                                        <?php
                                        }
                                        if ($instagram != "") {
                                        ?><a title="<?= $instagram ?>" href="<?php if (substr($instagram, 0, 8) == 'https://') {
                                                                                    echo $instagram;
                                                                                } else {
                                                                                    echo 'https://' . $instagram;
                                                                                } ?>" target="_blank" class="me-2 social-icons"><i class="ri-instagram-fill"></i></a>

                                        <?php
                                        }
                                        if ($github != "") {
                                        ?><a title="<?= $github ?>" href="<?php if (substr($github, 0, 8) == 'https://') {
                                                                                echo $github;
                                                                            } else {
                                                                                echo 'https://' . $github;
                                                                            } ?>" target="_blank" class="me-2 social-icons"><i class="ri-github-fill"></i></a>

                                        <?php
                                        }
                                        if ($tumbir != "") {
                                        ?><a title="<?= $tumbir ?>" href="<?php if (substr($tumbir, 0, 8) == 'https://') {
                                                                                echo $tumbir;
                                                                            } else {
                                                                                echo 'https://' . $tumbir;
                                                                            } ?>" target="_blank" class="me-2 social-icons"><i class="ri-tumblr-fill"></i></a>
                                        <?php } ?>
                                    </div>

                                    <div class="mt-2">
                                        <span style="font-size:10px">Expertise : </span><span class="bg bg-success" style="font-size:10px; padding: 1px 6px; border-radius:2px"><?= $technician_expertise ?></span>
                                    </div>

                                </div>
                                <div class="col mt-4 ps-4">
                                    <div class="mb-2">
                                        <?php
                                        if ($company_name != '') {
                                        ?>
                                            <span class="label">Company name:</span>&nbsp;&nbsp;<span class="company"><?= $company_name ?></span>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                    <div>
                                        <span class="address"><?= $address ?></span>
                                    </div>
                                </div>





                            </div>


                        </div>

                        <div class="row">
                            <nav class="navbar navbar-expand-lg bg-body-secondary" style="padding:0">
                                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                                    <span class="navbar-toggler-icon"></span>
                                </button>
                                <div class="collapse navbar-collapse" id="navbarNav">
                                    <ul class="navbar-nav gap-1">
                                        <li class="nav-item">
                                            <a id="nav-tickets" class="nav-link active" aria-current="page">Tickets</a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="profile-eval.php?id=<?=$profile_id?>" id="nav-evaluations" class="nav-link">Evaluations</a>
                                        </li>
                                    </ul>
                                </div>

                            </nav>
                        </div>



                        <!-- tickets -->
                        <div class="tickets mt-2" style="position: relative; padding-top:11px;">

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

                            <table class="table table_display table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Ticket ID</th>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Ticket Type</th>
                                        <th style="width: 70px;">Status</th>
                                        <th style="width: 100px;">Created<br>Last Activity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php


                                    $query = "SELECT * FROM tickets where technician = $profile_id";
                                    $result = mysqli_query($con, $query);

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
                                            $ticket_status = $row['status'];

                                            $qproject = "Select category from ticket_category where category_id = $category_id";
                                            $resultp = mysqli_query($con, $qproject);
                                            if (mysqli_num_rows($resultp) > 0) {
                                                while ($rowp = $resultp->fetch_assoc()) {
                                                    $category = $rowp['category'];
                                                }
                                            } else {
                                                $category = '-';
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

                                    ?>
                                            <tr>
                                                <td class="td<?= $ticket_id ?>"><a target="_blank" href='../tickets/view.php?ticketID=<?= $ticket_id ?>'>Ticket #<?= sprintf("%04d", $ticket_id); ?></a></td>
                                                <td class="td<?= $ticket_id ?>"><a target="_blank" href='../tickets/view.php?ticketID=<?= $ticket_id ?>'><?= $ticket_title ?></a></td>
                                                <td class="td<?= $ticket_id ?>"><?php if ($category_id != 0) {
                                                                                    echo $category;
                                                                                } else {
                                                                                    echo "-";
                                                                                } ?></td>
                                                <td class="td<?= $ticket_id ?>"><?= $ticket_type ?></td>
                                                <td class="td<?= $ticket_id ?>" style="position:relative">
                                                <?php
                          if($ticket_status == 'Closed')
                          {
                            

                $qChecks = "SELECT * FROM evaluation WHERE ticket_id = $ticket_id";
                $rChecks = mysqli_query($con, $qChecks);
                if (mysqli_num_rows($rChecks) == 0) {
                ?>

                      <span><i title="No Evaluation" style="font-size:12px;background-color:#fff3cd; color:#856404; border:1px solid #ffeeba; padding:2px; border-radius:50%" class="notEvaluated ri-error-warning-line"></i></span>&nbsp;
                      <?php 
                      
                    }
                }
                      ?>    
                                                <span style='font-size:10px!important' class="badge <?= $ticket_status ?>"><?= $ticket_status ?></span>
                                                    <?php
                                                    if ($ticket_status == 'Open' && $user_role == 'admin' || $ticket_status == 'Open' && $user_role == 'super_admin') {
                                                    ?>
                                                        &nbsp;&nbsp;<button data-ticket-id='<?= $ticket_id ?>' style="padding:0px 6px; border-radius:2px" class="btn btn-dark moveTicket"><i class="ri-share-forward-line"></i></button>
                                                    <?php
                                                    }
                                                    ?>



                                                </td>
                                                <td class="td<?= $ticket_id ?>"><?= $ticket_created ?><br><?= $last_activity ?></td>






                                            </tr>

                                    <?php
                                        }
                                    }

                                    ?>

                                </tbody>
                            </table>

                        </div>
                        <!-- tickets -->

                    </div>


                    <!-- Content -->



                </div>












            </main>

            <div class="overlay"></div>

        </div>

    </div>

    <!-- partial -->

    <!-- Modal share -->
    <div class="modal fade" id="moveTicketM" data-bs-backdrop="static" tabindex="-1" data-bs-keyboard="false" aria-labelledby="moveTicketMLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title" id="moveTicketMLabel">Move Ticket - <span id="moveTicketId"></span></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id='move-tickets'>
                    <div class="modal-body">
                        <input type="hidden" name="moveTicketsId" id="moveTicketsId">

                        <div class="row mt-2">
                            
                            <div class="col">
                                <select name="technician" id="technician" class="select_technician selectpicker">
                                    <option value="">-</option>

                                    <?php

                                    $queryMove = "SELECT * FROM users right join users_info on users.user_id = users_info.user_id where role = 'technician' order by users_info.expertise";
                                    $resultMove = mysqli_query($con, $queryMove);
                                    if (mysqli_num_rows($resultMove) > 0) {
                                        while ($rowMove = $resultMove->fetch_assoc()) {
                                            $technicianUserId = $rowMove['user_id'];
                                            $tech_name = $rowMove['last_name'] . ', ' . $rowMove['first_name'];
                                        $tech_expertise = $rowMove['expertise'];
                                            $tech_expert = '';

                                            if (!empty($tech_expertise)) {
                                                $expertise_ids = array_filter(array_map('intval', explode(',', $tech_expertise)));
                                                if (!empty($expertise_ids)) {
                                                    $in_clause = implode(',', $expertise_ids);

                                                    $queryExpertise = "SELECT category FROM ticket_category WHERE category_id IN ($in_clause)";
                                                    $resultExpertise = mysqli_query($con, $queryExpertise);

                                                    $categories = [];
                                                    while ($rowExpertise = mysqli_fetch_assoc($resultExpertise)) {
                                                        $categories[] = $rowExpertise['category'];
                                                    }

                                                    $tech_expert = implode(', ', $categories); // Example: "Networking, Printer, CCTV"
                                                }
                                            }

                                            $queryOpen = "SELECT * FROM tickets where technician = '$technicianUserId' and status = 'Open'";
                                            $resultOpen = mysqli_query($con, $queryOpen);
                                            $OpenTickets = mysqli_num_rows($resultOpen);

                                            $queryInprogress = "SELECT * FROM tickets where technician = '$technicianUserId' and status = 'In progress'";
                                            $resultInprogress = mysqli_query($con, $queryInprogress);
                                            $InprogressTickets = mysqli_num_rows($resultInprogress);





                                            if ($technicianUserId != $profile_id) {
                                    ?>
                                                <option value="<?= $technicianUserId ?>"><?= $tech_name ?>&nbsp;&nbsp;&nbsp;[ <?=$tech_expert?> ]&nbsp;&nbsp;Open Tickets: <?=$OpenTickets?>&nbsp;|&nbsp;In progress: <?=$InprogressTickets?></option>
                                    <?php
                                            }
                                        }
                                    }

                                    ?>

                                </select>
                            </div>
                        </div>



                    </div>
                    <div class="modal-footer" style="justify-content: start;">


                        <div style="display:flex; justify-content:space-between; width:100%">
                            <div>
                            </div>
                            <div>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i style="font-weight: 1000;" class="ri-close-fill"></i> Close</button>
                                <button disabled id="moveTicketBtn" type="submit" class="btn btn-primary"><i class="ri-checkbox-circle-fill"></i> Move</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal Share -->







    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header" style="border-radius: 12px;">
                <img style="height:.9rem; border-radius:50%" src="../tickets/images/success.png" alt="success">
                <strong style="font-size:.8rem" class="me-auto">&nbsp;UPDATED SUCCESSFULLY</strong>
                <small>Just now</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
















    <?php include("../footer.php") ?>

</body>

<script>
    $(document).on('click', '.moveTicket', function(e) {
        var ticket_id = $(this).data('ticket-id');


        console.log("Ticket ID:", ticket_id);
        $.ajax({
            type: "GET",
            url: "ajax.php?moveTicket=" + ticket_id,

            success: function(response) {

                var res = jQuery.parseJSON(response);

                if (res.status == 200) {

                    $('#moveTicketId').text(res.tickets_id);
                    $('#moveTicketsId').val(res.ticket_id);
                    $('#moveTicketM').modal('show');

                }


            }
        });


    });
</script>

<script>
  $(document).ready(function() {
    $('#move-tickets').submit(function(event) {
      // Prevent default form submission
      event.preventDefault();



      // Get form data
      var formData = new FormData(this);
      formData.append("move-ticket", true);




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
            location.reload();
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
    // Function to toggle the "Move" button based on the selected technician
    function toggleMoveButton() {
        if ($('#technician').val() === '') {
            $('#moveTicketBtn').attr('disabled', true);
        } else {
            $('#moveTicketBtn').attr('disabled', false);
        }
    }

    // Initially disable the "Move" button
    toggleMoveButton();

    // Add event listener to the select element
    $('#technician').change(function() {
        toggleMoveButton();
    });
});
</script>
<script>
    $(document).ready(function() {


        $('.status_filter').select2({
            width: '100%',
            placeholder: 'Status',
        });
        $('.type_filter').select2({
            width: '100%',
            placeholder: 'Type',
        });


        $('.select_technician').select2({
            width: '100%',
            placeholder: 'Select Technician',
            dropdownParent: $("#moveTicketM"),
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

            table.column(4).search(selectedStatus).draw(); // Assuming status column is the third column (index 2)
        });

        $('#ticketTypeFilter').on('change', function() {
            var selectedStatus = $(this).val();

            table.column(3).search(selectedStatus).draw(); // Assuming status column is the third column (index 2)
        });




    });
</script>

</html>