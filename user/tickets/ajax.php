<?php
session_start();

include("../../includes/connections.php");

if (isset($_GET['action']) && $_GET['action'] == 'get_technicians' && isset($_GET['category_id'])) {
    $category_id = mysqli_real_escape_string($con, $_GET['category_id']);
    
    $query = "SELECT u.user_id, ui.first_name, ui.last_name 
              FROM users u 
              JOIN users_info ui ON u.user_id = ui.user_id 
              WHERE u.role = 'technician' 
              AND ui.expertise = '$category_id'
              ORDER BY ui.last_name";
              
    $result = mysqli_query($con, $query);
    $technicians = [];
    
    if (mysqli_num_rows($result) > 0) {  // Fixed: Added missing parenthesis and > 0
        while ($row = mysqli_fetch_assoc($result)) {
            $technicians[] = $row;
        }
    }
    
    echo json_encode([
        'status' => 200,
        'technicians' => $technicians
    ]);
    exit;
}

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $verified_check = mysqli_query($con, "SELECT verified FROM users WHERE user_id = '$user_id'");
    
    if ($verified_check && mysqli_num_rows($verified_check) > 0) {
        $user_data = mysqli_fetch_assoc($verified_check);
        if ($user_data['verified'] != 1 && $_SESSION['role'] == 'client') {
            $res = [
                'status' => 403,
                'message' => 'Please verify your email before accessing the system'
            ];
            echo json_encode($res);
            exit;
        }
    }
}

$user_role = $_SESSION['role'];


if (isset($_GET['updateid'])) {
    $id = $_GET['updateid'];
    $sql = "SELECT * FROM tickets WHERE ticket_id = $id";
    $result = mysqli_query($con, $sql);
    $row = mysqli_fetch_assoc($result);
    if ($row) {

        $res = [
            'status' => 200,
            'data' => $row,
        ];
        echo json_encode($res);
        return false;
    }
}






