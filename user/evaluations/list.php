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
    <title>Evaluations</title>
    <?php include("../header.php") ?>
    <style>
        .dropdown-menu {
            --bs-dropdown-link-active-bg: rgb(140, 136, 102);
        }

        table.dataTable tbody td {
            vertical-align: middle;
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
                    <div class="card-header d-flex" style="justify-content: space-between;  padding-top:28px; padding-bottom:28px;background-color:#a60212;">
                        <h6 style="color:#ffffff;">User - Evaluations</h6>
                    </div>
                    <div class="card-body" style="position: relative;">
                        <div id="evaluation-results">



                            <div class="d-flex" style="position:absolute; width: 160px; top:10px; left:155px; z-index:99999999999">
                                <select class="technicians_filter" id="techniciansFilter">
                                    <option value="">-</option>
                                    <option value=" ">All</option>

                                    <?php
                                    $queryExp = "SELECT * FROM users u LEFT JOIN users_info i ON u.user_id = i.user_id WHERE u.role = 'technician'";
                                    $resultExp = mysqli_query($con, $queryExp);
                                    if (mysqli_num_rows($resultExp) > 0) {
                                        while ($rowExp = $resultExp->fetch_assoc()) {

                                            $technicians = $rowExp['first_name'] . ' ' . $rowExp['last_name'];




                                    ?>
                                            <option style="white-space: nowrap;" value="<?= $technicians ?>"><?= $technicians ?></option>
                                    <?php
                                        }
                                    }
                                    ?>

                                </select>
                            </div>

                            <div class="d-flex" style="position:absolute; width: 150px; top:10px; right:210px; z-index:99999999999">
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
                                        <th>Technician</th>
                                        <th>Client</th>
                                        <th style="width:110px">Solution</th>
                                        <th style="width:120px">Customer Satisfaction</th>
                                        <th class="table-dark" style="width:110px">Average</th>
                                        <th>Comments or Suggestions</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT a.*, 
                    b.first_name AS technician_first_name, 
                    b.last_name AS technician_last_name, 
                    b.user_id AS technician_user_id, 
                    c.ticket_id AS ticket_id,
                    c.title AS ticket_title,
                    d.first_name AS client_first_name, 
                    d.last_name AS client_last_name
                  FROM evaluation a 
                  LEFT JOIN users_info b ON a.technician_id = b.user_id
                  LEFT JOIN tickets c ON a.ticket_id = c.ticket_id
                  LEFT JOIN users_info d ON a.client_id = d.user_id
                  ORDER BY a.evaluation_id DESC";
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
                                                return "Unknown";
                                        }
                                    }


                                    function getAverageDisplayValue($value)
                                    {
                                        if ($value >= 1 && $value <= 1.49) {
                                            return $value . " - Highly Acceptable";
                                        } elseif ($value >= 1.50 && $value <= 2.49) {
                                            return $value . " - Acceptable";
                                        } elseif ($value >= 2.50 && $value <= 3.49) {
                                            return $value . " - Moderately Acceptable";
                                        } elseif ($value >= 3.50 && $value <= 4.49) {
                                            return $value . " - Unacceptable";
                                        } elseif ($value >= 4.50 && $value <= 5) {
                                            return $value . " - Highly Unacceptable";
                                        } else {
                                            return "Unknown";
                                        }
                                    }

                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $evaluation_id = $row['evaluation_id'];
                                            $ticket_id = $row['ticket_id'];
                                            $ticket_title = $row['ticket_title'];
                                            $technician = $row['technician_first_name'] . ' ' . $row['technician_last_name'];
                                            $technician_id = $row['technician_user_id'];
                                            $client = $row['client_first_name'] . ' ' . $row['client_last_name'];
                                            $solution = $row['solution'];
                                            $customer_satisfaction = $row['customer_satisfaction'];
                                            $average = number_format(($solution + $customer_satisfaction) / 2, 2);
                                            $comments = $row['comments'];
                                            $date_stamp = $row['date_stamp'];



                                            $solutionDisplay = getDisplayValue($solution);
                                            $customerSatisfactionDisplay = getDisplayValue($customer_satisfaction);


                                            $averageDisplay = getAverageDisplayValue($average);


                                    ?>
                                            <tr>
                                                <td class="td<?= $evaluation_id ?>"><a href='../tickets/view.php?ticketID=<?= $ticket_id ?>'>Ticket #<?= sprintf("%04d", $ticket_id) . '<br>' . $ticket_title ?></a></td>
                                                <td class="td<?= $evaluation_id ?>"><a href='../users/profile.php?id=<?= $technician_id ?>'><?= $technician ?></a></td>
                                                <td class="td<?= $evaluation_id ?>"><?= $client ?></td>
                                                <td class="td<?= $evaluation_id ?>"><?= $solutionDisplay ?></td>
                                                <td class="td<?= $evaluation_id ?>"><?= $customerSatisfactionDisplay ?></td>
                                                <td class="table-secondary td<?= $evaluation_id ?>"><?= $averageDisplay ?></td>
                                                <td class="td<?= $evaluation_id ?>"><?= $comments ?></td>
                                                <td class="td<?= $evaluation_id ?>"><?= $date_stamp ?></td>

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











    <?php include("../footer.php") ?>

</body>



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


        $('.technicians_filter').select2({
            width: '100%',
            placeholder: 'Technician',
        });

        $('.average_filter').select2({
            width: '100%',
            placeholder: 'Average',
        });

        $('#averageFilter').on('change', function() {
            var selectedAverage = $(this).val().trim();

        if (selectedAverage === "" || selectedAverage === " ") {
            table.column(5).search("").draw();
        } else {
            // Regex to match only the category part after the dash
            var regexPattern = '^\\d+\\.\\d+\\s-\\s' + selectedAverage.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&') + '$';
            table.column(5).search(regexPattern, true, false).draw();
        }
        });

        $('#techniciansFilter').on('change', function() {
            var selectedTechnician = $(this).val();

            table.column(1).search(selectedTechnician).draw();
        });



    });
</script>

</html>