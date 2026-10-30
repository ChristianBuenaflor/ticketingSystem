<?php
include("../../includes/connections.php");
session_start();

if (!empty($_SESSION['logged_in'])) {
    if (!isManagementRole((string) ($_SESSION['role'] ?? ''))) {
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
        $userRole = normalizeUserRole($rowuP['role']);
    }
}



if (isManagementRole($userRole)) {
    $userProfile = file_get_contents('images/adminpfp.png');
}



if (isset($_GET['project'])) {
    $project_id = (int) $_GET['project'];
    if (!canAccessProject($con, $project_id, $userRole, (int) $user_id)) {
        header("Location: list.php");
        exit;
    }
    $_SESSION['project'] = $project_id;

    $queryx = "SELECT * FROM projects where project_id = $project_id";
    $resultx = mysqli_query($con, $queryx);
    if (mysqli_num_rows($resultx) > 0) {
        while ($rowx = $resultx->fetch_assoc()) {
            $project_title = $rowx['project_title'];
            $project_description = nl2br($rowx['project_description']);
        }
    }
}



?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>User - Projects</title>
    <?php include("../header.php") ?>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="magnific-popup/magnific-popup.css">

    <style>
        img.profiles {

            height: 40px;
            width: 40px;
            object-fit: cover;
            border-radius: 50%;

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



        .table-btn-edit {
            border-radius: 50%;
            border: 1px solid rgba(0, 0, 0, 0.1);
            padding: 0px 6px;
        }

        .table-btn-edit:hover {
            background-color: #092a2f;
            color: #fff;
        }

        .table-btn-delete {
            border-radius: 50%;
            border: 1px solid rgba(0, 0, 0, 0.1);
            padding: 0px 6px;
        }

        .table-btn-delete:hover {
            background-color: darkred;
            color: #fff;
        }

        .table-btn-delete-yes {
            border-radius: 0;
            border: 1px solid rgba(0, 0, 0, 0.1);
            padding: 0px 6px;
            margin: 0;
        }

        .table-btn-delete-yes:hover {
            background-color: darkred;
            color: #fff;
        }

        .table-btn-row {
            margin: 0px;
            padding: 1px;
        }

        table.dataTable tbody td {
            vertical-align: middle;
        }

        .row-kanban {
            overflow-x: auto;
            overflow-y: hidden;
            white-space: nowrap;
            padding-bottom: 10px;
            margin: 0;
            width: 100%;
            height: 400px;
            max-width: 1190px;
        }

        .col-kanban {
            display: inline-block;
            width: 270px;
            margin-right: 15px;
            margin-left: 15px;
            opacity: .85;
            vertical-align: top;
        }

        .card-kanban {
            color: #ffffff;
            padding: 12px 12px;
        }




        div.col-kanban span {
            font-size: .65rem;
        }

        div.card-kanban.tasks {
            background-color: #fff;
            color: #343a40;
            white-space: wrap;
        }

        div.card-kanban.tasks:hover {
            cursor: pointer;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        div.card-kanban.tasks img {
            width: 35px;
            height: 35px;
            object-fit: cover;
            border-radius: 50%;
        }

        div.task-div {
            max-height: 315px;
            min-height: 315px;
            overflow: auto;
        }

        div.task-div>div {
            margin-bottom: 15px;
        }

        div.task-div>div:last-child {
            margin-bottom: 0;
            /* Remove margin-bottom of the last div */
        }

        div.task-div::-webkit-scrollbar {
            width: 4px;
            /* Width of the scrollbar */
        }

        div.task-div::-webkit-scrollbar-track {
            background: #f1f1f1;
            /* Color of the track */
        }

        div.task-div::-webkit-scrollbar-thumb {
            background: rgba(140, 136, 102, 0.8);
            /* Color of the scrollbar handle */
        }

        div.task-div::-webkit-scrollbar-thumb:hover {
            background: #555;
            /* Color of the scrollbar handle on hover */
        }

        .invisible {
            opacity: 0.5;
        }

        .hovered {
            border: 2px dashed #000;
        }



        .share-icon {
            color: rgb(140, 136, 102);
        }

        .share-icon:hover {
            color: rgba(140, 136, 102, 0.8);
        }

        .task-profile img {
            object-fit: cover;
            height: 60px;
            width: 60px;
            border-radius: 50%;
        }


        .badge {
            margin-left: 12px;
            padding: 6px 12px;
            opacity: .8;
            height: 22px;
        }

        .badge.To.do {
            background-color: darkorange;
        }

        .badge.In.progress {
            background-color: darkcyan;
            color: #fff;
        }

        .badge.For.Checking {
            background-color: #483D8B;
        }

        .badge.Completed {
            background-color: #343a40;
        }

        .badge.Cancelled {
            background-color: darkred;
        }


        /* preview profile in file */
        .profiles_task_preview {
            height: 40px;
            width: 40px;
            object-fit: cover;
            border-radius: 50%;
        }


        img.attached-files {
            width: 406px;
            height: 240px;
            object-fit: cover;
            margin-bottom: 12px;
        }

        .gallery:hover img {
            filter: grayscale(80%);
        }



        .gallery {
            width: 406px;
            position: relative;
        }


        .projectCommentGallery:hover img {
            filter: grayscale(80%);
        }



        .projectCommentGallery {
            width: 406px;
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
            font-size: .65rem;
        }

        .comments-gallery:hover img {
            filter: grayscale(80%);
        }

        .comments-gallery {
            width: 406px;
            position: relative;
        }

        .comment-more-span {
            position: absolute;
            top: 0;
            right: 0;
            background-color: black;
            padding: 6px 12px;
            color: #fff;
            border-left: 5px solid rgb(140, 136, 102);
            font-size: .65rem;
        }


        .downloadtaskfiles a,
        a.files {
            color: #092A2F;
            font-size: .6rem;
        }

        .download a {
            color: #092A2F;
            font-size: .6rem;
        }

        .download a:hover {
            color: rgb(140, 136, 102);
        }

        .downloadcommenttaskfiles a,
        a.files {
            color: #092A2F;
            font-size: .6rem;
        }

        .downloadcommenttaskfiles a:hover,
        a.files:hover {
            color: rgb(140, 136, 102);
        }

        .downloadtaskfiles a:hover,
        a.files:hover {

            color: rgb(140, 136, 102);
        }






        .mfp-bg {
            z-index: 999999999;
            /* Adjust this value if necessary */
        }

        .mfp-wrap {
            z-index: 999999999;
            /* Adjust this value if necessary */
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
        #taskActionsDropdown .dropend {
            position: relative;
        }

        #taskActionsDropdown .task-move-menu {
            top: 0;
            right: 100%;
            left: auto;
            margin-right: .125rem;
        }

        @media (max-width: 575.98px) {
            #taskActionsDropdown .task-move-menu {
                position: static;
                margin-right: 0;
            }
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
                    <div class="card-header d-flex" style="justify-content: space-between;">
                        <h6 style="padding: 12px 0px"><i class="ri-server-line"></i> <?= $project_title ?></h6>

                    </div>
                    <div class="card-body" style="padding:0">
                        <nav class="navbar navbar-expand-lg bg-body-secondary" style="padding:0">
                            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon"></span>
                            </button>
                            <div class="collapse navbar-collapse" id="navbarNav">
                                <ul class="navbar-nav gap-1">
                                    <li class="nav-item">
                                        <a id="nav-overview" class="nav-link active" aria-current="page">Overview</a>
                                    </li>
                                    <li class="nav-item">
                                        <a id="nav-list" class="nav-link">Task List</a>
                                    </li>
                                    <li class="nav-item">
                                        <a id="nav-kanban" class="nav-link">Task Kanban</a>
                                    </li>
                                    <li class="nav-item">
                                        <a id="nav-files" class="nav-link">Files</a>
                                    </li>
                                    <li class="nav-item">
                                        <a id="nav-comments" class="nav-link">Comments</a>
                                    </li>
                                </ul>
                            </div>

                        </nav>
                    </div>

                </div>


                <!-- overview -->

                <div id="div-overview" class="row mt-4 card shadow-sm">
                    <div class="card-header d-flex" style="justify-content: space-between; padding: 10px 12px;">
                        <h6 style="padding: 12px 0px">Description</h6>

                    </div>

                    <div class="card-body" style="padding: 12px">
                        <span><?= $project_description ?></span>
                    </div>

                </div>

                <!-- overview -->













                <!-- Task List -->

                <div id="div-list" class="d-none row mt-4 card shadow-sm">
                    <div class="card-header d-flex <?php if (isManagementRole($userRole)) {
                                                        echo 'py-4';
                                                    } ?>" style="justify-content: space-between; padding: 6px 12px;">
                        <h6>Tasks</h6>
                        <?php if (isManagementRole($userRole)) { ?>
                            <button style="padding:6px 12px" class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addTaskM"><i class="ri-add-circle-fill"></i> Add Task</button>
                        <?php } ?>
                    </div>
                    <div class="card-body" style="position: relative;" id="task-results">


                        <div class="d-flex" style="position:absolute; width: 100px; top:10px; right:200px; z-index:999999999">
                            <select class="status_filter" id="statusFilter">
                                <option value="">-</option>
                                <option value=" ">All</option>
                                <option value="To do">To do</option>
                                <option value="In progress">In progress</option>
                                <option value="For Checking">For Checking</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>

                        <table id="task-table-list" class="table table_display table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th style="width: 300px;">Title</th>
                                    <th>Start Date</th>
                                    <th>Deadline</th>
                                    <th>Status</th>
                                    <th class="text-center"><i class="ri-menu-line"></i></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php


                                $project_id = $_SESSION['project'];

                                $query = "SELECT * FROM projects_task where project_id = $project_id";
                                $result = mysqli_query($con, $query);


                                $todoC = 1;
                                $inprogressC = 1;
                                $forcheckingC = 1;
                                $completedC = 1;
                                $cancelledC = 1;


                                if (mysqli_num_rows($result) > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $task_id = $row['task_id'];
                                        $task_userID = $row['user_id'];
                                        $task_title = $row['task_title'];
                                        $task_description = $row['description'];
                                        $task_status = $row['status'];
                                        $task_startDate = $row['start_date'];
                                        $task_deadline = $row['deadline'];
                                        $task_files = $row['files'];


                                        if ($task_startDate == '') {
                                            $task_startDate = '-';
                                        }

                                        if ($task_deadline == '') {
                                            $task_deadline = '-';
                                        }


                                ?>
                                        <tr>
                                            <td style="border-left: 5px solid <?php
                                                                                if ($task_status == 'To do') {
                                                                                    $todoC++;
                                                                                    echo 'darkorange';
                                                                                ?>
"><span><i style="font-size: 1.2rem; color:rgba(0, 0, 0, 0.2)" class="ri-checkbox-blank-line"></i>
                                                <?php
                                                                                } else if ($task_status == 'In progress') {

                                                                                    $inprogressC++;
                                                                                    echo 'darkcyan';
                                                ?>
                                                    "><span><i style="font-size: 1.2rem; color:rgba(0, 0, 0, 0.2)" class="ri-checkbox-blank-line"></i>
                                                    <?php
                                                                                } else if ($task_status == 'For Checking') {
                                                                                    $forcheckingC++;
                                                                                    echo '#483D8B';
                                                    ?>
                                                        "><span><i style="font-size: 1.2rem; color:rgba(0, 0, 0, 0.2)" class="ri-checkbox-blank-line"></i>
                                                        <?php
                                                                                } else if ($task_status == 'Completed') {
                                                                                    $completedC++;
                                                                                    echo '#343a40';
                                                        ?>
                                                            "><span><i style="font-size: 1.2rem; color:rgba(0, 0, 0, 0.2)" class="ri-checkbox-blank-line"></i>
                                                            <?php
                                                                                } else if ($task_status == 'Cancelled') {
                                                                                    $cancelledC++;
                                                                                    echo 'darkred';
                                                            ?>
                                                                "><span><i style="font-size: 1.2rem; color:rgba(0, 0, 0, 0.2)" class="ri-checkbox-blank-line"></i>
                                                                <?php
                                                                                } else {
                                                                ?>
                                                                    ">
                                                                    <?php
                                                                                }
                                                                    ?>&nbsp;<?= sprintf("%04d", $task_id); ?></td>
                                            <td><a href='#' class="previewTaskM" data-task-id="<?= $task_id ?>"><?= $task_title ?></a></td>
                                            <td><?= $task_startDate ?></td>
                                            <td><?= $task_deadline ?></td>
                                            <td><?= $task_status ?></td>
                                            <td class="text-center">


                                                <?php if (isManagementRole($userRole)) { ?>
                                                    <button class="btn table-btn-edit updateTask" data-task-id='<?= $task_id ?>'><i class="ri-edit-box-line"></i></button>&nbsp;
                                                <?php } ?>
                                                <button class="btn table-btn-delete deleteTask" data-task-id='<?= $task_id ?>'><i class="ri-delete-bin-5-line"></i></button>
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


                <!-- Task List -->



                <!-- Kanban List -->

                <div id="div-kanban" class="d-none row mt-4 card shadow-sm">
                    <div class="card-header d-flex <?php if (isManagementRole($userRole)) {
                                                        echo 'py-4';
                                                    } ?>" style="justify-content: space-between; padding: 6px 12px;">
                        <h6>Tasks Kanban</h6>
                        <?php if (isManagementRole($userRole)) { ?>
                            <button style="padding:6px 12px" class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addTaskM"><i class="ri-add-circle-fill"></i> Add Task</button>
                        <?php } ?>
                    </div>
                    <div class="card-body" style="padding:0" id="kanban-results">
                        <div class="row-kanban" style="background-color: rgba(0, 0, 0, 0.1); padding:14px 0px">

                            <!-- To do -->
                            <div class="col-kanban">

                                <div class="card-kanban" style="background-color: darkorange;">
                                    <span>To do</span>
                                </div>



                                <!-- task container -->

                                <div id="toDo" class="mt-3 task-div">



                                    <?php

                                    $project_id = $_SESSION['project'];

                                    $query = "SELECT * FROM projects_task left join users_info on projects_task.user_id = users_info.user_id where status = 'To do' and project_id = $project_id ORDER BY orders DESC";
                                    $result = mysqli_query($con, $query);
                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = $result->fetch_assoc()) {

                                            $taskIds = $row['task_id'];

                                            // $sqlu = "UPDATE projects_task SET orders = '$count' WHERE task_id = '$taskId'";
                                            // $resultu = mysqli_query($con, $sqlu);
                                            $profiles_type = $row['profile_type'];
                                            $profiles = $row['profile'];
                                            if (empty($profiles)) {
                                                $profiles = file_get_contents('images/Default_pfp.png');
                                            }
                                    ?>

                                            <div class="card-kanban tasks previewTaskM" draggable="true" data-taskid="<?= $row['task_id']; ?>" data-task-id="<?= $row['task_id']; ?>">

                                                <div class="d-flex gap-2">
                                                    <?php echo '<img src="data:' . $profiles_type . ';base64,' . base64_encode($profiles) . '" alt="">'; ?>
                                                    <span><?= $row['task_title']; ?></span>
                                                </div>

                                            </div>

                                    <?php
                                        }
                                    }
                                    ?>



                                </div>

                                <!-- task container -->



                            </div>
                            <!-- To do -->

                            <!-- In progress -->

                            <div class="col-kanban">

                                <div class="card-kanban" style="background-color: darkcyan;">
                                    <span>In progress</span>
                                </div>


                                <!-- task container -->
                                <div id="inProgress" class="mt-3 task-div">

                                    <?php
                                    $query = "SELECT * FROM projects_task left join users_info on projects_task.user_id = users_info.user_id where status = 'In progress' and project_id = $project_id ORDER BY orders DESC";
                                    $result = mysqli_query($con, $query);
                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $profiles_type = $row['profile_type'];
                                            $profiles = $row['profile'];
                                            if (empty($profiles)) {
                                                $profiles = file_get_contents('images/Default_pfp.png');
                                            }
                                    ?>

                                            <div class="card-kanban tasks previewTaskM" draggable="true" data-taskid="<?= $row['task_id']; ?>" data-task-id="<?= $row['task_id']; ?>">

                                                <div class="d-flex gap-2">
                                                    <?php echo '<img src="data:' . $profiles_type . ';base64,' . base64_encode($profiles) . '" alt="">'; ?>
                                                    <span><?= $row['task_title']; ?></span>
                                                </div>

                                            </div>
                                    <?php
                                        }
                                    }
                                    ?>

                                </div>

                                <!-- task container -->

                            </div>
                            <!-- In progress -->


                            <!-- For Checking -->
                            <div class="col-kanban">

                                <div class="card-kanban" style="background-color: #483D8B;">
                                    <span>For Checking</span>
                                </div>

                                <!-- task container -->
                                <div id="forChecking" class="mt-3 task-div">

                                    <?php
                                    $query = "SELECT * FROM projects_task left join users_info on projects_task.user_id = users_info.user_id where status = 'For Checking' and project_id = $project_id ORDER BY orders DESC";
                                    $result = mysqli_query($con, $query);
                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $profiles_type = $row['profile_type'];
                                            $profiles = $row['profile'];
                                            if (empty($profiles)) {
                                                $profiles = file_get_contents('images/Default_pfp.png');
                                            }
                                    ?>

                                            <div class="card-kanban tasks previewTaskM" draggable="true" data-taskid="<?= $row['task_id']; ?>" data-task-id="<?= $row['task_id']; ?>">

                                                <div class="d-flex gap-2">
                                                    <?php echo '<img src="data:' . $profiles_type . ';base64,' . base64_encode($profiles) . '" alt="">'; ?>
                                                    <span><?= $row['task_title']; ?></span>
                                                </div>

                                            </div>
                                    <?php
                                        }
                                    }
                                    ?>

                                </div>

                                <!-- task container -->

                            </div>
                            <!-- For Checking -->


                            <!-- Completed -->
                            <div class="col-kanban">

                                <div class="card-kanban" style="background-color: #343a40;">
                                    <span>Completed</span>
                                </div>

                                <!-- task container -->

                                <div id="completed" class="mt-3 task-div">

                                    <?php
                                    $query = "SELECT * FROM projects_task left join users_info on projects_task.user_id = users_info.user_id where status = 'Completed' and project_id = $project_id ORDER BY orders DESC";
                                    $result = mysqli_query($con, $query);
                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $profiles_type = $row['profile_type'];
                                            $profiles = $row['profile'];
                                            if (empty($profiles)) {
                                                $profiles = file_get_contents('images/Default_pfp.png');
                                            }
                                    ?>

                                            <div class="card-kanban tasks previewTaskM" draggable="true" data-taskid="<?= $row['task_id']; ?>" data-task-id="<?= $row['task_id']; ?>">

                                                <div class="d-flex gap-2">
                                                    <?php echo '<img src="data:' . $profiles_type . ';base64,' . base64_encode($profiles) . '" alt="">'; ?>
                                                    <span><?= $row['task_title']; ?></span>
                                                </div>

                                            </div>
                                    <?php
                                        }
                                    }
                                    ?>

                                </div>

                                <!-- task container -->

                            </div>
                            <!-- Completed -->

                            <!-- Cancelled -->
                            <div class="col-kanban">

                                <div class="card-kanban" style="background-color: darkred;">
                                    <span>Cancelled</span>
                                </div>

                                <!-- task container -->
                                <div id="cancelled" class="mt-3 task-div">

                                    <?php
                                    $query = "SELECT * FROM projects_task left join users_info on projects_task.user_id = users_info.user_id where status = 'Cancelled' and project_id = $project_id ORDER BY orders DESC";
                                    $result = mysqli_query($con, $query);
                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $profiles_type = $row['profile_type'];
                                            $profiles = $row['profile'];
                                            if (empty($profiles)) {
                                                $profiles = file_get_contents('images/Default_pfp.png');
                                            }
                                    ?>

                                            <div class="card-kanban tasks previewTaskM" draggable="true" data-taskid="<?= $row['task_id']; ?>" data-task-id="<?= $row['task_id']; ?>">

                                                <div class="d-flex gap-2">
                                                    <?php echo '<img src="data:' . $profiles_type . ';base64,' . base64_encode($profiles) . '" alt="">'; ?>
                                                    <span><?= $row['task_title']; ?></span>
                                                </div>

                                            </div>
                                    <?php
                                        }
                                    }
                                    ?>

                                </div>

                                <!-- task container -->

                            </div>
                            <!-- Cancelled -->

                        </div>
                    </div>



                </div>


                <!-- Kanban List -->



                <!-- File List -->

                <div id="div-files" class="d-none row mt-4 card shadow-sm">
                    <div class="card-header d-flex" style="justify-content: space-between; padding: 6px 12px;">
                        <h6>Files</h6>
                        <button style="padding:6px 12px" class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addFilesM"><i class="ri-add-circle-fill"></i> Add Files</button>
                    </div>
                    <div class="card-body" style="position: relative;" id="files-results">


                        <table id="files-table-list" class="table-hover table file_table_display table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>File</th>
                                    <th>File Size</th>
                                    <th class="text-center"><i class="ri-menu-line"></i></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php


                                $project_id = $_SESSION['project'];

                                $queryPF = "SELECT * FROM projects where project_id = $project_id";
                                $resultPF = mysqli_query($con, $queryPF);
                                if (mysqli_num_rows($resultPF) > 0) {
                                    while ($rowPF = $resultPF->fetch_assoc()) {
                                        $project_files = $rowPF['files'];

                                        if (!empty($project_files)) {

                                            $file = explode(',', $project_files);
                                            $file_count = count($file);
                                            for ($j = 0; $j < $file_count; $j++) {
                                                $project_file = $file[$j];
                                                $queryPFS = "SELECT * FROM files where id = $project_file";
                                                $resultPFS = mysqli_query($con, $queryPFS);
                                                if (mysqli_num_rows($resultPFS) > 0) {
                                                    while ($rowPFS = $resultPFS->fetch_assoc()) {
                                ?>
                                                        <tr>
                                                            <td>&nbsp;<?= sprintf("%04d", $rowPFS['id']); ?></td>
                                                            <td><a target="_blank" href="../../files/project/<?= $rowPFS['file_name'] ?>">
                                                                    <?= $rowPFS['file_name'] ?></a></td>
                                                            <td><?= $rowPFS['file_size'] ?></td>
                                                            <td class="text-center"><a target="_blank" onclick="javascript:return confirm('Delete file? file ID : <?= $rowPFS['id'] ?>');" href="../delete.php?deleteID=<?= $rowPFS['id'] ?>&from=files&project_id=<?= $project_id ?>" class="btn table-btn-delete deleteFile"><i class="ri-delete-bin-5-line"></i></a>
                                                            </td>
                                                        </tr>

                                        <?php
                                                    }
                                                }
                                            }
                                        }

                                        ?>

                                <?php
                                    }
                                }

                                ?>

                            </tbody>
                        </table>


                    </div>


                </div>


                <!-- File List -->



                <!-- comments -->

                <div id="div-comments" class="d-none row mt-4 card shadow-sm">

                    <div class="card-body" style="position: relative;<?php if (isManagementRole($userRole)) {
                                                                            echo ' padding-top:40px;';
                                                                        } ?>" id="project-comments-results">


                        <?php if (isManagementRole($userRole)) { ?>
                            <div class="d-flex gap-4 mt-3" style="padding:12px 12px;border-top:1px solid rgba(0,0,0,0.1);">
                                <div class="d-flex gap-3" style="width: 100%;">
                                    <div>

                                        <?php echo '<img class="profiles" src="data:' . $userProfileType . ';base64,' . base64_encode($userProfile) . '" alt="profile">'; ?>
                                    </div>
                                    <div style="width: 100%;">

                                        <form id="project-comment-form">
                                            <input type="hidden" name="project_comment_users_id" value="<?= $user_id ?>">
                                            <input type="hidden" name="project_comment_project_id" value="<?= $project_id ?>">
                                            <div>
                                                <textarea class="form-control form-control-sm focus-ring focus-ring-light comment" name="project_comment_textarea" id="projectcommentsTextarea" rows="4" placeholder="Write a comment..." autocomplete="off"></textarea>
                                            </div>
                                            <div class="modal-footer project-post-comment" style="justify-content: start; background-color: #f2f4f6; padding:8px 16px; border-radius:0">
                                                <div class="project-comment-file-preview"></div>
                                                <div style="display:flex; justify-content:space-between; width:100%;">
                                                    <div>
                                                        <button class="project-comment-upload-file-btn btn comment" type="button"><i style="font-size:.9rem" class="ri-camera-fill"></i>&nbsp; Upload File</button>
                                                        <input style="display:none" name="project_comment_files[]" type="file" id="projectcommentFileInput" multiple>
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
                        <?php } ?>








                        <!-- other coment -->
                        <div id="project-other-comments-results">

                            <?php
                            $qComments = "SELECT * FROM project_comments WHERE project_id = $project_id ORDER BY comment_id DESC";
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
                                    } else {
                                        $commenter_dp_type = 'image/*';
                                        $commenter_lname = 'Account';
                                        $commenter_fname = 'Deleted';
                                        $commenter_dp = file_get_contents('images/Default_pfp.png');
                                    }
                            ?>
                                    <div class="d-flex gap-4" style="padding: 12px 12px; border-top: 1px solid rgba(0,0,0,0.05); position:relative">
                                        <div class="d-flex gap-3">
                                            <div>
                                                <?php echo '<img class="profiles" src="data:' . $commenter_dp_type . ';base64,' . base64_encode($commenter_dp) . '" alt="profile">'; ?>
                                            </div>
                                            <div class="d-block">
                                                <div class="d-flex gap-3 mb-1">
                                                    <span><b><?= $commenter_fname . " " . $commenter_lname ?></b></span><span><?= $comment_date ?></span>

                                                    <?php
                                                    if ($user_id == $commenter_user_id || isManagementRole($userRole)) {
                                                    ?>
                                                        <!-- Delete Button -->
                                                        <div class="btn-group dropstart" style="padding:0; position:absolute; top:0; right:12px">
                                                            <button type="button" class="btn" style="padding:0; border:0" data-bs-toggle="dropdown" aria-expanded="false">
                                                                <i class="ri-more-2-line"></i>
                                                            </button>
                                                            <ul class="dropdown-menu mt-1" style="border-radius: 0; padding:0">
                                                                <a class="projectCommentsDeleteClicked" target="_blank" href="../delete.php?deleteID=<?= $comment_id ?>&from=projectComments">
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
                                                    <span><?= nl2br($commenter_comment); ?></span>
                                                </div>
                                                <div class="mb-2">

                                                    <div class="projectCommentGallery">
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
                                                                echo 'href="../../files/project/' . $comment_file . '"><img class="attached-files" src="../../files/project/' . $comment_file . '">';
                                                                if ($comment_image_count > 1) {
                                                        ?><span class="more-span">+<?= $comment_image_count - 1; ?> More</span><?php
                                                                                                                            }
                                                                                                                            echo '</a>';
                                                                                                                        }
















                                                                                                                                ?>




                                                    </div>
                                                    <div class="projectcommentfileList">
                                                        <?php
                                                            // Display non-image files
                                                            foreach ($non_image_files as $file) {
                                                                echo '<a target="_blank" class="files" href="../../files/project/' . $file['file_name'] . '">' . $file['file_name'] . '</a><br>';
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













        </div>


    </div>


    <!-- comments -->














    </main>
    <div class="overlay"></div>
    </div>

    </div>
    <!-- partial -->








    <?php if (isManagementRole($userRole)) { ?>
        <!-- Modal -->

        <div class="modal fade" id="addTaskM" data-bs-backdrop="static" tabindex="-1" data-bs-keyboard="false" aria-labelledby="addTaskMLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title" id="addTaskMLabel">Add Task</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id='post-task' enctype="multipart/form-data">
                        <input type="hidden" name="user_id" value="<?= $user_id ?>">
                        <input type="hidden" name="project_id" value="<?= $project_id ?>">
                        <input type="hidden" name="m_taskId" id="m_taskId">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-3">
                                    <label class='form-label'>Title</label>
                                </div>
                                <div class="col">
                                    <input name="task_title" id="taskTitle" autocomplete="off" placeholder="Title" class="form-inputs form-control form-control-sm focus-ring focus-ring-light" type="text">
                                </div>
                            </div>

                            <!-- -->
                            <div class="row mt-2">
                                <div class="col-3" style="display:flex;align-self: start;">
                                    <label class='form-label'>Description</label>
                                </div>
                                <div class="col">
                                    <textarea class="form-control form-control-sm focus-ring focus-ring-light" name="task_description" id="taskDescription" cols="30" rows="4" placeholder="Description" autocomplete="off"></textarea>
                                </div>
                            </div>
                            <!-- -->
                            <div class="row mt-2">
                                <div class="col-3">
                                    <label class='form-label'>Status</label>
                                </div>
                                <div class="col">
                                    <select name="task_status" id="selectStatus" class="select_status selectpicker">
                                        <option selected value="To do">To do</option>
                                        <option value="In progress">In progress</option>
                                        <option value="For Checking">For Checking</option>
                                        <option value="Completed">Completed</option>
                                        <option value="Cancelled">Cancelled</option>
                                    </select>
                                </div>
                            </div>
                            <!-- -->

                            <div class="row mt-2">
                                <div class="col-3">
                                    <label class='form-label'>Start date</label>
                                </div>
                                <div class="col">
                                    <input name="start_date" id="startDate" autocomplete="off" placeholder="MM/DD/YYYY" class="form-inputs form-control form-control-sm focus-ring focus-ring-light" type="text">
                                </div>
                            </div>
                            <!-- -->

                            <div class="row mt-2">
                                <div class="col-3">
                                    <label class='form-label'>Deadline</label>
                                </div>
                                <div class="col">
                                    <input name="deadline" id="deadline" autocomplete="off" placeholder="MM/DD/YYYY" class="form-inputs form-control form-control-sm focus-ring focus-ring-light" type="text">
                                </div>
                            </div>
                            <!-- -->





                        </div>
                        <div class="modal-footer add-task" style="justify-content: start;">

                            <div class="file-preview"></div>
                            <div style="display:flex; justify-content:space-between; width:100%">
                                <div>
                                    <button class="upload-file-btn btn" type="button"><i style="font-size:.9rem" class="ri-camera-fill"></i>&nbsp; Upload File</button>
                                    <input style="display:none" name="task_files[]" type="file" id="fileInput" multiple>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i style="font-weight: 1000;" class="ri-close-fill"></i> Close</button>
                                    <button type="submit" id="addtaskBtn" class="btn btn-primary"><i class="ri-checkbox-circle-fill"></i> Save</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- modal -->
    <?php } ?>


    <!-- Modal -->

    <div class="modal fade" id="addFilesM" data-bs-backdrop="static" tabindex="-1" data-bs-keyboard="false" aria-labelledby="addFilesMLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title" id="addFilesMLabel">Add Files</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id='post-files' enctype="multipart/form-data">
                    <input type="hidden" name="project_id" value="<?= $project_id ?>">

                    <div class="modal-footer add-file" style="justify-content: start; border-top:0;">

                        <div class="file-preview"></div>
                        <div style="display:flex; justify-content:space-between; width:100%">
                            <div>
                                <button class="upload-files-btn btn" type="button"><i style="font-size:.9rem" class="ri-camera-fill"></i>&nbsp; Upload File</button>
                                <input style="display:none" name="file_files[]" type="file" id="filesInput" multiple>
                            </div>
                            <div>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i style="font-weight: 1000;" class="ri-close-fill"></i> Close</button>
                                <button type="submit" id="addfileBtn" class="btn btn-primary"><i class="ri-checkbox-circle-fill"></i> Save</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- modal -->






    <!-- Modal -->
    <div class="modal fade" id="previewTask" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="previewTaskLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title"><span id="previewTaskLabel"></span>&nbsp;<a class="share-icon" href="#"><i class="ri-share-box-line"></i></a></h1>
                    <div class="d-flex align-items-center gap-2 ms-auto">
                    <?php if (isManagementRole($userRole)) { ?>
                        <div class="dropdown" id="taskActionsDropdown">
                            <button type="button" class="btn btn-secondary" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" aria-label="Task actions">
                                <i class="ri-more-2-fill" aria-hidden="true"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li class="dropend">
                                    <button type="button" class="dropdown-item dropdown-toggle task-move-toggle" aria-expanded="false">Move</button>
                                    <ul class="dropdown-menu task-move-menu">
                                        <li><button type="button" class="dropdown-item task-move-status" data-status="To do">To do</button></li>
                                        <li><button type="button" class="dropdown-item task-move-status" data-status="In progress">In progress</button></li>
                                        <li><button type="button" class="dropdown-item task-move-status" data-status="For Checking">For Checking</button></li>
                                        <li><button type="button" class="dropdown-item task-move-status" data-status="Completed">Completed</button></li>
                                        <li><button type="button" class="dropdown-item task-move-status" data-status="Cancelled">Cancelled</button></li>
                                    </ul>
                                </li>
                                <li><button type="button" class="dropdown-item updateTaskP"><i class="ri-edit-box-line me-1" aria-hidden="true"></i>Edit task</button></li>
                            </ul>
                        </div>
                    <?php } ?>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="m_previewTaskId" id="m_previewTaskId">
                    <div>
                        <!-- -->
                        <div class="task-profile mb-2 d-flex" style="align-items: center;">
                            <img id="ptaskProfilePicture" src="images/Default_pfp.png" alt="">
                            <span id="ptaskStatus" class=" badge "></span>
                        </div>

                        <div class="task-title mb-2">
                            <span style="font-weight: 1000;" id="ptaskTitle"></span>
                        </div>

                        <div class="task-description mb-2">
                            <span style="font-size:.71rem;" id="ptaskDescription">
                            </span>
                        </div>

                        <div class="start-date mb-1">
                            <b>Start Date: </b><span id="pstartDate"></span>
                        </div>

                        <div class="deadline mb-1">
                            <b>Deadline: </b><span id="pdeadline"></span>
                        </div>

                        <div class="task-project mb-1">
                            <b>Project: </b><span id="ptaskProject">SNL Virutal partner Backup for may 2024</span>
                        </div>




                        <!-- other comments -->
                        <div id="comments-results">

                        </div>
                        <!-- other comments -->

                        <div class="task-files-preview">
                            <!-- ticket description and post informations -->
                            <div class="d-flex gap-4 mt-3" style="padding:12px 0px;border-top:1px solid rgba(0,0,0,0.1);">
                                <div class="d-flex gap-3">
                                    <div>
                                        <img id="pTaskFileProfile" class="profiles_task_preview" alt="profile">
                                    </div>
                                    <div class="d-block">
                                        <div class="d-flex gap-3 mb-2" style="align-items: end;">
                                            <span><b id="taskPosterName"></b></span><span style="font-size: .68rem;" id="taskPostedDate"></span>
                                        </div>
                                        <div class="mb-2">
                                            <div class="gallery"></div>
                                            <div class="fileList"></div>
                                        </div>
                                        <div class="d-flex downloadFilesBtn" style="justify-content: space-between; width:406px">
                                            <div><i style="font-size:.9rem;" class="ri-attachment-2"></i></div>
                                            <div class="downloadtaskfiles"></div>
                                        </div>

                                    </div>
                                </div>

                            </div>

                        </div>

                    </div>
                    <!-- ticket description and post informations -->
                </div>
            </div>
        </div>
    </div>


    <!-- delete Modal -->
    <div class="modal fade" id="previewDeleteTask" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="previewDeleteTaskLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title"><span id="DeletepreviewTaskLabel"></span>&nbsp;</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id='previewDeleteTaskF'>
                    <div class="modal-body">
                        <input type="hidden" name="m_previewDeleteTaskId" id="m_previewDeleteTaskId">
                        <div>
                            <!-- -->
                            <div class="task-profile mb-2 d-flex" style="align-items: center;">
                                <img id="ptaskDeleteProfilePicture" alt="">
                                <span id="ptaskDeleteStatus" class=" badge "></span>
                            </div>

                            <div class="task-title mb-2">
                                <span style="font-weight: 1000;" id="ptaskDeleteTitle"></span>
                            </div>

                            <div class="task-description mb-2">
                                <span style="font-size:.71rem" id="ptaskDeleteDescription">
                                </span>
                            </div>

                            <div class="start-date mb-1">
                                <b>Start Date: </b><span id="pstartDeleteDate"></span>
                            </div>

                            <div class="deadline mb-1">
                                <b>Deadline: </b><span id="Deletepdeadline"></span>
                            </div>

                            <div class="task-project mb-1">
                                <b>Project: </b><span id="DeleteptaskProject">SNL Virutal partner Backup for may 2024</span>
                            </div>


                            <!-- other comments -->
                            <div id="Delete-comments-results">

                            </div>
                            <!-- other comments -->

                            <div class="Delete-task-files-preview">
                                <!-- ticket description and post informations -->
                                <div class="d-flex gap-4 mt-3" style="padding:12px 0px;border-top:1px solid rgba(0,0,0,0.1);">
                                    <div class="d-flex gap-3">
                                        <div>
                                            <img id="DeletepTaskFileProfile" class="profiles_task_preview" alt="profile">
                                        </div>
                                        <div class="d-block">
                                            <div class="d-flex gap-3 mb-2" style="align-items: end;">
                                                <span><b id="DeletetaskPosterName"></b></span><span style="font-size: .68rem;" id="DeletetaskPostedDate"></span>
                                            </div>
                                            <div class="mb-2">
                                                <div class="gallery"></div>
                                                <div class="fileList"></div>
                                            </div>

                                        </div>
                                    </div>

                                </div>

                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i style="font-weight: 500; font-size:.8rem" class="ri-close-line"></i> Close</button>
                        <button style="color:darkred" type="submit" class="btn btn-secondary"><i style="font-weight: 500; font-size:.8rem" class="ri-delete-bin-5-line"></i> Delete Task</button>

                    </div>
                </form>
            </div>
        </div>
    </div>







    <?php include("../footer.php") ?>

    <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script>



    <!-- Magnific Popup core JS file -->
    <script src="magnific-popup/jquery.magnific-popup.js"></script>
    <script src="magnific-popup/jquery.magnific-popup.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.7.1/jszip.min.js"></script>

</body>



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


        $('.projectCommentGallery').each(function() {
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

        $('.comments-gallery').each(function() {
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



<!-- delete task -->


<script>
    $(document).ready(function() {
        $('#previewDeleteTaskF').submit(function(event) {
            // Prevent default form submission
            event.preventDefault();


            var formData = new FormData(this);
            formData.append("previewDeleteTaskF", true);


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
                        $('#previewDeleteTask').modal('hide');
                        $('#task-results').load("view.php #task-results", function() {


                            $('.status_filter').select2({
                                width: '100%',
                                placeholder: 'Status',
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

                                table.column(4).search(selectedStatus).draw(); // Assuming status column is the third column (index 2)
                            });



                        });
                        $('#kanban-results').load("view.php #kanban-results", function() {

                            $('.card-kanban.tasks').on('dragstart', function(event) {
                                const taskId = $(this).data('taskid');
                                event.originalEvent.dataTransfer.setData('text/plain', taskId);
                            });

                            // Drag over event listener
                            $('.task-div').on('dragover', function(event) {
                                event.preventDefault();
                                const draggedElement = $('.dragging');
                                const mouseY = event.originalEvent.clientY;
                                const target = $(event.target);
                                const box = target.offset();
                                const boxHeight = target.height();

                                if (target.hasClass('task-div')) {
                                    $('.dragging').removeClass('dragging');
                                    if (mouseY - box.top < boxHeight / 2) {
                                        target.addClass('drop-before');
                                    } else {
                                        target.addClass('drop-after');
                                    }
                                } else {
                                    $('.task-div').removeClass('drop-before drop-after');
                                }
                            });



                            $('.task-div').on('drop', function(event) {
                                event.preventDefault();
                                const taskId = event.originalEvent.dataTransfer.getData('text/plain');
                                const target = $(event.target);
                                const newOrder = target.prevAll('.card-kanban').length + 1;
                                let newStatus = this.id;


                                console.log(newOrder);

                                newStatus = convertStatus(newStatus);

                                const draggedElement = $(`[data-taskid='${taskId}']`);

                                const taskIdFromDataAttr = draggedElement.data('taskid');


                                updateTaskOrder(taskIdFromDataAttr, newOrder);
                                $('.task-div').removeClass('drop-before drop-after');


                                updateTaskStatus(taskIdFromDataAttr, newStatus);
                                $(this).prepend(draggedElement);
                            });

                            function updateTaskOrder(taskId, newOrder) {
                                // Send AJAX request to update task order in the database
                                $.ajax({
                                    url: 'update_task_order.php',
                                    method: 'POST',
                                    data: {
                                        taskId: taskId,
                                        newOrder: newOrder
                                    },
                                    success: function(response) {
                                        console.log(response); // Log success message
                                    },
                                    error: function(jqXHR, textStatus, errorThrown) {
                                        console.error('Error:', textStatus, errorThrown); // Log error message
                                    }
                                });
                            }

                            $('.task-div').on('dragover dragenter', function(event) {
                                event.preventDefault();
                            });

                            function convertStatus(status) {
                                if (status === 'toDo') {
                                    return 'To do';
                                } else if (status === 'inProgress') {
                                    return 'In progress';
                                } else if (status === 'forChecking') {
                                    return 'For Checking';
                                } else if (status === 'completed') {
                                    return 'Completed';
                                } else if (status === 'cancelled') {
                                    return 'Cancelled';
                                }
                                return status;
                            }

                            function updateTaskStatus(taskId, newStatus) {
                                $.post('update_task_status.php', {
                                        task_id: taskId,
                                        new_status: newStatus
                                    })
                                    .done(function(response) {
                                        console.log(response);
                                    })
                                    .fail(function(jqXHR, textStatus, errorThrown) {
                                        console.error('AJAX Error:', textStatus, errorThrown);
                                    });
                            }


                        });
                        // setTimeout(showToast, 500);

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

<!-- delete task -->
<script>
    $(document).on('click', '.deleteTask', function(e) {
        var task_id = $(this).data('task-id');
        console.log("Task ID:", task_id); // Log the task ID to check if it's correctly retrieved




        function displayFilesInModal(fileDetails) {
            var imageFiles = [];
            var nonImageFiles = [];

            fileDetails.forEach(function(file) {
                if (file.file_type.startsWith('image/')) {
                    imageFiles.push(file);
                } else {
                    nonImageFiles.push(file);
                }
            });

            var gallery = $('.gallery').empty();
            var fileList = $('.fileList').empty();
            var imagesCount = 0;

            imageFiles.forEach(function(file, index) {
                var imgClass = imagesCount > 0 ? 'd-none' : '';
                var imgElement = `<a class="${imgClass}" href="../../files/task/${file.file_name}">
                                <img class="attached-files" src="../../files/task/${file.file_name}">
                              </a>`;
                gallery.append(imgElement);
                imagesCount++;
            });

            if (imagesCount > 1) {
                gallery.append(`<span class="more-span">+${imagesCount - 1} More</span>`);
            }

            nonImageFiles.forEach(function(file) {
                var nonImgElement = `<a target="_blank" class="files" href="../../files/task/${file.file_name}">${file.file_name}</a><br>`;
                fileList.append(nonImgElement);
            });
        }


        function displayComments(taskComments) {


            var commentsContainer = $('#Delete-comments-results');
            commentsContainer.empty();

            taskComments.forEach(function(comment) {

                var commenterProfiles = 'data:image/*;base64,' + comment.commenter_profile;
                var commentHtml = `
                        <div class="d-flex gap-4" style="padding: 12px 0px; border-top: 1px solid rgba(0,0,0,0.05); position:relative">
                            <div class="d-flex gap-3">
                                <div>
                                    <img class="profiles" src="${commenterProfiles}" alt="profile">
                                </div>
                                <div class="d-block">
                                    <div class="d-flex gap-3 mb-1" style="align-items: end;">
                                        <span><b>${comment.commenter_name}</b></span>
                                        <span style="font-size:.68rem">${comment.comment_date}</span>
                                    </div>
                                    <div class="mb-2">
                                        <span>${comment.commenter_comment}</span>
                                    </div>
                                    <div class="mb-2">
                                        <div class="comments-gallery">`;

                // Display image files
                var images = 0;
                var imageCount = 0;
                comment.commenter_files.forEach(function(file) {
                    if (file.file_type.startsWith('image/')) {
                        images++;
                        imageCount++;
                        commentHtml += `<a ${images > 1 ? 'class="d-none"' : ''} href="../../files/task/${file.file_name}">
                                                <img class="attached-files" src="../../files/task/${file.file_name}">
                                            </a>`;
                    }
                });

                $('.comments-gallery').each(function() {
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

                commentHtml += `${imageCount > 1 ? `<span class="comment-more-span">+${imageCount - 1} More</span>` : ''}</div><div class="commentfileList">`;

                // Display non-image files
                comment.commenter_files.forEach(function(file) {
                    if (!file.file_type.startsWith('image/')) {
                        commentHtml += `<a target="_blank" class="files" href="../../files/task/${file.file_name}">${file.file_name}</a><br>`;

                    }
                });



                commentHtml += `</div></div></div></div>`;

                commentsContainer.append(commentHtml);
            });
        }




        $.ajax({
            type: "GET",
            url: "ajax.php?deletetaskid=" + task_id,

            success: function(response) {

                var res = jQuery.parseJSON(response);

                if (res.status == 400) {


                    console.log("confirmed ID:", res.data.task_title);
                    $('#m_previewDeleteTaskId').val(res.data.task_id);
                    var fileDetails = res.pTaskFiles;
                    var taskComments = res.pTaskComments;
                    if (fileDetails != '') {
                        displayFilesInModal(fileDetails);
                    }
                    console.log("confirmed ID:", res.data.task_title);
                    $('#m_previewDeleteTaskId').val(res.data.task_id);
                    $('#ptaskDeleteTitle').text(res.data.task_title);
                    $('#ptaskDeleteDescription').text(res.data.description);
                    $('#pstartDeleteDate').text(res.data.start_date);
                    $('#Deletepdeadline').text(res.data.deadline);
                    $('#ptaskDeleteStatus').removeClass();
                    $('#ptaskDeleteStatus').addClass('badge ' + res.data.status);
                    $('#ptaskDeleteStatus').text(res.data.status);
                    $('#DeletepreviewTaskLabel').text(res.pId);
                    $('#DeleteptaskProject').text(res.project_title);
                    var profilePictureDataURI = 'data:image/*;base64,' + res.profiles;
                    $('#ptaskDeleteProfilePicture').attr('src', profilePictureDataURI);

                    if (fileDetails != '') {
                        $('.Delete-task-files-preview').removeClass('d-none');

                        displayFilesInModal(fileDetails);

                        $('#DeletepTaskFileProfile').attr('src', profilePictureDataURI);
                        $('#DeletetaskPosterName').text(res.taskPName);
                        $('#DeletetaskPostedDate').text(res.taskPDate);
                        var downloadtaskfiles = $('.Deletedownloadtaskfiles').empty();
                        downloadtaskfiles.append(`<a href="download.php?files=${res.data.files}&taskID=${res.data.task_id}" id="downloadLink">Download all files</a>`);


                    } else {
                        $('.Delete-task-files-preview').addClass('d-none');
                    }

                    $('#DeletecommentTaskId').val(res.data.task_id);

                    displayComments(taskComments);




                    $('#previewDeleteTask').modal('show');

                }


            }
        });


    });
</script>


<!-- delete file -->

<script>
    $(document).on('click', '.deleteFile', function(e) {
        e.preventDefault();
        var url = this.href;
        window.open(url, '_blank');

        $('#files-results').load("view.php #files-results", function() {

            var table = $('.file_table_display').DataTable({
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
    });


    //Delete Project Comment

    $(document).on('click', '.projectCommentsDeleteClicked', function(e) {
        e.preventDefault();
        var url = this.href;
        window.open(url, '_blank');
        $('#project-other-comments-results').load('view.php #project-other-comments-results');
    });
</script>


<script>
    $(document).on('click', '.task-move-toggle', function(event) {
        event.preventDefault();
        event.stopPropagation();
        var submenu = $(this).siblings('.task-move-menu');
        var shouldOpen = !submenu.hasClass('show');
        $('.task-move-menu').removeClass('show');
        $('.task-move-toggle').attr('aria-expanded', 'false');
        submenu.toggleClass('show', shouldOpen);
        $(this).attr('aria-expanded', shouldOpen ? 'true' : 'false');
    });

    $(document).on('click', '.task-move-status', function() {
        var taskId = $('#m_previewTaskId').val();
        var newStatus = $(this).data('status');
        var statusLanes = {
            'To do': 'toDo',
            'In progress': 'inProgress',
            'For Checking': 'forChecking',
            'Completed': 'completed',
            'Cancelled': 'cancelled'
        };
        var moveButton = $(this);

        if (!taskId || !statusLanes[newStatus]) {
            alert('Unable to move this task. Please close and reopen the task, then try again.');
            return;
        }

        moveButton.prop('disabled', true);
        $.post('update_task_status.php', { task_id: taskId, new_status: newStatus })
            .done(function() {
                $('#ptaskStatus').attr('class', 'badge ' + newStatus).text(newStatus);
                $('.card-kanban.tasks[data-task-id="' + taskId + '"]').prependTo('#' + statusLanes[newStatus]);
                $('#taskActionsDropdown .task-move-menu').removeClass('show');
                $('#taskActionsDropdown .task-move-toggle').attr('aria-expanded', 'false');
                bootstrap.Dropdown.getOrCreateInstance($('#taskActionsDropdown [data-bs-toggle="dropdown"]')[0]).hide();
                $('.card-kanban.tasks[data-task-id="' + taskId + '"]').trigger('click');
                if ($.fn.DataTable.isDataTable('#task-table-list')) {
                    $('#task-table-list').DataTable().destroy();
                }
                $('#task-results').load('view.php #task-results', function() {
                    $('.status_filter').select2({ width: '100%', placeholder: 'Status' });
                    var table = $('.table_display').DataTable({
                        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
                        pageLength: 10,
                        order: [[0, 'desc']]
                    });
                    $('#statusFilter').on('change', function() {
                        table.column(4).search($(this).val()).draw();
                    });
                });
            })
            .fail(function() {
                alert('Unable to move this task. Please try again.');
            })
            .always(function() {
                moveButton.prop('disabled', false);
            });
    });
</script>

<!-- Update task -->

<script>
    $(document).on('click', '.updateTaskP', function(e) {
        var task_id = $('.updateTaskP').attr("data-task-id");
        $(".updateTaskP").attr("data-task-id", '');

        $('#addTaskBtn').html('<i class="ri-checkbox-circle-fill"></i> Update');

        console.log("Task ID:", task_id);
        $.ajax({
            type: "GET",
            url: "ajax.php?updatetaskid=" + task_id,

            success: function(response) {

                var res = jQuery.parseJSON(response);

                if (res.status == 200) {


                    $('#m_taskId').val(res.data.task_id);
                    $('#taskTitle').val(res.data.task_title);
                    $('#taskDescription').val(res.data.description);
                    $('#startDate').val(res.start_date);
                    $('#deadline').val(res.deadline);
                    $('#selectStatus').val(res.data.status).trigger('change');

                    $('#previewTask').modal('hide');

                    $('#addTaskM').modal('show');

                }


            }
        });


    });
</script>

<script>
    $(document).on('click', '.updateTask', function(e) {
        var task_id = $(this).data('task-id');

        $('#addTaskBtn').html('<i class="ri-checkbox-circle-fill"></i> Update');

        console.log("Task ID:", task_id);
        $.ajax({
            type: "GET",
            url: "ajax.php?updatetaskid=" + task_id,

            success: function(response) {

                var res = jQuery.parseJSON(response);

                if (res.status == 200) {


                    $('#m_taskId').val(res.data.task_id);
                    $('#taskTitle').val(res.data.task_title);
                    $('#taskDescription').val(res.data.description);
                    $('#startDate').val(res.start_date);
                    $('#deadline').val(res.deadline);
                    $('#selectStatus').val(res.data.status).trigger('change');

                    $('#previewTask').modal('hide');

                    $('#addTaskM').modal('show');

                }


            }
        });


    });
</script>

<!-- Update task -->

<!-- preview task and commenting -->
<script>
    $(document).on('click', '.previewTaskM', function(e) {
        var task_id = $(this).data('task-id');

        console.log(task_id);



        function displayFilesInModal(fileDetails) {
            var imageFiles = [];
            var nonImageFiles = [];

            fileDetails.forEach(function(file) {
                if (file.file_type.startsWith('image/')) {
                    imageFiles.push(file);
                } else {
                    nonImageFiles.push(file);
                }
            });

            var gallery = $('.gallery').empty();
            var fileList = $('.fileList').empty();
            var imagesCount = 0;

            imageFiles.forEach(function(file, index) {
                var imgClass = imagesCount > 0 ? 'd-none' : '';
                var imgElement = `<a class="${imgClass}" href="../../files/task/${file.file_name}">
                                <img class="attached-files" src="../../files/task/${file.file_name}">
                              </a>`;
                gallery.append(imgElement);
                imagesCount++;
            });

            if (imagesCount > 1) {
                gallery.append(`<span class="more-span">+${imagesCount - 1} More</span>`);
            }

            nonImageFiles.forEach(function(file) {
                var nonImgElement = `<a target="_blank" class="files" href="../../files/task/${file.file_name}">${file.file_name}</a><br>`;
                fileList.append(nonImgElement);
            });
        }


        function displayComments(taskComments) {


            var commentsContainer = $('#comments-results');
            commentsContainer.empty();

            taskComments.forEach(function(comment) {

                var commenterProfiles = 'data:image/*;base64,' + comment.commenter_profile;
                var commentHtml = `
                        <div class="d-flex gap-4" style="padding: 12px 0px; border-top: 1px solid rgba(0,0,0,0.05); position:relative">
                            <div class="d-flex gap-3">
                                <div>
                                    <img class="profiles" src="${commenterProfiles}" alt="profile">
                                </div>
                                <div class="d-block">
                                    <div class="d-flex gap-3 mb-1" style="align-items: end;">
                                        <span><b>${comment.commenter_name}</b></span>
                                        <span style="font-size:.68rem">${comment.comment_date}</span>
                                    </div>
                                    <div class="mb-2">
                                        <span>${comment.commenter_comment}</span>
                                    </div>
                                    <div class="mb-2">
                                        <div class="comments-gallery">`;

                // Display image files
                var images = 0;
                var imageCount = 0;
                comment.commenter_files.forEach(function(file) {
                    if (file.file_type.startsWith('image/')) {
                        images++;
                        imageCount++;
                        commentHtml += `<a ${images > 1 ? 'class="d-none"' : ''} href="../../files/task/${file.file_name}">
                                                <img class="attached-files" src="../../files/task/${file.file_name}">
                                            </a>`;
                    }
                });



                commentHtml += `${imageCount > 1 ? `<span class="comment-more-span">+${imageCount - 1} More</span>` : ''}</div><div class="commentfileList">`;

                // Display non-image files
                comment.commenter_files.forEach(function(file) {
                    if (!file.file_type.startsWith('image/')) {
                        commentHtml += `<a target="_blank" class="files" href="../../files/task/${file.file_name}">${file.file_name}</a><br>`;

                    }
                });

                if (comment.commenter_files != 0) {
                    commentHtml += (`
                        <div class="d-flex downloadFilesBtn" style="justify-content: space-between; width:406px">
                                            <div><i style="font-size:.9rem;" class="ri-attachment-2"></i></div>
                                            <div class="downloadcommenttaskfiles">
                                            <a href="download.php?files=${comment.commenter_files_all}&taskID=Comment" id="downloadLink">Download all files</a></div>
                                        </div>
                       `);
                }


                if (comment.commenter_user_id == <?php echo json_encode($user_id) ?> || <?php echo json_encode(isManagementRole($userRole)) ?>) {

                    commentHtml += `
           <div style="position:absolute;top:6px;right:0;">
                                    <a class="deleteComment" onclick="javascript:return confirm('Delete Comment on ${comment.comment_date}?');" style="color:darkred" target="_blank" href="../delete.php?deleteID=${comment.comment_id}&from=taskComments"><i class="ri-delete-bin-5-line"></i></a>
                                    </div>
                                    `;
                }



                commentHtml += `</div></div></div></div>`;

                commentsContainer.append(commentHtml);
            });
            $('.comments-gallery').each(function() {
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
        }

        console.log("Task ID:", task_id);
        $.ajax({
            type: "GET",
            url: "ajax.php?previewid=" + task_id,

            success: function(response) {


                var res = jQuery.parseJSON(response);

                if (res.status == 300) {

                    $(".updateTaskP").attr("data-task-id", task_id);
                    console.log("new data task id: " + $(".updateTaskP").attr("data-task-id"));


                    var fileDetails = res.pTaskFiles;
                    var taskComments = res.pTaskComments;
                    if (fileDetails != '') {
                        displayFilesInModal(fileDetails);
                    }
                    console.log("confirmed ID:", res.data.task_title);
                    $('#m_previewTaskId').val(res.data.task_id);
                    $('#ptaskTitle').text(res.data.task_title);
                    $('#ptaskDescription').html(res.taskDescription);
                    $('#pstartDate').text(res.data.start_date);
                    $('#pdeadline').text(res.data.deadline);
                    $('#ptaskStatus').removeClass();
                    $('#ptaskStatus').addClass('badge ' + res.data.status);
                    $('#ptaskStatus').text(res.data.status);
                    $('#previewTaskLabel').text(res.pId);
                    $('#ptaskProject').text(res.project_title);
                    var profilePictureDataURI = 'data:image/*;base64,' + res.profiles;
                    $('#ptaskProfilePicture').attr('src', profilePictureDataURI);

                    if (fileDetails != '') {
                        $('.task-files-preview').removeClass('d-none');

                        displayFilesInModal(fileDetails);

                        $('#pTaskFileProfile').attr('src', profilePictureDataURI);
                        $('#taskPosterName').text(res.taskPName);
                        $('#taskPostedDate').text(res.taskPDate);
                        var downloadtaskfiles = $('.downloadtaskfiles').empty();
                        downloadtaskfiles.append(`<a href="download.php?files=${res.data.files}&taskID=${res.data.task_id}" id="downloadLink">Download all files</a>`);


                    } else {
                        $('.task-files-preview').addClass('d-none');
                    }

                    $('#commentTaskId').val(res.data.task_id);

                    displayComments(taskComments);



                    $('#previewTask').modal('show');
                }


            }
        });
    });



    // ------------------------------------------ comment --------------------------


    $('#task-comment-form').submit(function(event) {
        // Prevent default form submission
        event.preventDefault();
        var task_id = $('#commentTaskId').val();

        function displayComments(taskComments) {
            var commentsContainer = $('#comments-results');
            commentsContainer.empty();

            taskComments.forEach(function(comment) {

                var commenterProfiles = 'data:image/*;base64,' + comment.commenter_profile;
                var commentHtml = `
                        <div class="d-flex gap-4" style="padding: 12px 0px; border-top: 1px solid rgba(0,0,0,0.05); position:relative">
                            <div class="d-flex gap-3">
                                <div>
                                    <img class="profiles" src="${commenterProfiles}" alt="profile">
                                </div>
                                <div class="d-block">
                                    <div class="d-flex gap-3 mb-1" style="align-items: end;">
                                        <span><b>${comment.commenter_name}</b></span>
                                        <span style="font-size:.68rem">${comment.comment_date}</span>
                                    </div>
                                    <div class="mb-2">
                                        <span>${comment.commenter_comment}</span>
                                    </div>
                                    <div class="mb-2">
                                        <div class="comments-gallery">`;

                // Display image files
                var images = 0;
                var imageCount = 0;
                comment.commenter_files.forEach(function(file) {
                    if (file.file_type.startsWith('image/')) {
                        images++;
                        imageCount++;
                        commentHtml += `<a ${images > 1 ? 'class="d-none"' : ''} href="../../files/task/${file.file_name}">
                                                <img class="attached-files" src="../../files/task/${file.file_name}">
                                            </a>`;
                    }
                });

                $('.comments-gallery').each(function() {
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

                commentHtml += `${imageCount > 1 ? `<span class="comment-more-span">+${imageCount - 1} More</span>` : ''}</div><div class="commentfileList">`;

                // Display non-image files
                comment.commenter_files.forEach(function(file) {
                    if (!file.file_type.startsWith('image/')) {
                        commentHtml += `<a target="_blank" class="files" href="../../files/task/${file.file_name}">${file.file_name}</a><br>`;
                    }
                });


                if (comment.commenter_files != 0) {
                    commentHtml += (`
                        <div class="d-flex downloadFilesBtn" style="justify-content: space-between; width:406px">
                                            <div><i style="font-size:.9rem;" class="ri-attachment-2"></i></div>
                                            <div class="downloadcommenttaskfiles">
                                            <a href="download.php?files=${comment.commenter_files_all}&taskID=Comment" id="downloadLink">Download all files</a></div>
                                        </div>
                                   
                       `);
                }

                if (comment.commenter_user_id == <?php echo json_encode($user_id) ?>) {

                    commentHtml += `
<div style="position:absolute;top:6px;right:0;">
                        <a class="deleteComment" onclick="javascript:return confirm('Delete Comment on ${comment.comment_date}?');" style="color:darkred" target="_blank" href="../delete.php?deleteID=${comment.comment_id}&from=taskComments"><i class="ri-delete-bin-5-line"></i></a>
                        </div>
                        `;
                }



                commentHtml += `</div></div></div></div>`;

                commentsContainer.append(commentHtml);
            });
            $('.comments-gallery').each(function() {
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
        }

        //---------------------------------------- post comment ajax
        // Get form data
        var formData = new FormData(this);
        formData.append("post-task-comment", true);

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
                    $('#commentTextarea').val('');
                    $('#commentFileInput').val('');
                    $('.comment-file-preview').empty();
                    // load comments

                    //---------------------------------------- nested ajax for refresh comments

                    $.ajax({
                        type: "GET",
                        url: "ajax.php?previewid=" + task_id,
                        success: function(response) {
                            var res = jQuery.parseJSON(response);
                            if (res.status == 300) {
                                var taskComments = res.pTaskComments;
                                displayComments(taskComments);
                            }
                        }
                    });
                    // load comments
                }
            },
            error: function(xhr, status, error) {
                // Handle error
                alert('Error saving Comment: ' + error);
            }
        });
    });






    //------------------------------------ refresh comments on comment deletion
    $(document).on('click', '.deleteComment', function(e) {
        var task_id = $('#commentTaskId').val();


        e.preventDefault();
        var url = this.href;
        window.open(url, '_blank');


        function displayComments(taskComments) {


            var commentsContainer = $('#comments-results');
            commentsContainer.empty();

            taskComments.forEach(function(comment) {

                var commenterProfiles = 'data:image/*;base64,' + comment.commenter_profile;
                var commentHtml = `
            <div class="d-flex gap-4" style="padding: 12px 0px; border-top: 1px solid rgba(0,0,0,0.05); position:relative">
                <div class="d-flex gap-3">
                    <div>
                        <img class="profiles" src="${commenterProfiles}" alt="profile">
                    </div>
                    <div class="d-block">
                        <div class="d-flex gap-3 mb-1" style="align-items: end;">
                            <span><b>${comment.commenter_name}</b></span>
                            <span style="font-size:.68rem">${comment.comment_date}</span>
                        </div>
                        <div class="mb-2">
                            <span>${comment.commenter_comment}</span>
                        </div>
                        <div class="mb-2">
                            <div class="comments-gallery">`;

                // Display image files
                var images = 0;
                var imageCount = 0;
                comment.commenter_files.forEach(function(file) {
                    if (file.file_type.startsWith('image/')) {
                        images++;
                        imageCount++;
                        commentHtml += `<a ${images > 1 ? 'class="d-none"' : ''} href="../../files/task/${file.file_name}">
                                    <img class="attached-files" src="../../files/task/${file.file_name}">
                                </a>`;
                    }
                });

                $('.comments-gallery').each(function() {
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

                commentHtml += `${imageCount > 1 ? `<span class="comment-more-span">+${imageCount - 1} More</span>` : ''}</div><div class="commentfileList">`;

                // Display non-image files
                comment.commenter_files.forEach(function(file) {
                    if (!file.file_type.startsWith('image/')) {
                        commentHtml += `<a target="_blank" class="files" href="../../files/task/${file.file_name}">${file.file_name}</a><br>`;

                    }
                });

                if (comment.commenter_files != 0) {
                    commentHtml += (`
            <div class="d-flex downloadFilesBtn" style="justify-content: space-between; width:406px">
                                <div><i style="font-size:.9rem;" class="ri-attachment-2"></i></div>
                                <div class="downloadcommenttaskfiles">
                                <a href="download.php?files=${comment.commenter_files_all}&taskID=Comment" id="downloadLink">Download all files</a></div>
                            </div>
           `);
                }


                if (comment.commenter_user_id == <?php echo json_encode($user_id) ?> || <?php echo json_encode(isManagementRole($userRole)) ?>) {

                    commentHtml += `
<div style="position:absolute;top:6px;right:0;">
                        <a class="deleteComment" onclick="javascript:return confirm('Delete Comment on ${comment.comment_date}?');" style="color:darkred" target="_blank" href="../delete.php?deleteID=${comment.comment_id}&from=taskComments"><i class="ri-delete-bin-5-line"></i></a>
                        </div>
                        `;
                }



                commentHtml += `</div></div></div></div>`;

                commentsContainer.append(commentHtml);
            });
        }


        fas

        $.ajax({
            type: "GET",
            url: "ajax.php?previewid=" + task_id,
            success: function(response) {
                var res = jQuery.parseJSON(response);
                if (res.status == 300) {
                    var taskComments = res.pTaskComments;
                    displayComments(taskComments);
                }
            }
        });






















    });
</script>


<!-- add files -->

<script>
    $(document).ready(function() {
        $('#post-files').submit(function(event) {
            // Prevent default form submission
            event.preventDefault();

            var formData = new FormData(this);
            formData.append("add-files", true);

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
                        $('#files-results').load("view.php #files-results", function() {

                            var table = $('.file_table_display').DataTable({
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
                        $('#post-files')[0].reset();
                        $('#addFilesM').modal('hide');

                    }
                },
                error: function(xhr, status, error) {
                    // Handle error
                    alert('Error adding files: ' + error);
                }
            });

        });





        //================================ project comments

        $('#project-comment-form').submit(function(event) {
            // Prevent default form submission
            event.preventDefault();


            //---------------------------------------- post comment ajax
            // Get form data
            var formData = new FormData(this);
            formData.append("post-project-comment", true);

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
                        $('#projectcommentsTextarea').val('');
                        $('#projectcommentFileInput').val('');
                        $('.project-comment-file-preview').empty();
                        $('#project-other-comments-results').load('view.php #project-other-comments-results', function() {
                            $('.projectCommentGallery').each(function() {
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
                            $('.comments-gallery').each(function() {
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
                        // load comments




                    }
                },
                error: function(xhr, status, error) {
                    // Handle error
                    alert('Error saving Comment: ' + error);
                }
            });
        });



















    });
</script>



<!-- add task -->


<script>
    $(document).ready(function() {
        $('#post-task').submit(function(event) {
            // Prevent default form submission
            event.preventDefault();



            //   const toastLiveExample = document.getElementById('liveToast')


            //   function showToast() {
            //     const toastBootstrap = new bootstrap.Toast(toastLiveExample)
            //     toastBootstrap.show()
            //   }





            // Get form data
            var formData = new FormData(this);
            formData.append("add-task", true);




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
                        $('#post-task')[0].reset();
                        $('#added-task-id').text(res.id);
                        $('#taskStatus').val('').prop('selected', true).trigger('change');
                        $('#addTaskM').modal('hide');
                        $('#task-results').load("view.php #task-results", function() {


                            $('.status_filter').select2({
                                width: '100%',
                                placeholder: 'Status',
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

                                table.column(4).search(selectedStatus).draw(); // Assuming status column is the third column (index 2)
                            });




                        });
                        $('#kanban-results').load("view.php #kanban-results", function() {




















                            $('.card-kanban.tasks').on('dragstart', function(event) {
                                const taskId = $(this).data('taskid');
                                event.originalEvent.dataTransfer.setData('text/plain', taskId);
                            });

                            // Drag over event listener
                            $('.task-div').on('dragover', function(event) {
                                event.preventDefault();
                                const draggedElement = $('.dragging');
                                const mouseY = event.originalEvent.clientY;
                                const target = $(event.target);
                                const box = target.offset();
                                const boxHeight = target.height();

                                if (target.hasClass('task-div')) {
                                    $('.dragging').removeClass('dragging');
                                    if (mouseY - box.top < boxHeight / 2) {
                                        target.addClass('drop-before');
                                    } else {
                                        target.addClass('drop-after');
                                    }
                                } else {
                                    $('.task-div').removeClass('drop-before drop-after');
                                }
                            });



                            $('.task-div').on('drop', function(event) {
                                event.preventDefault();
                                const taskId = event.originalEvent.dataTransfer.getData('text/plain');
                                const target = $(event.target);
                                const newOrder = target.prevAll('.card-kanban').length + 1;
                                let newStatus = this.id;

                                console.log(newOrder);

                                newStatus = convertStatus(newStatus);

                                const draggedElement = $(`[data-taskid='${taskId}']`);

                                const taskIdFromDataAttr = draggedElement.data('taskid');


                                updateTaskOrder(taskIdFromDataAttr, newOrder);
                                $('.task-div').removeClass('drop-before drop-after');


                                updateTaskStatus(taskIdFromDataAttr, newStatus);
                                $(this).prepend(draggedElement);
                            });

                            function updateTaskOrder(taskId, newOrder) {
                                // Send AJAX request to update task order in the database
                                $.ajax({
                                    url: 'update_task_order.php',
                                    method: 'POST',
                                    data: {
                                        taskId: taskId,
                                        newOrder: newOrder
                                    },
                                    success: function(response) {
                                        console.log(response); // Log success message
                                    },
                                    error: function(jqXHR, textStatus, errorThrown) {
                                        console.error('Error:', textStatus, errorThrown); // Log error message
                                    }
                                });
                            }

                            $('.task-div').on('dragover dragenter', function(event) {
                                event.preventDefault();
                            });

                            function convertStatus(status) {
                                if (status === 'toDo') {
                                    return 'To do';
                                } else if (status === 'inProgress') {
                                    return 'In progress';
                                } else if (status === 'forChecking') {
                                    return 'For Checking';
                                } else if (status === 'completed') {
                                    return 'Completed';
                                } else if (status === 'cancelled') {
                                    return 'Cancelled';
                                }
                                return status;
                            }

                            function updateTaskStatus(taskId, newStatus) {
                                $.post('update_task_status.php', {
                                        task_id: taskId,
                                        new_status: newStatus
                                    })
                                    .done(function(response) {
                                        console.log(response);
                                    })
                                    .fail(function(jqXHR, textStatus, errorThrown) {
                                        console.error('AJAX Error:', textStatus, errorThrown);
                                    });
                            }




































                        });
                        // setTimeout(showToast, 500);

                    }
                    if (res.status == 200) {
                        $('#add-projects')[0].reset();
                        $('#projectStatus').val('').prop('selected', true).trigger('change');
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

<!-- add task -->


<!-- drag and drop move status -->
<script>
    $(document).ready(function() {
        $('.card-kanban.tasks').on('dragstart', function(event) {
            const taskId = $(this).data('taskid');
            event.originalEvent.dataTransfer.setData('text/plain', taskId);
        });

        // Drag over event listener
        $('.task-div').on('dragover', function(event) {
            event.preventDefault();
            const draggedElement = $('.dragging');
            const mouseY = event.originalEvent.clientY;
            const target = $(event.target);
            const box = target.offset();
            const boxHeight = target.height();

            if (target.hasClass('task-div')) {
                $('.dragging').removeClass('dragging');
                if (mouseY - box.top < boxHeight / 2) {
                    target.addClass('drop-before');
                } else {
                    target.addClass('drop-after');
                }
            } else {
                $('.task-div').removeClass('drop-before drop-after');
            }
        });



        $('.task-div').on('drop', function(event) {
            event.preventDefault();
            const taskId = event.originalEvent.dataTransfer.getData('text/plain');
            const target = $(event.target);
            const newOrder = 0;
            let newStatus = this.id;


            newStatus = convertStatus(newStatus);


            console.log('newStatus: ' + newStatus);
            console.log('newOrder: ' + newOrder);



            const draggedElement = $(`[data-taskid='${taskId}']`);

            const taskIdFromDataAttr = draggedElement.data('taskid');

            $('#task-results').load("view.php #task-results", function() {
                $('.status_filter').select2({
                    width: '100%',
                    placeholder: 'Status',
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
                    table.column(4).search(selectedStatus).draw(); // Assuming status column is the third column (index 2)
                });




            });


            updateTaskOrder(taskIdFromDataAttr, newOrder);
            $('.task-div').removeClass('drop-before drop-after');


            updateTaskStatus(taskIdFromDataAttr, newStatus);
            $(this).prepend(draggedElement);
        });

        function updateTaskOrder(taskId, newOrder) {
            // Send AJAX request to update task order in the database
            $.ajax({
                url: 'update_task_order.php',
                method: 'POST',
                data: {
                    taskId: taskId,
                    newOrder: newOrder
                },
                success: function(response) {
                    console.log(response); // Log success message
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Error:', textStatus, errorThrown); // Log error message
                }
            });
        }

        $('.task-div').on('dragover dragenter', function(event) {
            event.preventDefault();
        });

        function convertStatus(status) {
            if (status === 'toDo') {
                return 'To do';
            } else if (status === 'inProgress') {
                return 'In progress';
            } else if (status === 'forChecking') {
                return 'For Checking';
            } else if (status === 'completed') {
                return 'Completed';
            } else if (status === 'cancelled') {
                return 'Cancelled';
            }
            return status;
        }


        function updateTaskStatus(taskId, newStatus) {
            $.post('update_task_status.php', {
                    task_id: taskId,
                    new_status: newStatus
                })
                .done(function(response) {
                    console.log(response);
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX Error:', textStatus, errorThrown);
                });
        }
    });
</script>
<!-- drag and drop move to status -->



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
                        $('.modal-footer.add-task').prepend(filePreview);

                        removeButton.click(function() {
                            console.log('remove hahah');
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
        // Function to handle file upload
        $('.upload-files-btn').click(function() {
            $('#filesInput').click();
        });

        $('#filesInput').change(function() {
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
                        $('.modal-footer.add-file').prepend(filePreview);

                        removeButton.click(function() {
                            console.log('remove hahah');
                            // Remove file preview
                            filePreview.remove();

                            // Remove the corresponding file from the file input
                            var indexToRemove = Array.from($('#filesInput').prop('files')).indexOf(file);
                            if (indexToRemove !== -1) {
                                var filesArray = Array.from($('#filesInput').prop('files'));
                                filesArray.splice(indexToRemove, 1);
                                $('#filesInput').prop('files', new FileListWrapper(filesArray));
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
        // Listen for the modal's hidden.bs.modal event
        $('#previewTask').on('hidden.bs.modal', function(e) {



            // Reset the form or input values here
            $('#ptaskTitle').text('');
            $('#ptaskDescription').text('');
            $('#pstartDate').text('');
            $('#pdeadline').text('');
            $('#ptaskProject').text('');
            $('.task-files-preview').addClass('d-none');
            $('#taskPosterName').text('');
            $('#taskPostedDate').text('');
            $('.gallery').empty();
            $('.fileList').empty();
            $('#commentTextarea').val('');
            $('#commentFileInput').val('');
            $('.comment-file-preview').empty();



        });
    });
</script>

<script>
    $(document).ready(function() {
        // Listen for the modal's hidden.bs.modal event
        $('#addFilesM').on('hidden.bs.modal', function(e) {
            $('.file-preview').empty();
            $('#filesInput').val('');

        });
    });
</script>

<script>
    $(document).ready(function() {
        // Listen for the modal's hidden.bs.modal event
        $('#addTaskM').on('hidden.bs.modal', function(e) {


            // Reset the form or input values here
            $('#taskTitle').val('');
            $('#taskDescription').val('');
            $('#selectStatus').val('To do').trigger('change'); // Reset Select2 dropdown
            $('#startDate').val('');
            $('#deadline').val('');
            $('.file-preview').empty();
            $('#fileInput').val('');

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

<!-- navigation -->

<script>
    $(document).ready(function() {


        $("#nav-overview").click(function() {
            $("#div-overview").removeClass("d-none");
            $("#div-list").addClass("d-none");
            $("#div-kanban").addClass("d-none");
            $("#div-files").addClass("d-none");
            $("#div-comments").addClass("d-none");
            $("#nav-comments").removeClass("active");
            $("#nav-files").removeClass("active");
            $("#nav-kanban").removeClass("active");
            $("#nav-list").removeClass("active");
            $("#nav-overview").addClass("active");
        });

        $("#nav-list").click(function() {
            $("#div-list").removeClass("d-none");
            $("#div-overview").addClass("d-none");
            $("#div-kanban").addClass("d-none");
            $("#div-files").addClass("d-none");
            $("#div-comments").addClass("d-none");
            $("#nav-comments").removeClass("active");
            $("#nav-files").removeClass("active");
            $("#nav-kanban").removeClass("active");
            $("#nav-overview").removeClass("active");
            $("#nav-list").addClass("active");
        });


        $("#nav-kanban").click(function() {
            $("#div-kanban").removeClass("d-none");
            $("#div-list").addClass("d-none");
            $("#div-overview").addClass("d-none");
            $("#div-files").addClass("d-none");
            $("#div-comments").addClass("d-none");
            $("#nav-comments").removeClass("active");
            $("#nav-files").removeClass("active");
            $("#nav-overview").removeClass("active");
            $("#nav-list").removeClass("active");
            $("#nav-kanban").addClass("active");
        });

        $("#nav-files").click(function() {
            $("#div-files").removeClass("d-none");
            $("#div-list").addClass("d-none");
            $("#div-overview").addClass("d-none");
            $("#div-kanban").addClass("d-none");
            $("#div-comments").addClass("d-none");
            $("#nav-comments").removeClass("active");
            $("#nav-kanban").removeClass("active");
            $("#nav-overview").removeClass("active");
            $("#nav-list").removeClass("active");
            $("#nav-files").addClass("active");
        });


        $("#nav-comments").click(function() {
            $("#div-comments").removeClass("d-none");
            $("#div-list").addClass("d-none");
            $("#div-overview").addClass("d-none");
            $("#div-kanban").addClass("d-none");
            $("#div-files").addClass("d-none");
            $("#nav-files").removeClass("active");
            $("#nav-kanban").removeClass("active");
            $("#nav-overview").removeClass("active");
            $("#nav-list").removeClass("active");
            $("#nav-comments").addClass("active");
        });




        $('.select_status').select2({
            width: '100%',
            dropdownParent: $("#addTaskM"),
        });

        $('.status_filter').select2({
            width: '100%',
            placeholder: 'Status',
        });

    });
</script>

<script>
    $(document).ready(function() {
        var tablef = $('.table_display').DataTable({
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

            tablef.column(4).search(selectedStatus).draw(); // Assuming status column is the third column (index 2)
        });


        var table = $('.file_table_display').DataTable({
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
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
</script>


<script>
    $(document).ready(function() {
        // Function to handle file upload
        $('.project-comment-upload-file-btn').click(function() {
            $('#projectcommentFileInput').click();
        });

        $('#projectcommentFileInput').change(function() {
            var files = this.files;

            for (var i = 0; i < files.length; i++) {
                (function(file) { // Use closure to capture the correct file reference
                    var reader = new FileReader();

                    reader.onload = function(e) {
                        var filePreview = $('<div class="project-comment-file-preview"></div>');
                        var removeButton = $('<span class="remove-file">&times;</span>');

                        if (file.type.match('image.*')) {
                            var img = $('<img src="' + e.target.result + '">');
                            filePreview.append(img);
                        } else {
                            var iconImg = $('<img src="' + getFileIcon(file.type) + '">'); // Get default icon based on file type
                            filePreview.append(iconImg);
                        }

                        filePreview.append(removeButton);
                        $('.modal-footer.project-post-comment').prepend(filePreview);

                        removeButton.click(function() {
                            console.log('remove hahah');
                            // Remove file preview
                            filePreview.remove();

                            // Remove the corresponding file from the file input
                            var indexToRemove = Array.from($('#projectcommentFileInput').prop('files')).indexOf(file);
                            if (indexToRemove !== -1) {
                                var filesArray = Array.from($('#projectcommentFileInput').prop('files'));
                                filesArray.splice(indexToRemove, 1);
                                $('#projectcommentFileInput').prop('files', new FileListWrapper(filesArray));
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
        // Function to handle file upload
        $('.comment-upload-file-btn').click(function() {
            $('#commentFileInput').click();
        });

        $('#commentFileInput').change(function() {
            var files = this.files;

            for (var i = 0; i < files.length; i++) {
                (function(file) { // Use closure to capture the correct file reference
                    var reader = new FileReader();

                    reader.onload = function(e) {
                        var filePreview = $('<div class="comment-file-preview"></div>');
                        var removeButton = $('<span class="remove-file">&times;</span>');

                        if (file.type.match('image.*')) {
                            var img = $('<img src="' + e.target.result + '">');
                            filePreview.append(img);
                        } else {
                            var iconImg = $('<img src="' + getFileIcon(file.type) + '">'); // Get default icon based on file type
                            filePreview.append(iconImg);
                        }

                        filePreview.append(removeButton);
                        $('.modal-footer.post-comment').prepend(filePreview);

                        removeButton.click(function() {
                            console.log('remove hahah');
                            // Remove file preview
                            filePreview.remove();

                            // Remove the corresponding file from the file input
                            var indexToRemove = Array.from($('#commentFileInput').prop('files')).indexOf(file);
                            if (indexToRemove !== -1) {
                                var filesArray = Array.from($('#commentFileInput').prop('files'));
                                filesArray.splice(indexToRemove, 1);
                                $('#commentFileInput').prop('files', new FileListWrapper(filesArray));
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
    $(function() {
        var taskId = <?= isset($_GET['task']) ? (int) $_GET['task'] : 0 ?>;
        if (taskId) {
            $('<button type="button" class="previewTaskM">')
                .attr('data-task-id', taskId)
                .appendTo('body')
                .trigger('click')
                .remove();
        }
    });
</script>

</html>