if (isset($_POST['post-ticket'])) {
    // Add this verification check at the VERY TOP of the post-ticket block
    $user_id = mysqli_real_escape_string($con, $_POST['user_id']);
    $verified = mysqli_fetch_assoc(mysqli_query($con, "SELECT verified FROM users WHERE user_id = '$user_id'"));
    
    if ($verified['verified'] != 1) {
        echo json_encode([
            'status' => 403,
            'message' => 'Please verify your email before creating tickets. Check your inbox or spam folder.'
        ]);
        exit;
    }



    $m_ticketId =  mysqli_real_escape_string($con, $_POST['m_ticketId']);
    $user_id =  mysqli_real_escape_string($con, $_POST['user_id']);
    if ($user_role != 'technician') {
        $title =  mysqli_real_escape_string($con, $_POST['ticket_title']);
        $ticket_category = mysqli_real_escape_string($con, $_POST['project']);
        $ticket_type_id = mysqli_real_escape_string($con, $_POST['ticket_type']);
        $description = mysqli_real_escape_string($con, $_POST['ticket_description']);


        if ($title == '') {
            $res = [
                'status' => 101,
            ];
            echo json_encode($res);
            return;
        }

        if ($ticket_category == '') {
            $res = [
                'status' => 102,
            ];
            echo json_encode($res);
            return;
        }




        $queryck = "SELECT * FROM users right join users_info on users.user_id = users_info.user_id WHERE users.role = 'technician' and users_info.expertise = $ticket_category";
        $resultck = mysqli_query($con, $queryck);

        $technicianTickets = [];

        if (mysqli_num_rows($resultck) > 0) {
            while ($rowck = $resultck->fetch_assoc()) {
                $technician = $rowck['user_id'];

                $querysk = "SELECT * FROM tickets WHERE technician = '$technician'";
                $resultsk = mysqli_query($con, $querysk);
                $tickets = mysqli_num_rows($resultsk);

                // Store the technician and their ticket count in the array
                $technicianTickets[] = ['technician' => $technician, 'tickets' => $tickets];
            }
        }


        $lowestTechnician = null;
        $minTickets = PHP_INT_MAX;

        foreach ($technicianTickets as $entry) {
            if ($entry['tickets'] < $minTickets) {
                $minTickets = $entry['tickets'];
                $lowestTechnician = $entry['technician'];
            }
        }
    }


    $status = mysqli_real_escape_string($con, $_POST['ticket_status']);












    if ($m_ticketId != '') {

        date_default_timezone_set('Asia/Manila');
        $last_activity = date("m/d/Y h:i a");

        if ($user_role == 'technician') {
            $sqlf = "UPDATE tickets SET status='$status', last_activity='$last_activity' WHERE ticket_id = '$m_ticketId'";
            $resultf = mysqli_query($con, $sqlf);
        } else if ($user_role == 'client') {
            $sqlf = "UPDATE tickets SET title='$title', category_id=$ticket_category, type_id='$ticket_type_id', description='$description', technician=$lowestTechnician, last_activity='$last_activity' WHERE ticket_id = '$m_ticketId'";
            $resultf = mysqli_query($con, $sqlf);
        } else {
            $sqlf = "UPDATE tickets SET title='$title', category_id=$ticket_category, type_id='$ticket_type_id', description='$description', technician=$lowestTechnician, status='$status', last_activity='$last_activity' WHERE ticket_id = '$m_ticketId'";
            $resultf = mysqli_query($con, $sqlf);
        }



        if ($resultf) {

            $res = [
                'status' => 200,
                'id' => $m_ticketId,
            ];
            echo json_encode($res);
            return;
        } else {
            echo "error";
        }
    } else {








        $uploadedFiles = array();

        if (!empty($_FILES['ticket_files']['tmp_name'][0])) {
            $fileCount = count($_FILES['ticket_files']['tmp_name']);

            for ($i = 0; $i < $fileCount; $i++) {
                $file_name = $_FILES['ticket_files']['name'][$i];
                $file_type = $_FILES['ticket_files']['type'][$i];
                $file_tmp_name = $_FILES['ticket_files']['tmp_name'][$i];

                // Move the file to the 'files' folder
                $target_path = '../../files/ticket/' . $file_name;
                if (move_uploaded_file($file_tmp_name, $target_path)) {
                    // Insert file name and file type into the database
                    $query = "INSERT INTO files (file_name, file_type) 
                          VALUES ('$file_name', '$file_type')";
                    $result = mysqli_query($con, $query);
                    if ($result) {
                        $uploadedFiles[] = mysqli_insert_id($con); // Store the ID of the inserted file
                    } else {
                        echo "Error inserting file into database: " . mysqli_error($con);
                        exit;
                    }
                } else {
                    echo "Error moving uploaded file to destination folder.";
                    exit;
                }
            }
        }

        date_default_timezone_set('Asia/Manila');
        $createdDate = date("m/d/Y h:i a");


        if (!empty($uploadedFiles)) {
            $fileIdsString = implode(',', $uploadedFiles);
            $query = "INSERT INTO tickets (user_id, title, category_id, type_id, description, status, files, technician, created) 
              VALUES ('$user_id', '$title', '$ticket_category', '$ticket_type_id', '$description', '$status', '$fileIdsString', '$lowestTechnician', '$createdDate')";
        } else {
            $query = "INSERT INTO tickets (user_id, title, category_id, type_id, description, technician, created, status) 
              VALUES ('$user_id', '$title', '$ticket_category', '$ticket_type_id', '$description', '$lowestTechnician', '$createdDate', '$status')";
        }

        $result = mysqli_query($con, $query);

        if ($result) {
            $last_id = $con->insert_id;

            $res = [
                'status' => 100,
                'id' => sprintf("%04d", $last_id),
            ];
            echo json_encode($res);
            return;
        } else {
            echo "error";
        }
    }











    mysqli_close($con);
}





































