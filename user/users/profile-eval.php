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


$queryDateMonth = "Select * from evaluation_date where user_id = $user_id";
$resultDateMonth = mysqli_query($con, $queryDateMonth);
if (mysqli_num_rows($resultDateMonth) > 0) {

    while ($rowDateMonth = $resultDateMonth->fetch_assoc()) {
        $dateMonth = isset($rowDateMonth['date_month']) ? $rowDateMonth['date_month'] : '';

        if ($dateMonth == ''|| $dateMonth == ' ') {
            $formattedDateMonth = '';
        } else {
            $date = DateTime::createFromFormat('Y-m', $dateMonth);
            $formattedDateMonth = $date->format('F Y'); // e.g., August 2024
        }
    }
} else {

    $queryAddDate = "INSERT INTO evaluation_date (user_id, date_month) 
    VALUES ('$user_id', '')";
    $resultAddDate = mysqli_query($con, $queryAddDate);
    $dateMonth = '';
}

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


            if ($expertise != 0) {
                $queryExpe = "Select * from ticket_category where category_id = $expertise";
                $resultExpe = mysqli_query($con, $queryExpe);
                if (mysqli_num_rows($resultExpe) > 0) {
                    while ($rowExpe = $resultExpe->fetch_assoc()) {
                        $technician_expertise = $rowExpe['category'];
                    }
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
          background-color:#a60212;
            color: #fff;
          border-left: 1px solid #ffffff;
        }

  span.email, span.label, span.company, span.address{
             color: #fff;
    font-weight: 600 !important;  
        }
  .gen-info-social i {
            font-size: 1.2rem;
            line-height: 0;
            color: #f5ab2c;
        }

        .gen-info-social:hover i {
            color: #fff;
        }

        }
