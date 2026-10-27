<?php
include("../../includes/connections.php");
session_start();

if (!empty($_SESSION['logged_in'])) {
    if ($_SESSION['role'] == 'super_admin') {
        header("location:../../admin/dashboard/dashboard.php");
    }
} else {
    header("location:../../index.php");
    die;
}

$user_id = $_SESSION['user_id'];


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>User - Projects</title>
    <?php include("../header.php") ?>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css">

    <style>
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
                        <h6 style="padding: 12px 0px"><i class="ri-server-line"></i> Project Title</h6>

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
                        <span>Sample Description</span>
                    </div>

                </div>

                <!-- overview -->













                <!-- Task List -->

                <div id="div-list" class="d-none row mt-4 card shadow-sm">
                    <div class="card-header d-flex" style="justify-content: space-between; padding: 6px 12px;">
                        <h6>Tasks</h6>
                        <button style="padding:6px 12px" class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addTaskM"><i class="ri-add-circle-fill"></i> Add Task</button>
                    </div>
                    <div class="card-body" style="position: relative;" id="task-results">


                        <div class="d-flex" style="position:absolute; width: 100px; top:10px; right:200px; z-index:999999999">
                            <select class="status_filter" id="statusFilter">
                                <option value="">-</option>
                                <option value=" ">All</option>
                                <option value="To do">To do</option>
                                <option value="In progress">In progress</option>
                                <option value="Done">Done</option>
                                <option value="For Checking">For Checking</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>

                        <table class="table table_display table-bordered">
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
                                $query = "SELECT * FROM projects_task";
                                $result = mysqli_query($con, $query);
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



                                ?>
                                        <tr>
                                            <td style="border-left: 5px solid <?php
                                                                                if ($task_status == 'To do') {
                                                                                    echo 'darkorange';
                                                                                ?>
"><span><i style="font-size: 1.2rem; color:rgba(0, 0, 0, 0.2)" class="ri-checkbox-blank-line"></i>
                                                <?php
                                                                                } else if ($task_status == 'Done') {
                                                                                    echo 'darkolivegreen';
                                                ?>
                                                    "><span><i style="font-size: 1.2rem; color:rgba(1, 50, 32, 0.5)" class="ri-check-line"></i>
                                                    </span>
                                                <?php
                                                                                } else if ($task_status == 'In progress') {
                                                                                    echo 'darkcyan';
                                                ?>
                                                    "><span><i style="font-size: 1.2rem; color:rgba(0, 0, 0, 0.2)" class="ri-checkbox-blank-line"></i>
                                                    <?php
                                                                                } else if ($task_status == 'For Checking') {
                                                                                    echo '#483D8B';
                                                    ?>
                                                        "><span><i style="font-size: 1.2rem; color:rgba(0, 0, 0, 0.2)" class="ri-checkbox-blank-line"></i>
                                                        <?php
                                                                                } else if ($task_status == 'Completed') {
                                                                                    echo '#343a40';
                                                        ?>
                                                            "><span><i style="font-size: 1.2rem; color:rgba(0, 0, 0, 0.2)" class="ri-checkbox-blank-line"></i>
                                                            <?php
                                                                                } else if ($task_status == 'Cancelled') {
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
                                            <td><a href='view.php?taskID=<?= $task_id ?>'><?= $task_title ?></a></td>
                                            <td><?= $task_startDate ?></td>
                                            <td><?= $task_deadline ?></td>
                                            <td><?= $task_status ?></td>
                                            <td class="text-center"><button class="btn table-btn-edit"><i class="ri-edit-box-line"></i></button></td>


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
                    <div class="card-header d-flex" style="justify-content: space-between; padding: 6px 12px;">
                        <h6>Tasks Kanban</h6>
                        <button style="padding:6px 12px" class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addTaskM"><i class="ri-add-circle-fill"></i> Add Task</button>
                    </div>
                    <div class="card-body" style="background-color: rgba(0, 0, 0, 0.1)" id="kanban-results">
                        <div class="row-kanban">
                            <!-- To do -->
                            <div class="col-kanban">

                                <div class="card-kanban" style="background-color: darkorange;">
                                    <span>To do</span>
                                </div>


                                <!-- task container -->
                                <div id="toDo" class="mt-3 task-div" ondragover="dragOver(event)" ondrop="drop(event)" ondragenter="dragEnter(event)" ondragleave="dragLeave(event)">
                                    
                                
                                
                                <?php 
                                  $query = "SELECT * FROM projects_task where status = 'To do'";
                                  $result = mysqli_query($con, $query);
                                  if (mysqli_num_rows($result) > 0) {
                                      while ($row = $result->fetch_assoc()) {
                                ?>
                                
                                    <div class="card-kanban tasks" draggable="true" data-taskid="<?= $row['task_id']; ?>">

                                        <div class="d-flex gap-2">
                                            <img src="images/Default_pfp.png" alt="">
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
                                <div id="inProgress" class="mt-3 task-div" ondragover="dragOver(event)" ondrop="drop(event)" ondragenter="dragEnter(event)" ondragleave="dragLeave(event)">
                                   
                                <?php 
                                  $query = "SELECT * FROM projects_task where status = 'In progress'";
                                  $result = mysqli_query($con, $query);
                                  if (mysqli_num_rows($result) > 0) {
                                      while ($row = $result->fetch_assoc()) {
                                ?>
                                
                                    <div class="card-kanban tasks" draggable="true" data-taskid="<?= $row['task_id']; ?>">

                                        <div class="d-flex gap-2">
                                            <img src="images/Default_pfp.png" alt="">
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


                            <!-- Done -->
                            <div class="col-kanban">

                                <div class="card-kanban" style="background-color: darkolivegreen;">
                                    <span>Done</span>
                                </div>


                                <!-- task container -->
                                <div id="done" class="mt-3 task-div" ondragover="dragOver(event)" ondrop="drop(event)" ondragenter="dragEnter(event)" ondragleave="dragLeave(event)">
                                   
                                <?php 
                                  $query = "SELECT * FROM projects_task where status = 'Done'";
                                  $result = mysqli_query($con, $query);
                                  if (mysqli_num_rows($result) > 0) {
                                      while ($row = $result->fetch_assoc()) {
                                ?>
                                
                                    <div class="card-kanban tasks" draggable="true" data-taskid="<?= $row['task_id']; ?>">

                                        <div class="d-flex gap-2">
                                            <img src="images/Default_pfp.png" alt="">
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
                            <!-- Done -->


                            <!-- For Checking -->
                            <div class="col-kanban">

                                <div class="card-kanban" style="background-color: #483D8B;">
                                    <span>For Checking</span>
                                </div>

                                  <!-- task container -->
                                  <div id="forChecking" class="mt-3 task-div" ondragover="dragOver(event)" ondrop="drop(event)" ondragenter="dragEnter(event)" ondragleave="dragLeave(event)">
                                   
                                  <?php 
                                  $query = "SELECT * FROM projects_task where status = 'For Checking'";
                                  $result = mysqli_query($con, $query);
                                  if (mysqli_num_rows($result) > 0) {
                                      while ($row = $result->fetch_assoc()) {
                                ?>
                                
                                    <div class="card-kanban tasks" draggable="true" data-taskid="<?= $row['task_id']; ?>">

                                        <div class="d-flex gap-2">
                                            <img src="images/Default_pfp.png" alt="">
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
                                  <div id="completed" class="mt-3 task-div" ondragover="dragOver(event)" ondrop="drop(event)" ondragenter="dragEnter(event)" ondragleave="dragLeave(event)">
                                   
                                  <?php 
                                  $query = "SELECT * FROM projects_task where status = 'Completed'";
                                  $result = mysqli_query($con, $query);
                                  if (mysqli_num_rows($result) > 0) {
                                      while ($row = $result->fetch_assoc()) {
                                ?>
                                
                                    <div class="card-kanban tasks" draggable="true" data-taskid="<?= $row['task_id']; ?>">

                                        <div class="d-flex gap-2">
                                            <img src="images/Default_pfp.png" alt="">
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
                                  <div id="cancelled" class="mt-3 task-div" ondragover="dragOver(event)" ondrop="drop(event)" ondragenter="dragEnter(event)" ondragleave="dragLeave(event)">
                                   
                                  <?php 
                                  $query = "SELECT * FROM projects_task where status = 'Cancelled'";
                                  $result = mysqli_query($con, $query);
                                  if (mysqli_num_rows($result) > 0) {
                                      while ($row = $result->fetch_assoc()) {
                                ?>
                                
                                    <div class="card-kanban tasks" draggable="true" data-taskid="<?= $row['task_id']; ?>">

                                        <div class="d-flex gap-2">
                                            <img src="images/Default_pfp.png" alt="">
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

















            </main>
            <div class="overlay"></div>
        </div>

    </div>
    <!-- partial -->









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
                                    <option value="Done">Done</option>
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
                    <div class="modal-footer" style="justify-content: start;">

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















    <?php include("../footer.php") ?>

    <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script>
</body>


<script>
   $(document).ready(function() {


   

    // Function to handle drop
    function drop(event) {
        event.preventDefault();
        const taskId = event.originalEvent.dataTransfer.getData('text/plain');
        let newStatus = event.target.closest('.task-div').id;

if (newStatus === 'toDo') {
    newStatus = 'To do';
}

if (newStatus === 'inProgress') {
    newStatus = 'In progress';
}

if (newStatus === 'done') {
    newStatus = 'Done';
}

if (newStatus === 'forChecking') {
    newStatus = 'For Checking';
}

if (newStatus === 'completed') {
    newStatus = 'Completed';
}

if (newStatus === 'cancelled') {
    newStatus = 'Cancelled';
}
console.log('Task ID:', taskId);

// Log the newStatus
console.log('New Status:', newStatus);

const draggedElement = $(`[data-taskid='${taskId}']`);

// Log the dragged element
console.log('Dragged Element:', draggedElement);

// Retrieve the taskId from data-taskid attribute of dragged element
const taskIdFromDataAttr = draggedElement.data('taskid');

// Log the taskId retrieved from data attribute
console.log('Task ID from Data Attr:', taskIdFromDataAttr);



        updateTaskStatus(taskIdFromDataAttr, newStatus); // Send task ID and new status to server
        event.target.appendChild(document.getElementById(taskId));
    }

    // Function to update task status via AJAX
    function updateTaskStatus(taskId, newStatus) {
        $.post('update_task_status.php', { task_id: taskId, new_status: newStatus })
            .done(function(response) {
                console.log(response); // Log the response (optional)
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown); // Log AJAX error
            });
    }

    // Add drag event listeners to each draggable element
    $('.card-kanban.tasks').on('dragstart', function(event) {
        event.originalEvent.dataTransfer.setData('text/plain', event.target.id);
    });

    // Add drop event listener to each drop zone
    $('.task-div').on('drop', drop);

    // Prevent default behavior for dragover and dragenter events
    $('.task-div').on('dragover dragenter', function(event) {
        event.preventDefault();
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
        // Listen for the modal's hidden.bs.modal event
        $('#addTaskM').on('hidden.bs.modal', function(e) {


            // Reset the form or input values here
            $('#taskTitle').val('');
            $('#taskDescription').val('');
            $('#selectStatus').val('To do').trigger('change'); // Reset Select2 dropdown
            $('#startDate').val('');
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
            $("#nav-kanban").removeClass("active");
            $("#nav-list").removeClass("active");
            $("#nav-overview").addClass("active");
        });

        $("#nav-list").click(function() {
            $("#div-list").removeClass("d-none");
            $("#div-overview").addClass("d-none");
            $("#div-kanban").addClass("d-none");
            $("#nav-kanban").removeClass("active");
            $("#nav-overview").removeClass("active");
            $("#nav-list").addClass("active");
        });


        $("#nav-kanban").click(function() {
            $("#div-kanban").removeClass("d-none");
            $("#div-list").addClass("d-none");
            $("#div-overview").addClass("d-none");
            $("#nav-overview").removeClass("active");
            $("#nav-list").removeClass("active");
            $("#nav-kanban").addClass("active");
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

            table.column(4).search(selectedStatus).draw(); // Assuming status column is the third column (index 2)
        });



    });
</script>

</html>