if (isset($_POST['post-ticket-comment'])) {
    // NEW: Verification check for comments
    $comment_user_id = mysqli_real_escape_string($con, $_POST['users_id']);
    $verified_check = mysqli_query($con, "SELECT verified FROM users WHERE user_id = '$comment_user_id'");
    
    if ($verified_check && mysqli_num_rows($verified_check) > 0) {
        $user_data = mysqli_fetch_assoc($verified_check);
        if ($user_data['verified'] != 1) {
            $res = [
                'status' => 403,
                'message' => 'Please verify your email before posting comments'
            ];
            echo json_encode($res);
            exit;
        }
    }

    $comment_user_id =  mysqli_real_escape_string($con, $_POST['users_id']);
    $comment_ticket_id =  mysqli_real_escape_string($con, $_POST['comment_ticket_id']);
    $comment_textarea = mysqli_real_escape_string($con, $_POST['comment_textarea']);


    if ($comment_textarea == '' && empty($_FILES['comment_files']['tmp_name'][0])) {
        $res = [
            'status' => 101,
        ];
        echo json_encode($res);
        return;
    }

    $uploadedFiles = array();

    if (!empty($_FILES['comment_files']['tmp_name'][0])) {
        $fileCount = count($_FILES['comment_files']['tmp_name']);

        for ($i = 0; $i < $fileCount; $i++) {
            $file_name = $_FILES['comment_files']['name'][$i];
            $file_type = $_FILES['comment_files']['type'][$i];
            $file_tmp_name = $_FILES['comment_files']['tmp_name'][$i];

            // Move the file to the 'files' folder
            $target_path = '../../files/ticket/' . $file_name;
            if (move_uploaded_file($file_tmp_name, $target_path)) {
                // Insert file name and file type into the database
                $query = "INSERT INTO files (file_name, file_type) 
                      VALUES ('$file_name', '$file_type')";
                $result = mysqli_query($con, $query);
                if ($result) {
                    $uploadedFiles[] = mysqli_insert_id($con); // Store the ID of the inserted file
                } else {
                    echo "Error inserting file into database: " . mysqli_error($con);
                    exit;
                }
            } else {
                echo "Error moving uploaded file to destination folder.";
                exit;
            }
        }
    }

    date_default_timezone_set('Asia/Manila');
    $date_stamp = date("m/d/Y h:i a");


    if (!empty($uploadedFiles)) {
        $fileIdsString = implode(',', $uploadedFiles);
        $query = "INSERT INTO ticket_comments (ticket_id, user_id, description, files, date_stamp) 
              VALUES ('$comment_ticket_id', '$comment_user_id', '$comment_textarea', '$fileIdsString', '$date_stamp')";
    } else {
        $query = "INSERT INTO ticket_comments (ticket_id, user_id, description, date_stamp) 
              VALUES ('$comment_ticket_id', '$comment_user_id', '$comment_textarea', '$date_stamp')";
    }

    $result = mysqli_query($con, $query);


    $last_activity = date("m/d/Y h:i a");
    $sql = "update `tickets` set last_activity='$last_activity' where ticket_id='$comment_ticket_id'";
    $updateresult = mysqli_query($con, $sql);



    if ($result) {

        $res = [
            'status' => 100,
        ];
        echo json_encode($res);
        return;
    } else {
        echo "error";
    }



    mysqli_close($con);
}
































if (isset($_POST['evaluateTechnicianForm'])) {



    $ticketId =  mysqli_real_escape_string($con, $_POST['ticketId']);
    $ticketTechnician =  mysqli_real_escape_string($con, $_POST['ticketTechnician']);
    $ticketClient =  mysqli_real_escape_string($con, $_POST['ticketClient']);
    $comments =  mysqli_real_escape_string($con, $_POST['comments']);
    $solutionSatisfaction = isset($_POST['solutionSatisfaction']) ? mysqli_real_escape_string($con, $_POST['solutionSatisfaction']) : '';
    $customerSatisfaction = isset($_POST['customerSatisfaction']) ? mysqli_real_escape_string($con, $_POST['customerSatisfaction']) : '';
    $comments = isset($_POST['comments']) ? mysqli_real_escape_string($con, $_POST['comments']) : '';

    if (empty($solutionSatisfaction) || empty($customerSatisfaction)) {
        $res = [
            'status' => 101,
        ];
        echo json_encode($res);
        return;
    }



    date_default_timezone_set('Asia/Manila');
    $dateStamp = date("m/d/Y h:i a");


    $sql = "delete from `evaluation` where ticket_id =$ticketId";
    $delete_result = mysqli_query($con, $sql);

    $query = "INSERT INTO evaluation (ticket_id, technician_id, client_id, solution, customer_satisfaction, comments, date_stamp) 
              VALUES ('$ticketId', '$ticketTechnician', '$ticketClient', '$solutionSatisfaction', '$customerSatisfaction', '$comments', '$dateStamp')";
    $result = mysqli_query($con, $query);

    if ($result) {

        $res = [
            'status' => 100,
        ];
        echo json_encode($res);
        return;
    } else {
        echo "error";
    }




    mysqli_close($con);
}