.ms-5{
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
                                            <a href="profile.php?id=<?= $profile_id ?>" id="nav-tickets" class="nav-link ">Tickets</a>
                                        </li>
                                        <li class="nav-item">
                                            <a id="nav-evaluations" class="nav-link active" aria-current="page">Evaluations</a>
                                        </li>
                                    </ul>
                                </div>

                                <div style="position: relative;">
                                    <div class="d-flex" style="position:absolute; width: 150px; top:-15px; right:24px; z-index:99999999999">
                                        <select class="date_filter" id="dateFilter">
                                            <option value="">-</option>
                                            <option value=" ">All</option>
                                            <?php
                                            $queryDate = "SELECT date_stamp FROM evaluation WHERE technician_id = $profile_id";
                                            $resultDate = mysqli_query($con, $queryDate);

                                            $uniqueDates = array(); // Array to store unique month-year combinations

                                            if (mysqli_num_rows($resultDate) > 0) {
                                                while ($rowDate = $resultDate->fetch_assoc()) {
                                                    $dateValue = $rowDate['date_stamp']; // Adjust the column name accordingly
                                                    $dateTime = new DateTime($dateValue);
                                                    $formattedDate = $dateTime->format('Y-m'); // Format as "Year-Month" for sorting

                                                    // Add to array if not already present
                                                    if (!isset($uniqueDates[$formattedDate])) {
                                                        $uniqueDates[$formattedDate] = $dateTime->format('F Y'); // Store formatted date
                                                    }
                                                }

                                                // Sort dates
                                                ksort($uniqueDates); // Sort by keys which are "Year-Month"

                                                // Display options
                                                foreach ($uniqueDates as $key => $date) {
                                                    $formattedOption = htmlspecialchars($date); // Sanitize output
                                                    echo "<option ";
                                                    if ($dateMonth == $key) {
                                                        echo 'selected';
                                                    }
                                                    echo " value=\"$key\">$formattedOption</option>";
                                                }
                                            }
                                            ?>



                                        </select>
                                    </div>
                                </div>


                            </nav>


                        </div>



                        <!-- tickets -->
                        <div id="evaluation-results">

                            <div class="tickets mt-2" style="position: relative; padding-top:11px;">



                                <div class="d-flex" style="position:absolute; width: 150px; top:10px; right:205px; z-index:99999999999">
                                    <select class="average_filter" id="averageFilter">
                                        <option value="">-</option>
                                        <option value=" ">All</option>
                                        <option value="Highly Acceptable">Highly Acceptable</option>
                                        <option value="Acceptable">Acceptable</option>
                                        <option value="Moderately Acceptable">Moderately Acceptable</option>
                                        <option value="Unacceptable">Unacceptable</option>
                                        <option value="Highly Unacceptable">Highly Unacceptable</option>



                                    </select>
                                </div>

                                <table class="table table_display table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Ticket ID</th>

                                            <th>Client</th>
                                            <th style="width:130px">Solution</th>
                                            <th style="width:130px">Customer Satisfaction</th>
                                            <th class="table-dark" style="width:130px">Average</th>
                                            <th>Comments or Suggestions</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php


                                        if ($dateMonth == '' || $dateMonth == ' ') {
                                               // No filtering applied if dateMonth is empty
                                               $query = "
                                               SELECT a.*, 
                                                      c.ticket_id AS ticket_id,
                                                      c.title AS ticket_title,
                                                      d.first_name AS client_first_name, 
                                                      d.last_name AS client_last_name
                                               FROM evaluation a 
                                               LEFT JOIN tickets c ON a.ticket_id = c.ticket_id
                                               LEFT JOIN users_info d ON a.client_id = d.user_id
                                               WHERE technician_id = $profile_id
                                               ORDER BY a.evaluation_id DESC
                                           ";
                                        } else {
                                        



     // Split date_month into year and month
     list($selectedYear, $selectedMonth) = explode('-', $dateMonth);

     // Construct the query to filter by month and year
     $query = "
SELECT a.*, 
c.ticket_id AS ticket_id,
c.title AS ticket_title,
d.first_name AS client_first_name, 
d.last_name AS client_last_name
FROM evaluation a 
LEFT JOIN tickets c ON a.ticket_id = c.ticket_id
LEFT JOIN users_info d ON a.client_id = d.user_id
WHERE technician_id = $profile_id
AND MONTH(STR_TO_DATE(a.date_stamp, '%m/%d/%Y %h:%i %p')) = $selectedMonth
AND YEAR(STR_TO_DATE(a.date_stamp, '%m/%d/%Y %h:%i %p')) = $selectedYear
ORDER BY a.evaluation_id DESC
";
                                        }




                                        $result = mysqli_query($con, $query);

                                        function getDisplayValue($value)
                                        {
                                            switch ($value) {
                                                case 1:
                                                    return "1 - Highly Acceptable";
                                                case 2:
                                                    return "2 - Acceptable";
                                                case 3:
                                                    return "3 - Moderately Acceptable";
                                                case 4:
                                                    return "4 - Unacceptable";
                                                case 5:
                                                    return "5 - Highly Unacceptable";
                                                default:
                                                    return "N/A";
                                            }
                                        }

                                        $tableClass = '';
                                        function getAverageDisplayValue($value)
                                        {
                                            if ($value >= 1 && $value <= 1.49) {
                                                return [$value . " - Highly Acceptable", "d4edda"];
                                            } elseif ($value >= 1.50 && $value <= 2.49) {
                                                return [$value . " - Acceptable", "d4edda"];
                                            } elseif ($value >= 2.50 && $value <= 3.49) {
                                                return [$value . " - Moderately Acceptable", "e9ecef"];
                                            } elseif ($value >= 3.50 && $value <= 4.49) {
                                                return [$value . " - Unacceptable", "f8d7da"];
                                            } elseif ($value >= 4.50 && $value <= 5) {
                                                return [$value . " - Highly Unacceptable", "f8d7da"];
                                            } else {
                                                return ["N/A", ""];
                                            }
                                        }

                                        $totalSolution = 0;
                                        $totalCustomerSatisfaction = 0;
                                        $totalAverage = 0;
                                        $count = 0;

                                        if (mysqli_num_rows($result) > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                                $evaluation_id = $row['evaluation_id'];
                                                $ticket_id = $row['ticket_id'];
                                                $ticket_title = $row['ticket_title'];
                                                $client = $row['client_first_name'] . ' ' . $row['client_last_name'];
                                                $solution = $row['solution'];
                                                $customer_satisfaction = $row['customer_satisfaction'];
                                                $average = number_format(($solution + $customer_satisfaction) / 2, 2);
                                                $comments = $row['comments'];
                                                $date_stamp = $row['date_stamp'];

                                                $totalSolution += $solution;
                                                $totalCustomerSatisfaction += $customer_satisfaction;
                                                $totalAverage += $average;
                                                $count++;

                                                $solutionDisplay = getDisplayValue($solution);
                                                $customerSatisfactionDisplay = getDisplayValue($customer_satisfaction);


                                                list($averageDisplay, $tableClass) = getAverageDisplayValue($average);


                                        ?>
                                                <tr>
                                                    <td class="td<?= $evaluation_id ?>"><a href='../tickets/view.php?ticketID=<?= $ticket_id ?>'>Ticket #<?= sprintf("%04d", $ticket_id) . '<br>' . $ticket_title ?></a></td>

                                                    <td class="td<?= $evaluation_id ?>"><?= $client ?></td>
                                                    <td class="td<?= $evaluation_id ?>"><?= $solutionDisplay ?></td>
                                                    <td class="td<?= $evaluation_id ?>"><?= $customerSatisfactionDisplay ?></td>
                                                    <td style="border-left:8px solid #<?= $tableClass ?>; padding-left:3px" class=" td<?= $evaluation_id ?>"><?= $averageDisplay ?></td>
                                                    <td class="td<?= $evaluation_id ?>"><?= $comments ?></td>
                                                    <td class="td<?= $evaluation_id ?>"><?= $date_stamp ?></td>

                                                </tr>

                                            <?php
                                            } ?>

                                        <?php
                                        }
                                        $overallSolution = $count > 0 ? number_format($totalSolution / $count, 2) : 0;
                                        $overallCustomerSatisfaction = $count > 0 ? number_format($totalCustomerSatisfaction / $count, 2) : 0;
                                        $overallAverage = $count > 0 ? number_format($totalAverage / $count, 2) : 0;
                                        ?>

                                    </tbody>
                                    <tr>
                                        <td colspan="2"><b><?=$formattedDateMonth?> OVERALL</b></td>
                                        <?php
                                        list($overallSolution, $tableClass) = getAverageDisplayValue($overallSolution);
                                        ?>
                                        <td style="border-left:8px solid #<?= $tableClass ?>; padding-left:3px"><b><?= $overallSolution ?></b></td>
                                        <?php
                                        list($overallCustomerSatisfaction, $tableClass) = getAverageDisplayValue($overallCustomerSatisfaction);
                                        ?>
                                        <td style="border-left:8px solid #<?= $tableClass ?>; padding-left:3px"><b><?= $overallCustomerSatisfaction ?></b></td>
                                        <?php
                                        list($overallAverage, $tableClass) = getAverageDisplayValue($overallAverage);
                                        ?>
                                        <td style="border-left:8px solid #<?= $tableClass ?>; padding-left:3px"><b><?= $overallAverage ?></b></td>
                                        <td colspan="2"></td>
                                    </tr>
                                </table>

                            </div>

                        </div><!-- tickets -->

                    </div>


                    <!-- Content -->



                </div>












            </main>

            <div class="overlay"></div>

        </div>

    </div>

    <!-- partial -->













    <?php include("../footer.php") ?>

