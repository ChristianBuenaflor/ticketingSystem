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
$can_manage_projects = in_array($user_role, ['admin', 'super_admin'], true);
$can_manage_tasks = in_array($user_role, ['admin', 'super_admin', 'technician'], true);

function formatProjectFileSize($fileSize, $filePath)
{
    $bytes = is_file($filePath) ? filesize($filePath) : false;
    if ($bytes === false && is_numeric($fileSize)) {
        $bytes = (float) $fileSize;
    }
    if ($bytes !== false) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $unitIndex = 0;
        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }
        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }

    if (preg_match('/^\d+(?:\.\d+)?\s*(?:B|KB|MB|GB|TB)$/i', trim((string) $fileSize))) {
        return strtoupper(trim((string) $fileSize));
    }

    return 'Size unavailable';
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





if (isset($_GET['project'])) {
    $project_id = (int) $_GET['project'];
    $_SESSION['project'] = $project_id;

    $queryx = "SELECT projects.* FROM projects
               WHERE projects.project_id = $project_id";
    if (!$can_manage_projects) {
        $queryx .= " AND EXISTS (
            SELECT 1 FROM project_shares
            WHERE project_shares.project_id = projects.project_id
            AND project_shares.user_id = $user_id
        )";
    }
    $resultx = mysqli_query($con, $queryx);
    if (mysqli_num_rows($resultx) > 0) {
        while ($rowx = $resultx->fetch_assoc()) {
            $project_title = $rowx['project_title'];
            $project_description = nl2br($rowx['project_description']);
        }
    } else {
        header("Location: list.php");
        exit;
    }
} else {
    header("Location: list.php");
    exit;
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
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 220px), 1fr));
            align-content: start;
            gap: 15px;
            overflow-x: hidden;
            overflow-y: auto;
            white-space: normal;
            padding: 14px;
            margin: 0;
            width: 100%;
            min-height: 430px;
        }

        .col-kanban {
            display: block;
            width: auto;
            min-width: 0;
            margin: 0;
            opacity: .85;
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

        @media (max-width: 767.98px) {
            .row-kanban {
                grid-template-columns: 1fr;
                min-height: 0;
                overflow: visible;
                padding: 10px;
            }

            div.task-div {
                max-height: 250px;
                min-height: 120px;
            }

            #div-kanban .card-header {
                flex-wrap: wrap;
                gap: 8px;
            }
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

        .task-shared-accounts-trigger {
            display: flex;
            align-items: center;
            gap: 0;
            border: 0;
            padding: 0;
            background: transparent;
        }

        .task-shared-account-avatar {
            width: 34px;
            height: 34px;
            object-fit: cover;
            border: 2px solid #fff;
            border-radius: 50%;
            position: relative;
            margin-left: -18px;
        }

        .task-shared-account-avatar:first-child {
            margin-left: 0;
        }

        .task-shared-account-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            margin-left: -18px;
            border: 2px solid #fff;
            border-radius: 50%;
            background: #e9ecef;
            color: #343a40;
            font-size: .72rem;
            font-weight: 600;
            position: relative;
        }

        .task-shared-account-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 4px 0;
            white-space: nowrap;
        }

        .project-share-name {
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .remove-project-share {
            min-width: 44px;
            min-height: 44px;
            flex-shrink: 0;
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

        .task-preview-grid {
            display: grid;
            grid-template-columns: minmax(0, 0.8fr) minmax(0, 1.2fr);
            gap: 1.5rem;
        }

        .task-preview-details,
        .task-preview-conversation {
            min-width: 0;
        }

        .task-preview-conversation {
            border-left: 1px solid rgba(0, 0, 0, 0.1);
            padding-left: 1.5rem;
        }

        .task-attachment-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 0.75rem;
        }

        .task-attachment {
            min-width: 0;
        }

        .task-preview-conversation .task-attachment-image {
            display: block;
        }

        .task-preview-conversation img.attached-files {
            display: block;
            width: 100%;
            height: auto;
            object-fit: contain;
            background: #f2f4f6;
        }

        .task-preview-conversation .task-files-preview .task-attachment-list,
        .task-preview-conversation .task-files-preview .downloadFilesBtn {
            width: calc(100% - 56px) !important;
            margin-left: 56px;
        }

        .task-preview-conversation .comments-gallery {
            width: 100%;
        }

        .task-attachment {
            min-width: 0;
            padding: 10px;
            border: 1px solid #e1e6eb;
            border-radius: 8px;
            background: #fff;
        }

        .task-attachment-name {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
            white-space: nowrap;
        }

        .task-attachment-name span {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .task-attachment-icon {
            width: 36px;
            height: 36px;
            flex-shrink: 0;
            object-fit: contain;
        }

        .task-preview-conversation .downloadFilesBtn {
            width: 100% !important;
        }

        #taskExistingFiles {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        #taskExistingFiles .task-existing-file {
            width: 80px;
            height: auto;
            text-align: center;
        }

        #taskExistingFiles img {
            max-width: 100%;
            max-height: 64px;
            object-fit: contain;
        }

        #taskExistingFiles .task-existing-file-name {
            display: block;
            overflow-wrap: anywhere;
            font-size: 0.7rem;
        }

        .task-file-dropzone {
            width: 100%;
            min-height: 72px;
            padding: 10px;
            border: 1px dashed #9aa5a8;
            border-radius: 4px;
            color: #536368;
            text-align: center;
        }

        .task-file-dropzone.is-dragging {
            border-color: #092a2f;
            background: #f2f4f6;
        }

        #taskFilePreviewList {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 8px;
            margin-bottom: 8px;
        }

        #taskFilePreviewList .file-preview {
            position: relative;
            width: 72px;
            padding: 4px;
            overflow-wrap: anywhere;
            font-size: 0.68rem;
        }

        #taskFilePreviewList .remove-file {
            position: absolute;
            top: 0;
            right: 0;
            padding: 3px;
            background-color: #fff;
        }

        #taskFilePreviewList .file-preview img {
            display: block;
            max-width: 100%;
            max-height: 56px;
            margin: 0 auto 4px;
            object-fit: contain;
        }

        @media (max-width: 767.98px) {
            .task-preview-grid {
                grid-template-columns: minmax(0, 1fr);
                gap: 1rem;
            }

            .task-preview-conversation {
                border-left: 0;
                border-top: 1px solid rgba(0, 0, 0, 0.1);
                padding: 1rem 0 0;
            }
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
                    <div class="card-header d-flex align-items-center" style="justify-content: space-between;">
                        <div>
                            <h6 style="padding: 12px 0px; margin-bottom:0"><i class="ri-server-line"></i> <?= $project_title ?></h6>
                            <button type="button" class="task-shared-accounts-trigger" id="projectSharedAccountsButton" data-bs-toggle="popover" aria-label="Show shared accounts" hidden></button>
                        </div>
                        <button type="button" class="btn share-icon" data-bs-toggle="modal" data-bs-target="#shareProjectM" aria-label="Share project">
                            <i class="ri-share-box-line"></i> Share With
                        </button>

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
                    <div class="card-header d-flex" style="justify-content: space-between; padding-top:22px; padding-bottom:22px;">
                        <h6>Tasks</h6>
                        <?php
                        if ($can_manage_tasks) {
                        ?>
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
                                                <?php if ($can_manage_tasks) { ?>
                                                    <button class="btn table-btn-edit updateTask" data-task-id='<?= $task_id ?>'><i class="ri-edit-box-line"></i></button>&nbsp;
                                                    <button class="btn table-btn-delete deleteTask" data-task-id='<?= $task_id ?>'><i class="ri-delete-bin-5-line"></i></button>
                                                <?php } ?>
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
                    <div class="card-header d-flex" style="justify-content: space-between; padding-top:22px; padding-bottom:22px;">
                        <h6>Tasks Kanban</h6>
                        <?php if ($can_manage_tasks) {
?>
 <button style="padding:6px 12px" class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addTaskM"><i class="ri-add-circle-fill"></i> Add Task</button>
                 
<?php
                        } ?>
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
                                    $user_role = $_SESSION['role'];


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

                                            <div class="card-kanban tasks previewTaskM" <?php if($user_role != 'client'){?> draggable="true" <?php } ?>  data-taskid="<?= $row['task_id']; ?>" data-task-id="<?= $row['task_id']; ?>">

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

                                            <div class="card-kanban tasks previewTaskM" <?php if($user_role != 'client'){?> draggable="true" <?php } ?> data-taskid="<?= $row['task_id']; ?>" data-task-id="<?= $row['task_id']; ?>">

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

                                            <div class="card-kanban tasks previewTaskM" <?php if($user_role != 'client'){?> draggable="true" <?php } ?> data-taskid="<?= $row['task_id']; ?>" data-task-id="<?= $row['task_id']; ?>">

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

                                            <div class="card-kanban tasks previewTaskM" <?php if($user_role != 'client'){?> draggable="true" <?php } ?> data-taskid="<?= $row['task_id']; ?>" data-task-id="<?= $row['task_id']; ?>">

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

                                            <div class="card-kanban tasks previewTaskM" <?php if($user_role != 'client'){?> draggable="true" <?php } ?> data-taskid="<?= $row['task_id']; ?>" data-task-id="<?= $row['task_id']; ?>">

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
                    <div class="card-header d-flex" style="justify-content: space-between;  <?php
                                    if($user_role == 'super_admin'|| $user_role == 'admin')
                                    {
                                    ?> padding: 6px 12px;<?php }else{ ?> padding-top:22px; padding-bottom:22px; <?php  } ?>">
                        <h6>Files</h6>
                        <?php
                                    if($user_role == 'super_admin'|| $user_role == 'admin')
                                    {
                                    ?>
                        <button style="padding:6px 12px" class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addFilesM"><i class="ri-add-circle-fill"></i> Add Files</button>
                    <?php } ?>
                    </div>
                    <div class="card-body" style="position: relative;" id="files-results">


                        <table id="files-table-list" class="table-hover table file_table_display table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>File</th>
                                    <th>File Size</th>
                                    <?php
                                    if ($user_role == 'super_admin' || $user_role == 'admin') {
                                    ?>
                                        <th class="text-center"><i class="ri-menu-line"></i></th>
                                    <?php } ?>
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
                                                            <?php
                                                            $projectFileName = $rowPFS['file_name'];
                                                            $projectFilePath = __DIR__ . '/../../files/project/' . basename($projectFileName);
                                                            $projectFileUrl = '../../files/project/' . rawurlencode(basename($projectFileName));
                                                            $projectFileExists = is_file($projectFilePath);
                                                            $projectFileExtension = strtoupper(pathinfo($projectFileName, PATHINFO_EXTENSION));
                                                            if ($projectFileExtension === '') {
                                                                $projectFileExtension = strtoupper(substr(strrchr($rowPFS['file_type'], '/'), 1) ?: 'FILE');
                                                                if (strpos($projectFileExtension, 'VND.') === 0) {
                                                                    $projectFileExtension = 'FILE';
                                                                }
                                                            }
                                                            $projectFilePreviewType = strpos($rowPFS['file_type'], 'image/') === 0
                                                                ? 'image'
                                                                : ($rowPFS['file_type'] === 'application/pdf' ? 'pdf' : 'unsupported');
                                                            ?>
                                                            <td>&nbsp;<?= sprintf("%04d", $rowPFS['id']); ?></td>
                                                            <td>
                                                                <button type="button" class="btn btn-link p-0 project-file-preview" data-file-url="<?= htmlspecialchars($projectFileUrl, ENT_QUOTES, 'UTF-8') ?>" data-file-name="<?= htmlspecialchars($projectFileName, ENT_QUOTES, 'UTF-8') ?>" data-preview-type="<?= htmlspecialchars($projectFilePreviewType, ENT_QUOTES, 'UTF-8') ?>" data-file-exists="<?= $projectFileExists ? '1' : '0' ?>"><?= htmlspecialchars($projectFileName, ENT_QUOTES, 'UTF-8') ?></button>
                                                                <span class="badge text-bg-light"><?= htmlspecialchars($projectFileExtension !== '' ? $projectFileExtension : 'FILE', ENT_QUOTES, 'UTF-8') ?></span>
                                                                <?php if ($projectFileExists) { ?>
                                                                    <a class="ms-2" href="<?= htmlspecialchars($projectFileUrl, ENT_QUOTES, 'UTF-8') ?>" download="<?= htmlspecialchars(basename($projectFileName), ENT_QUOTES, 'UTF-8') ?>" aria-label="Download <?= htmlspecialchars($projectFileName, ENT_QUOTES, 'UTF-8') ?>" title="Download file"><i class="ri-download-line" aria-hidden="true"></i></a>
                                                                <?php } else { ?>
                                                                    <span class="ms-2 text-muted" aria-label="File missing" title="File is missing from project storage"><i class="ri-download-line" aria-hidden="true"></i></span>
                                                                <?php } ?>
                                                            </td>
                                                            <td><?= htmlspecialchars(formatProjectFileSize($rowPFS['file_size'], $projectFilePath), ENT_QUOTES, 'UTF-8') ?></td>
                                                            <?php
                                                            if ($user_role == 'super_admin' || $user_role == 'admin') {
                                                            ?>
                                                                <td class="text-center"><a target="_blank" onclick="javascript:return confirm('Delete file? file ID : <?= $rowPFS['id'] ?>');" href="../delete.php?deleteID=<?= $rowPFS['id'] ?>&from=files&project_id=<?= $project_id ?>" class="btn table-btn-delete deleteFile"><i class="ri-delete-bin-5-line"></i></a>
                                                                </td>
                                                            <?php } ?>
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

                <div class="modal fade" id="projectFilePreviewModal" tabindex="-1" aria-labelledby="projectFilePreviewTitle" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="projectFilePreviewTitle"></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center" id="projectFilePreviewBody"></div>
                            <div class="modal-footer">
                                <a class="btn btn-primary" id="projectFilePreviewDownload" href="#" download><i class="ri-download-line" aria-hidden="true"></i> Download</a>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- comments -->

                <div id="div-comments" class="d-none row mt-4 card shadow-sm">

                    <div class="card-body" style="position: relative;" id="project-comments-results">

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
                                                    if ($can_manage_projects && $user_id == $commenter_user_id) {
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
                                                    <div class="download"><a style="font-size:.6rem;" href="download.php?files=<?= urlencode($commenter_files) ?>&taskID=0">Download <?= $comment_file_count ?> files</a></div>

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

    <div class="modal fade" id="shareProjectM" tabindex="-1" aria-labelledby="shareProjectMLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title" id="shareProjectMLabel">Share With</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label for="shareUserSelect" class="form-label">Add a user to this project</label>
                    <select id="shareUserSelect" class="form-select form-select-sm" multiple>
                    </select>
                    <div class="form-text">Select one or more accounts.</div>
                    <div id="projectShareList" class="mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="addProjectShare">Share</button>
                </div>
            </div>
        </div>
    </div>
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="projectShareToast" class="toast" role="status" aria-live="polite" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto" id="projectShareToastTitle">SUCCESS</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="projectShareToastMessage"></div>
        </div>
    </div>

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
                                <input name="task_title" id="taskTitle" autocomplete="off" placeholder="Title" class="form-inputs form-control form-control-sm focus-ring focus-ring-light" type="text" required>
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
                        
                        <div class="row mt-2" id="selectStatusdiv">
                            <div class="col-3">
                                <label class='form-label'>Status</label>
                            </div>
                            <div class="col">
                       
                                <select name="task_status" id="selectStatus" class="select_status selectpicker">
                                    <option selected value="To do">To do</option>
                                    <?php 
                        if($user_role != 'client')
                        {
                        ?>
                                    <option value="In progress">In progress</option>
                                    <option value="For Checking">For Checking</option>
                                    <option value="Completed">Completed</option>
                                
                                    <option value="Cancelled">Cancelled</option>
                                    <?php } ?>
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

                        <div id="taskExistingFiles" aria-label="Existing task files"></div>
                        <div id="taskFileDropzone" class="task-file-dropzone mb-2">
                            <div id="taskFilePreviewList"></div>
                            <span>Drag and drop files here, or choose multiple files.</span>
                        </div>
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
                    <h1 class="modal-title"><span id="previewTaskLabel"></span></h1>
                    <div class="d-flex align-items-center gap-2 ms-auto">
                    <?php if ($can_manage_tasks) { ?>
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
                    <div class="task-preview-grid">
                        <input type="hidden" name="m_previewTaskId" id="m_previewTaskId">
                        <section class="task-preview-details" aria-label="Task details">
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
                        </section>

                        <section class="task-preview-conversation" aria-label="Comments and attachments">
                        <div class="d-flex gap-4 mt-1" style="padding:12px 0px;">
                            <div class="d-flex gap-3" style="width: 100%;">
                                <div>

                                    <?php echo '<img class="profiles" src="data:' . $userProfileType . ';base64,' . base64_encode($userProfile) . '" alt="profile">'; ?>
                                </div>
                                <div style="width: 100%;">

                                    <form id="task-comment-form">
                                        <input type="hidden" name="users_id" value="<?= $user_id ?>">
                                        <input type="hidden" name="comment_task_id" id="commentTaskId">
                                        <div>
                                            <textarea class="form-control form-control-sm focus-ring focus-ring-light comment" name="comment_textarea" id="commentTextarea" rows="4" placeholder="Write a comment..." autocomplete="off"></textarea>
                                        </div>
                                        <div class="modal-footer post-comment" style="justify-content: start; background-color: #f2f4f6; padding:8px 16px; border-radius:0">
                                            <div class="comment-file-preview"></div>
                                            <div style="display:flex; justify-content:space-between; width:100%;">
                                                <div>
                                                    <button class="comment-upload-file-btn btn comment" type="button"><i style="font-size:.9rem" class="ri-camera-fill"></i>&nbsp; Upload File</button>
                                                    <input style="display:none" name="comment_files[]" type="file" id="commentFileInput" multiple>
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

                        <!-- Task activity stays directly below the comment composer. -->
                        <div id="comments-results">

                        </div>

                        <div class="task-files-preview mt-3">
                            <div class="d-flex gap-3 mb-2 align-items-center">
                                <img id="pTaskFileProfile" class="profiles_task_preview" alt="Task attachment uploader profile">
                                <div class="d-flex flex-wrap gap-3 align-items-center">
                                    <span><b id="taskPosterName"></b></span>
                                    <span style="font-size: .68rem;" id="taskPostedDate"></span>
                                </div>
                            </div>
                            <div class="task-attachment-list" id="taskAttachmentList"></div>
                            <div class="d-flex downloadFilesBtn mt-2" style="justify-content: space-between;">
                                <div><i style="font-size:.9rem;" class="ri-attachment-2"></i></div>
                                <div class="downloadtaskfiles"></div>
                            </div>
                        </div>

                        </section>
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

        $('#taskAttachmentList').magnificPopup({
            delegate: 'a.task-attachment-image',
            type: 'image',
            gallery: { enabled: true }
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
                        $('#task-results').load("view.php?project=<?= $project_id ?> #task-results", function() {


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
                        $('#kanban-results').load("view.php?project=<?= $project_id ?> #kanban-results", function() {

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


                                $('.task-div').removeClass('drop-before drop-after');

                                $.when(
                                    updateTaskOrder(taskIdFromDataAttr, newOrder),
                                    updateTaskStatus(taskIdFromDataAttr, newStatus)
                                ).done(refreshTaskList);
                                $(this).prepend(draggedElement);
                            });

                            function updateTaskOrder(taskId, newOrder) {
                                // Send AJAX request to update task order in the database
                                return $.ajax({
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
                                return $.post('update_task_status.php', {
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
            console.log('okay okay');

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
                                        <span style="font-size:.7rem"><b>${comment.commenter_name}</b></span>
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
    $(document).on('click', '.project-file-preview', function() {
        var fileUrl = $(this).data('file-url');
        var fileName = $(this).data('file-name');
        var previewType = $(this).data('preview-type');
        var fileExists = String($(this).data('file-exists')) === '1';
        var previewBody = $('#projectFilePreviewBody').empty();

        $('#projectFilePreviewTitle').text(fileName);
        var downloadLink = $('#projectFilePreviewDownload')
            .attr('href', fileUrl)
            .attr('download', fileName)
            .removeClass('disabled')
            .removeAttr('aria-disabled')
            .show();

        if (!fileExists) {
            $('<p>', {
                class: 'mb-0 text-danger',
                text: 'This file is missing from project storage. Ask the project owner to upload it again.'
            }).appendTo(previewBody);
            downloadLink.addClass('disabled').attr('aria-disabled', 'true').hide();
        } else if (previewType === 'image') {
            $('<img>', {
                src: fileUrl,
                alt: fileName,
                class: 'img-fluid',
                css: { maxHeight: '70vh' }
            }).on('error', function() {
                previewBody.empty().append($('<p>', {
                    class: 'mb-0 text-danger',
                    text: 'The image could not be loaded. It may have been removed from project storage.'
                }));
                downloadLink.addClass('disabled').attr('aria-disabled', 'true').hide();
            }).appendTo(previewBody);
        } else if (previewType === 'pdf') {
            $('<iframe>', {
                src: fileUrl,
                title: fileName,
                css: { width: '100%', height: '70vh', border: 0 }
            }).appendTo(previewBody);
        } else {
            $('<p>', {
                class: 'mb-0',
                text: 'Preview is not available for this file format. Download the file to view it.'
            }).appendTo(previewBody);
        }

        var previewModalElement = document.getElementById('projectFilePreviewModal');
        if (previewModalElement.parentElement !== document.body) {
            document.body.appendChild(previewModalElement);
        }
        bootstrap.Modal.getOrCreateInstance(previewModalElement).show();
    });

    $('#projectFilePreviewModal').on('hidden.bs.modal', function() {
        $('#projectFilePreviewBody').empty();
    });

    $(document).on('click', '.deleteFile', function(e) {
        e.preventDefault();
        var url = this.href;
        window.open(url, '_blank');

        $('#files-results').load("view.php?project=<?= $project_id ?> #files-results", function() {

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
        $('#project-other-comments-results').load('view.php?project=<?= $project_id ?> #project-other-comments-results');
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
                refreshTaskList();
                $('.card-kanban.tasks[data-task-id="' + taskId + '"]').trigger('click');
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
    function renderExistingTaskFiles(files) {
        var previews = $('#taskExistingFiles').empty();
        (files || []).forEach(function(file) {
            var fileUrl = '../../files/task/' + encodeURIComponent(file.file_name);
            var preview = $('<div>', { class: 'task-existing-file' });
            var isImage = isTaskPreviewImage(file);

            $('<img>', {
                src: isImage ? fileUrl : getFileIcon(file.file_type),
                alt: isImage ? file.file_name : 'Document file'
            }).appendTo(preview);
            $('<span>', {
                class: 'task-existing-file-name',
                text: file.file_name
            }).appendTo(preview);

            previews.append(preview);
        });
    }

    function populateTaskEditForm(res) {
        $('#addTaskMLabel').text('Edit Task');
        $('#addtaskBtn').html('<i class="ri-checkbox-circle-fill"></i> Update');
        $('#m_taskId').val(res.data.task_id);
        $('#taskTitle').val(res.data.task_title);
        $('#taskDescription').val(res.data.description);
        $('#startDate').val(res.start_date);
        $('#deadline').val(res.deadline);
        $('#selectStatus').val(res.data.status).trigger('change');
        renderExistingTaskFiles(res.taskFiles);
        $('#previewTask').modal('hide');
        $('#addTaskM').modal('show');
    }

    $(document).on('click', '.updateTaskP', function(e) {
        var task_id = $('.updateTaskP').attr("data-task-id");
        $(".updateTaskP").attr("data-task-id", '');

        console.log("Task ID:", task_id);
        $.ajax({
            type: "GET",
            url: "ajax.php?updatetaskid=" + task_id,

            success: function(response) {

                var res = jQuery.parseJSON(response);

                if (res.status == 200) {
                    if(<?php echo json_encode($user_role) ?> == 'client'){
                        $('#selectStatusdiv').addClass('d-none');
                    }
                    populateTaskEditForm(res);
                }


            }
        });


    });
</script>

<script>
    $(document).on('click', '.updateTask', function(e) {
        var task_id = $(this).data('task-id');

        console.log("Task ID:", task_id);
        $.ajax({
            type: "GET",
            url: "ajax.php?updatetaskid=" + task_id,

            success: function(response) {

                var res = jQuery.parseJSON(response);

                if (res.status == 200) {
                    if(<?php echo json_encode($user_role) ?> == 'client'){
                        $('#selectStatusdiv').addClass('d-none');
                    }
                    populateTaskEditForm(res);
                }


            }
        });


    });
</script>

<!-- Update task -->

<!-- preview task and commenting -->
<script>
    function isTaskPreviewImage(file) {
        var type = String(file.file_type || file.type || '').toLowerCase();
        var extension = String(file.file_name || file.name || '').split('.').pop().toLowerCase();
        return type.indexOf('image/') === 0 || ['avif', 'bmp', 'gif', 'jpeg', 'jpg', 'png', 'webp'].includes(extension);
    }

    $(document).on('click', '.previewTaskM', function(e) {
        var task_id = $(this).data('task-id');

        $('.task-files-preview').addClass('d-none');
        $('#taskAttachmentList').empty();
        $('#taskPosterName, #taskPostedDate, .downloadtaskfiles').empty();

        console.log(task_id);



        function displayFilesInModal(fileDetails) {
            var attachmentList = $('#taskAttachmentList').empty();

            fileDetails.forEach(function(file) {
                var fileUrl = '../../files/task/' + encodeURIComponent(file.file_name);
                var attachment = $('<div>', { class: 'task-attachment' });

                if (isTaskPreviewImage(file)) {
                    $('<a>', {
                        class: 'task-attachment-image',
                        href: fileUrl,
                        title: file.file_name
                    }).append($('<img>', {
                        class: 'attached-files',
                        src: fileUrl,
                        alt: file.file_name
                    })).appendTo(attachment);
                } else {
                    $('<img>', {
                        class: 'task-attachment-icon',
                        src: getFileIcon(file.file_type),
                        alt: ''
                    }).appendTo(attachment);
                }

                var fileLink = $('<a>', {
                    class: 'task-attachment-name files',
                    href: fileUrl,
                    target: '_blank',
                    rel: 'noopener',
                    title: file.file_name
                });
                $('<span>').text(file.file_name).appendTo(fileLink);
                fileLink.appendTo(attachment);

                attachmentList.append(attachment);
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
                                        <span style="font-size:.7rem"><b>${comment.commenter_name}</b></span>
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
                    if (isTaskPreviewImage(file)) {
                        images++;
                        imageCount++;
                        var fileUrl = '../../files/task/' + encodeURIComponent(file.file_name);
                        var safeFileName = $('<span>').text(file.file_name).html().replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                        commentHtml += `<a ${images > 1 ? 'class="d-none"' : ''} href="${fileUrl}">
                                                <img class="attached-files" src="${fileUrl}" alt="${safeFileName}">
                                            </a><span class="task-attachment-name">${safeFileName}</span>`;
                    }
                });



                commentHtml += `${imageCount > 1 ? `<span class="comment-more-span">+${imageCount - 1} More</span>` : ''}</div><div class="commentfileList">`;

                // Display non-image files
                comment.commenter_files.forEach(function(file) {
                    if (!isTaskPreviewImage(file)) {
                        var fileUrl = '../../files/task/' + encodeURIComponent(file.file_name);
                        var safeFileName = $('<span>').text(file.file_name).html();
                        commentHtml += `<a target="_blank" rel="noopener" class="files" href="${fileUrl}">${safeFileName}</a><br>`;
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


                if (<?php echo json_encode($user_role) ?> == 'admin'||<?php echo json_encode($user_role) ?> == 'super_admin') {

                    commentHtml += `
           <div style="position:absolute;top:6px;right:0;">
                                    <a class="deleteComment" style="color:darkred" target="_blank" href="../delete.php?deleteID=${comment.comment_id}&from=taskComments"><i class="ri-delete-bin-5-line"></i></a>
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


                    var fileDetails = Array.isArray(res.pTaskFiles)
                        ? res.pTaskFiles.filter(function(file) { return file && file.file_name; })
                        : [];
                    var taskComments = res.pTaskComments;
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

                    if (fileDetails.length) {
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
                                        <span style="font-size:.7rem"><b>${comment.commenter_name}</b></span>
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
                    if (isTaskPreviewImage(file)) {
                        images++;
                        imageCount++;
                        var fileUrl = '../../files/task/' + encodeURIComponent(file.file_name);
                        var safeFileName = $('<span>').text(file.file_name).html().replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                        commentHtml += `<a ${images > 1 ? 'class="d-none"' : ''} href="${fileUrl}">
                                                <img class="attached-files" src="${fileUrl}" alt="${safeFileName}">
                                            </a><span class="task-attachment-name">${safeFileName}</span>`;
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
                    if (!isTaskPreviewImage(file)) {
                        var fileUrl = '../../files/task/' + encodeURIComponent(file.file_name);
                        var safeFileName = $('<span>').text(file.file_name).html();
                        commentHtml += `<a target="_blank" rel="noopener" class="files" href="${fileUrl}">${safeFileName}</a><br>`;
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

                if (<?php echo json_encode($user_role) ?> == 'admin'||<?php echo json_encode($user_role) ?> == 'super_admin') {

                    commentHtml += `
<div style="position:absolute;top:6px;right:0;">
                        <a class="deleteComment" style="color:darkred" target="_blank" href="../delete.php?deleteID=${comment.comment_id}&from=taskComments"><i class="ri-delete-bin-5-line"></i></a>
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
                            <span style="font-size:.7rem"><b>${comment.commenter_name}</b></span>
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


                if (<?php echo json_encode($user_role) ?> == 'admin'||<?php echo json_encode($user_role) ?> == 'super_admin') {

                    commentHtml += `
<div style="position:absolute;top:6px;right:0;">
                        <a class="deleteComment" style="color:darkred" target="_blank" href="../delete.php?deleteID=${comment.comment_id}&from=taskComments"><i class="ri-delete-bin-5-line"></i></a>
                        </div>
                        `;
                }



                commentHtml += `</div></div></div></div>`;

                commentsContainer.append(commentHtml);
            });
        }




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
                        $('#files-results').load("view.php?project=<?= $project_id ?> #files-results", function() {

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
                        $('#project-other-comments-results').load('view.php?project=<?= $project_id ?> #project-other-comments-results', function() {
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
                        $('#task-results').load("view.php?project=<?= $project_id ?> #task-results", function() {


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
                        $('#kanban-results').load("view.php?project=<?= $project_id ?> #kanban-results", function() {




















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


                                $('.task-div').removeClass('drop-before drop-after');

                                $.when(
                                    updateTaskOrder(taskIdFromDataAttr, newOrder),
                                    updateTaskStatus(taskIdFromDataAttr, newStatus)
                                ).done(refreshTaskList);
                                $(this).prepend(draggedElement);
                            });

                            function updateTaskOrder(taskId, newOrder) {
                                // Send AJAX request to update task order in the database
                                return $.ajax({
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
                                return $.post('update_task_status.php', {
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
                    if (res.status == 101) {
                        alert(res.message);
                    }
                    if (res.status == 102) {
                        alert(res.message || 'The selected files could not be uploaded.');
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
    function refreshTaskList() {
        $('#task-results').load("view.php?project=<?= $project_id ?> #task-results", function() {
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
                table.column(4).search(selectedStatus).draw();
            });
        });
    }

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

            const taskDraggedBy = <?php echo json_encode($user_id); ?>

            $('.task-div').removeClass('drop-before drop-after');

            $.when(
                updateTaskOrder(taskIdFromDataAttr, newOrder),
                updateTaskStatus(taskIdFromDataAttr, newStatus, taskDraggedBy)
            ).done(refreshTaskList);
            $(this).prepend(draggedElement);
        });

        function updateTaskOrder(taskId, newOrder) {
            // Send AJAX request to update task order in the database
            return $.ajax({
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


        function updateTaskStatus(taskId, newStatus, draggedBy) {
            return $.post('update_task_status.php', {
                    task_id: taskId,
                    new_status: newStatus,
                    dragged_by: draggedBy
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
        var selectedTaskFiles = [];
        var taskFileInput = document.getElementById('fileInput');
        var taskFileDropzone = $('#taskFileDropzone');

        // Function to handle file upload
        $('.upload-file-btn').click(function() {
            taskFileInput.click();
        });

        function syncTaskFileInput() {
            var transfer = new DataTransfer();
            selectedTaskFiles.forEach(function(file) {
                transfer.items.add(file);
            });
            taskFileInput.files = transfer.files;
        }

        function addTaskFiles(files) {
            Array.from(files).forEach(function(file) {
                var duplicate = selectedTaskFiles.some(function(existing) {
                    return existing.name === file.name && existing.size === file.size && existing.lastModified === file.lastModified;
                });
                if (!duplicate) selectedTaskFiles.push(file);
            });

            syncTaskFileInput();
            renderTaskFilePreviews();
        }

        function renderTaskFilePreviews() {
            var previewList = $('#taskFilePreviewList').empty();
            selectedTaskFiles.forEach(function(file, index) {
                var filePreview = $('<div>', {
                    class: 'file-preview',
                    title: file.name
                });
                var removeButton = $('<button>', {
                    type: 'button',
                    class: 'remove-file btn-close',
                    'aria-label': 'Remove ' + file.name
                });

                if (isTaskPreviewImage(file)) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('<img>', { src: e.target.result, alt: file.name }).prependTo(filePreview);
                    };
                    reader.readAsDataURL(file);
                } else {
                    $('<img>', { src: getFileIcon(file.type), alt: 'Document file' }).appendTo(filePreview);
                }

                $('<span>').text(file.name).appendTo(filePreview);
                removeButton.on('click', function() {
                    selectedTaskFiles.splice(index, 1);
                    syncTaskFileInput();
                    renderTaskFilePreviews();
                });
                filePreview.append(removeButton).appendTo(previewList);
            });
        }

        $('#fileInput').on('change', function() {
            addTaskFiles(this.files);
        });

        taskFileDropzone.on('dragenter dragover', function(event) {
            event.preventDefault();
            if (event.originalEvent.dataTransfer) {
                event.originalEvent.dataTransfer.dropEffect = 'copy';
            }
            taskFileDropzone.addClass('is-dragging');
        }).on('dragleave', function(event) {
            if (!taskFileDropzone[0].contains(event.originalEvent.relatedTarget)) {
                taskFileDropzone.removeClass('is-dragging');
            }
        }).on('drop', function(event) {
            event.preventDefault();
            taskFileDropzone.removeClass('is-dragging');
            addTaskFiles(event.originalEvent.dataTransfer.files);
        });

        $('#addTaskM').on('hidden.bs.modal', function() {
            selectedTaskFiles = [];
            renderTaskFilePreviews();
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
            $('#taskAttachmentList').empty();
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

            $('#selectStatusdiv').removeClass('d-none');

            // Reset the form or input values here
            $('#taskTitle').val('');
            $('#taskDescription').val('');
            $('#m_taskId').val('');
            $('#addTaskMLabel').text('Add Task');
            $('#addtaskBtn').html('<i class="ri-checkbox-circle-fill"></i> Save');
            $('#selectStatus').val('To do').trigger('change'); // Reset Select2 dropdown
            $('#startDate').val('');
            $('#deadline').val('');
            $('.file-preview').empty();
            $('#taskExistingFiles').empty();
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
console.log('aba aba');
            table.column(4).search(selectedStatus).draw(); // Assuming status column is the third column (index 2)
        });


     


    });
</script>
<script>
    $(document).ready(function(){
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
        var projectId = <?= json_encode((int) $project_id) ?>;
        var shareSelect = $('#shareUserSelect').select2({
            width: '100%',
            dropdownParent: $('#shareProjectM'),
            placeholder: 'Select users to share with'
        });

        function showShareToast(message, isError) {
            $('#projectShareToastTitle').text(isError ? 'ERROR' : 'SUCCESS');
            $('#projectShareToastMessage').text(message);
            $('#projectShareToast').removeClass('text-bg-danger text-bg-success')
                .addClass(isError ? 'text-bg-danger' : 'text-bg-success');
            bootstrap.Toast.getOrCreateInstance(document.getElementById('projectShareToast')).show();
        }

        function loadProjectShares() {
            $.getJSON('ajax.php', { shareusers: projectId }, function(response) {
                if (response.status !== 200) {
                    $('#projectShareList').text(response.message || 'Unable to load sharing information.');
                    return;
                }

                var sharedAccountsButton = document.getElementById('projectSharedAccountsButton');
                var sharedAccountsPopover = bootstrap.Popover.getInstance(sharedAccountsButton);
                if (sharedAccountsPopover) {
                    sharedAccountsPopover.dispose();
                }
                var sharedAccountsButtonContent = $(sharedAccountsButton).empty();
                var sharedAccountsContent = $('<div>');
                response.shared.forEach(function(account, index) {
                    var profileType = /^[\w.+-]+\/[\w.+-]+$/.test(account.profile_type) ? account.profile_type : 'image/png';
                    var profileSource = 'data:' + profileType + ';base64,' + account.profile;
                    if (index < 3) {
                        $('<img>', {
                            class: 'task-shared-account-avatar',
                            src: profileSource,
                            alt: '',
                            title: account.name
                        }).appendTo(sharedAccountsButtonContent);
                    }
                    $('<div>', { class: 'task-shared-account-row' })
                        .append($('<img>', {
                            class: 'task-shared-account-avatar',
                            src: profileSource,
                            alt: ''
                        }))
                        .append($('<span>').text(account.name))
                        .appendTo(sharedAccountsContent);
                });
                if (response.shared.length > 3) {
                    $('<span>', {
                        class: 'task-shared-account-count',
                        text: '+' + (response.shared.length - 3),
                        'aria-label': (response.shared.length - 3) + ' more shared accounts'
                    }).appendTo(sharedAccountsButtonContent);
                }
                sharedAccountsButton.hidden = response.shared.length === 0;
                sharedAccountsButton.setAttribute('aria-label', response.shared.length + ' shared accounts. Show names.');
                if (response.shared.length) {
                    new bootstrap.Popover(sharedAccountsButton, {
                        title: 'Shared accounts',
                        content: sharedAccountsContent[0],
                        html: true,
                        trigger: 'focus',
                        placement: 'bottom'
                    });
                }

                var select = $('#shareUserSelect').empty();
                response.users.forEach(function(user) {
                    select.append($('<option>').val(user.user_id).text(user.name + ' (' + user.role + ')'));
                });
                shareSelect.val(null).trigger('change');

                var list = $('#projectShareList').empty();
                if (!response.shared.length) {
                    list.text('No users have been added yet.');
                    return;
                }

                response.shared.forEach(function(user) {
                    var entry = $('<div>', {
                        class: 'd-flex align-items-center justify-content-between gap-2 py-1 border-bottom'
                    });
                    entry.append($('<span class="small project-share-name">').text(user.name + ' (' + user.role + ')'));
                    entry.append($('<button>', {
                        type: 'button',
                        class: 'btn btn-outline-danger removeProjectShare remove-project-share',
                        text: 'Remove',
                        'aria-label': 'Remove sharing with ' + user.name,
                        'data-user-id': user.user_id
                    }));
                    list.append(entry);
                });
            }).fail(function() {
                $('#projectShareList').text('Unable to load sharing information.');
            });
        }

        $('#shareProjectM').on('show.bs.modal', loadProjectShares);
        loadProjectShares();
        $('#projectShareList').on('click', '.removeProjectShare', function() {
            var button = $(this).prop('disabled', true).text('Removing...');
            $.post('ajax.php', {
                'unshare-project': true,
                project_id: projectId,
                share_user_id: button.attr('data-user-id')
            }, function(response) {
                if (response.status === 200) {
                    loadProjectShares();
                    showShareToast('Project sharing removed.');
                } else {
                    button.prop('disabled', false).text('Remove');
                    showShareToast(response.message || 'Unable to remove project sharing.', true);
                }
            }, 'json').fail(function() {
                button.prop('disabled', false).text('Remove');
                showShareToast('Unable to remove project sharing.', true);
            });
        });
        $('#addProjectShare').on('click', function() {
            var userIds = shareSelect.val();
            if (!userIds || !userIds.length) {
                return;
            }

            $.post('ajax.php', {
                'share-project': true,
                project_id: projectId,
                share_user_ids: userIds
            }, function(response) {
                if (response.status === 200) {
                    loadProjectShares();
                    showShareToast('Project shared with ' + userIds.length + (userIds.length === 1 ? ' account.' : ' accounts.'));
                } else {
                    showShareToast(response.message || 'Unable to share this project.', true);
                }
            }, 'json').fail(function() {
                showShareToast('Unable to share this project.', true);
            });
        });
    });
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