</body>




<script type="text/javascript">
    $(document).ready(function() {

        var profileId = <?php echo json_encode($profile_id); ?>;
        // Initial load of data

        // Function to handle AJAX request
        function load_data(input, user_ids) {
            $.ajax({
                url: "ajax.php",
                method: "POST",
                data: {
                    selected_date: input,
                    user_ids: user_ids
                },
                success: function(data) {
                    // Optional: Handle success response if needed
                    console.log("Data updated successfully" + profileId);

                    $('#evaluation-results').load("profile-eval.php?id="+profileId+" #evaluation-results", function() {

                        var table = $('.table_display').DataTable({
                            "lengthMenu": [
                                [5, 10, 25, 50, -1],
                                [5, 10, 25, 50, "All"]
                            ],
                            "pageLength": 5,
                            "order": [
                                [6, 'desc']
                            ],

                        });


                        $('.technicians_filter').select2({
                            width: '100%',
                            placeholder: 'Technician',
                        });

                        $('.average_filter').select2({
                            width: '100%',
                            placeholder: 'Average',
                        });

                        $('.date_filter').select2({
                            width: '100%',
                            placeholder: 'Select Month',
                        });

                        $('#averageFilter').on('change', function() {
                            var selectedAverage = $(this).val().trim();

                            if (selectedAverage === "" || selectedAverage === " ") {
                                table.column(4).search("").draw();
                            } else {
                                // Regex to match only the category part after the dash
                                var regexPattern = '^\\d+\\.\\d+\\s-\\s' + selectedAverage.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&') + '$';
                                table.column(4).search(regexPattern, true, false).draw();
                            }
                        });

                        $('#techniciansFilter').on('change', function() {
                            var selectedTechnician = $(this).val();

                            table.column(1).search(selectedTechnician).draw();
                        });


                    });
                },
                error: function(xhr, status, error) {
                    // Optional: Handle error response if needed
                    console.error("Error updating data: " + error);
                }
            });
        }

        // Event handler for input changes
        $("#dateFilter").change(function() {
            var input = $(this).val();
            var userIds = <?= json_encode($user_id) ?>; // Ensure statement_id is properly echoed

            if (input !== "") {
                load_data(input, userIds);
            } else {
                load_data(input, userIds);
            }
        });

    });
</script>







<script>
    $(document).ready(function() {
        var table = $('.table_display').DataTable({
            "lengthMenu": [
                [5, 10, 25, 50, -1],
                [5, 10, 25, 50, "All"]
            ],
            "pageLength": 5,
            "order": [
                [6, 'desc']
            ],

        });


        $('.technicians_filter').select2({
            width: '100%',
            placeholder: 'Technician',
        });

        $('.average_filter').select2({
            width: '100%',
            placeholder: 'Average',
        });

        $('.date_filter').select2({
            width: '100%',
            placeholder: 'Select Month',
        });

        $('#averageFilter').on('change', function() {
            var selectedAverage = $(this).val().trim();

            if (selectedAverage === "" || selectedAverage === " ") {
                table.column(4).search("").draw();
            } else {
                // Regex to match only the category part after the dash
                var regexPattern = '^\\d+\\.\\d+\\s-\\s' + selectedAverage.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&') + '$';
                table.column(4).search(regexPattern, true, false).draw();
            }
        });

        $('#techniciansFilter').on('change', function() {
            var selectedTechnician = $(this).val();

            table.column(1).search(selectedTechnician).draw();
        });



    });
</script>

</html>