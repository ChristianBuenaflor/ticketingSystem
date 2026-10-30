<?php
session_start();

include("../../includes/connections.php");
require_once("../../includes/ticket_upload.php");

if (isset($_GET['action']) && $_GET['action'] == 'get_technicians' && isset($_GET['category_id'])) {
    $technicians = [];
    $categoryId = filter_var($_GET['category_id'], FILTER_VALIDATE_INT);
    if (!$categoryId || $categoryId < 1) {
        http_response_code(400);
        echo json_encode(['status' => 400, 'message' => 'Invalid category.']);
        exit;
    }

    $stmt = $con->prepare(
        "SELECT u.user_id, ui.first_name, ui.last_name
         FROM users u
         JOIN users_info ui ON u.user_id = ui.user_id
         WHERE u.role = 'clerk' AND ui.expertise = ?
         ORDER BY ui.last_name"
    );
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['status' => 500, 'message' => 'Unable to load technicians.']);
        exit;
    }
    $stmt->bind_param('i', $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $technicians[] = $row;
        }
    }
    $stmt->close();
    
    echo json_encode([
        'status' => 200,
        'technicians' => $technicians
    ]);
    exit;
}

if (empty($_SESSION['logged_in']) || (int) ($_SESSION['user_id'] ?? 0) < 1
    || !in_array(normalizeUserRole((string) ($_SESSION['role'] ?? '')), ['admin', 'director', 'head', 'clerk'], true)) {
    http_response_code(401);
    echo json_encode(['status' => 401, 'message' => 'Authentication required.']);
    exit;
}

$authenticatedUserId = (int) $_SESSION['user_id'];
$user_role = normalizeUserRole((string) $_SESSION['role']);
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && (!isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        || !hash_equals($_SESSION['csrf_token'], (string) $_POST['csrf_token']))) {
    http_response_code(403);
    echo json_encode(['status' => 403, 'message' => 'Invalid security token.']);
    exit;
}

if (isset($_GET['updateid'])) {
    $id = filter_var($_GET['updateid'], FILTER_VALIDATE_INT);
    if (!$id || $id < 1) {
        http_response_code(400);
        echo json_encode(['status' => 400, 'message' => 'Invalid ticket ID.']);
        exit;
    }
    $stmt = $con->prepare("SELECT * FROM tickets WHERE ticket_id = ?");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['status' => 500, 'message' => 'Unable to load ticket.']);
        exit;
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    if ($row) {
        if (!canAccessTicket($row, $user_role, $authenticatedUserId)) {
            http_response_code(403);
            echo json_encode(['status' => 403, 'message' => 'Forbidden.']);
            exit;
        }

        $res = [
            'status' => 200,
            'data' => $row,
        ];
        echo json_encode($res);
        return false;
    }
}






if (isset($_POST['post-ticket'])) {
    $user_id = $authenticatedUserId;
    if (!isManagementRole($user_role)) {
        http_response_code(403);
        echo json_encode(['status' => 403, 'message' => 'Clerks may only comment on existing tickets.']);
        exit;
    }

    $rawTicketId = $_POST['m_ticketId'] ?? '';
    $m_ticketId = $rawTicketId === '' ? 0 : filter_var($rawTicketId, FILTER_VALIDATE_INT);
    if ($m_ticketId === false || $m_ticketId < 0) {
        http_response_code(400);
        echo json_encode(['status' => 400, 'message' => 'Invalid ticket ID.']);
        exit;
    }

    if (isManagementRole($user_role)) {
        $title = trim((string) ($_POST['ticket_title'] ?? ''));
        $ticket_category = filter_var($_POST['project'] ?? null, FILTER_VALIDATE_INT);
        $ticket_type_id = filter_var($_POST['ticket_type'] ?? null, FILTER_VALIDATE_INT);
        $description = trim((string) ($_POST['ticket_description'] ?? ''));

        if ($title === '' || strlen($title) > 255) {
            $res = [
                'status' => 101,
            ];
            http_response_code(400);
            echo json_encode($res);
            exit;
        }

        if (!$ticket_category || $ticket_category < 1 || !$ticket_type_id || $ticket_type_id < 1
            || strlen($description) > 1000) {
            $res = [
                'status' => 102,
            ];
            http_response_code(400);
            echo json_encode($res);
            exit;
        }

        $statusOptions = ['open' => 'Open', 'in progress' => 'In progress', 'closed' => 'Closed'];
        $status = $statusOptions[strtolower(trim((string) ($_POST['ticket_status'] ?? 'Open')))] ?? '';
        if ($status === '') {
            http_response_code(400);
            echo json_encode(['status' => 400, 'message' => 'Invalid ticket status.']);
            exit;
        }

        $queryck = "SELECT users.user_id FROM users
                    INNER JOIN users_info ON users.user_id = users_info.user_id
                    WHERE users.role = 'clerk' AND users_info.expertise = ?";
        $technicianStmt = $con->prepare($queryck);
        if (!$technicianStmt) {
            http_response_code(500);
            echo json_encode(['status' => 500, 'message' => 'Unable to assign a clerk.']);
            exit;
        }
        $technicianStmt->bind_param('i', $ticket_category);
        $technicianStmt->execute();
        $resultck = $technicianStmt->get_result();

        $technicianTickets = [];

        if ($resultck->num_rows > 0) {
            while ($rowck = $resultck->fetch_assoc()) {
                $technician = $rowck['user_id'];

                $countStmt = $con->prepare("SELECT COUNT(*) AS ticket_count FROM tickets WHERE technician = ?");
                if (!$countStmt) {
                    $technicianStmt->close();
                    http_response_code(500);
                    echo json_encode(['status' => 500, 'message' => 'Unable to assign a clerk.']);
                    exit;
                }
                $technicianId = (int) $technician;
                $countStmt->bind_param('i', $technicianId);
                $countStmt->execute();
                $tickets = (int) $countStmt->get_result()->fetch_assoc()['ticket_count'];
                $countStmt->close();

                // Store the technician and their ticket count in the array
                $technicianTickets[] = ['technician' => $technician, 'tickets' => $tickets];
            }
        }
        $technicianStmt->close();


        $lowestTechnician = null;
        $minTickets = PHP_INT_MAX;

        foreach ($technicianTickets as $entry) {
            if ($entry['tickets'] < $minTickets) {
                $minTickets = $entry['tickets'];
                $lowestTechnician = $entry['technician'];
            }
        }
    }


    if ($m_ticketId > 0) {
        $ticketStmt = $con->prepare("SELECT user_id, technician FROM tickets WHERE ticket_id = ?");
        if (!$ticketStmt) {
            http_response_code(500);
            echo json_encode(['status' => 500, 'message' => 'Unable to verify ticket access.']);
            exit;
        }
        $ticketStmt->bind_param('i', $m_ticketId);
        $ticketStmt->execute();
        $existingTicket = $ticketStmt->get_result()->fetch_assoc();
        $ticketStmt->close();
        if (!$existingTicket) {
            http_response_code(404);
            echo json_encode(['status' => 404, 'message' => 'Ticket not found.']);
            exit;
        }
        if (!canAccessTicket($existingTicket, $user_role, $authenticatedUserId)) {
            http_response_code(403);
            echo json_encode(['status' => 403, 'message' => 'Forbidden.']);
            exit;
        }

        date_default_timezone_set('Asia/Manila');
        $last_activity = date("m/d/Y h:i a");

        $stmt = $con->prepare("UPDATE tickets SET title = ?, category_id = ?, type_id = ?, description = ?, technician = ?, status = ?, last_activity = ? WHERE ticket_id = ?");
        if (!$stmt) {
            http_response_code(500);
            echo json_encode(['status' => 500, 'message' => 'Unable to update ticket.']);
            exit;
        }
        $technicianId = (int) ($lowestTechnician ?? 0);
        $stmt->bind_param('siisissi', $title, $ticket_category, $ticket_type_id, $description, $technicianId, $status, $last_activity, $m_ticketId);
        $resultf = $stmt->execute();
        $stmt->close();



        if ($resultf) {

            $res = [
                'status' => 200,
                'id' => $m_ticketId,
            ];
            echo json_encode($res);
            return;
        } else {
            cleanupStoredTicketUploads($con, $uploadedFiles);
            http_response_code(500);
            echo json_encode(['status' => 500, 'message' => 'Unable to create ticket.']);
        }
    } else {








        try {
            $uploadedFiles = storeTicketUploads($con, 'ticket_files');
        } catch (RuntimeException $exception) {
            error_log('Ticket upload failed: ' . $exception->getMessage());
            http_response_code(400);
            echo json_encode(['status' => 400, 'message' => $exception->getMessage()]);
            exit;
        }

        date_default_timezone_set('Asia/Manila');
        $createdDate = date("m/d/Y h:i a");
        $fileIdsString = implode(',', $uploadedFiles['ids']);
        $technicianId = (int) ($lowestTechnician ?? 0);
        $stmt = $con->prepare(
            "INSERT INTO tickets (user_id, title, category_id, type_id, description, status, files, technician, created)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        if (!$stmt) {
            cleanupStoredTicketUploads($con, $uploadedFiles);
            http_response_code(500);
            echo json_encode(['status' => 500, 'message' => 'Unable to create ticket.']);
            exit;
        }
        $stmt->bind_param('isiisssis', $user_id, $title, $ticket_category, $ticket_type_id, $description, $status, $fileIdsString, $technicianId, $createdDate);
        $result = $stmt->execute();
        $last_id = $result ? (int) $con->insert_id : 0;
        $stmt->close();

        if ($result) {
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
    $comment_user_id = $authenticatedUserId;
    $comment_ticket_id = filter_var($_POST['comment_ticket_id'] ?? null, FILTER_VALIDATE_INT);
    $comment_textarea = trim((string) ($_POST['comment_textarea'] ?? ''));
    if (!$comment_ticket_id || $comment_ticket_id < 1 || strlen($comment_textarea) > 250) {
        http_response_code(400);
        echo json_encode(['status' => 400, 'message' => 'Invalid comment.']);
        exit;
    }

    $ticketStmt = $con->prepare("SELECT user_id, technician FROM tickets WHERE ticket_id = ?");
    if (!$ticketStmt) {
        http_response_code(500);
        echo json_encode(['status' => 500, 'message' => 'Unable to verify ticket access.']);
        exit;
    }
    $ticketStmt->bind_param('i', $comment_ticket_id);
    $ticketStmt->execute();
    $commentTicket = $ticketStmt->get_result()->fetch_assoc();
    $ticketStmt->close();
    if (!$commentTicket) {
        http_response_code(404);
        echo json_encode(['status' => 404, 'message' => 'Ticket not found.']);
        exit;
    }
    if (!canAccessTicket($commentTicket, $user_role, $authenticatedUserId)) {
        http_response_code(403);
        echo json_encode(['status' => 403, 'message' => 'Forbidden.']);
        exit;
    }
    if ($user_role === 'clerk') {
        $verifiedStmt = $con->prepare("SELECT verified FROM users WHERE user_id = ?");
        if (!$verifiedStmt) {
            http_response_code(500);
            echo json_encode(['status' => 500, 'message' => 'Unable to verify account.']);
            exit;
        }
        $verifiedStmt->bind_param('i', $comment_user_id);
        $verifiedStmt->execute();
        $verified = $verifiedStmt->get_result()->fetch_assoc();
        $verifiedStmt->close();
        if (!$verified || (int) $verified['verified'] !== 1) {
            http_response_code(403);
            echo json_encode(['status' => 403, 'message' => 'Please verify your email before posting comments.']);
            exit;
        }
    }

    if ($comment_textarea === '' && empty($_FILES['comment_files']['name'][0])) {
        $res = [
            'status' => 101,
        ];
        echo json_encode($res);
        exit;
    }

    try {
        $uploadedFiles = storeTicketUploads($con, 'comment_files');
    } catch (RuntimeException $exception) {
        error_log('Ticket comment upload failed: ' . $exception->getMessage());
        http_response_code(400);
        echo json_encode(['status' => 400, 'message' => $exception->getMessage()]);
        exit;
    }

    date_default_timezone_set('Asia/Manila');
    $date_stamp = date("m/d/Y h:i a");
    $fileIdsString = implode(',', $uploadedFiles['ids']);
    $commentStmt = $con->prepare(
        "INSERT INTO ticket_comments (ticket_id, user_id, description, files, date_stamp) VALUES (?, ?, ?, ?, ?)"
    );
    if (!$commentStmt) {
        cleanupStoredTicketUploads($con, $uploadedFiles);
        http_response_code(500);
        echo json_encode(['status' => 500, 'message' => 'Unable to save comment.']);
        exit;
    }
    $commentStmt->bind_param('iisss', $comment_ticket_id, $comment_user_id, $comment_textarea, $fileIdsString, $date_stamp);
    $result = $commentStmt->execute();
    $commentStmt->close();
    if (!$result) {
        cleanupStoredTicketUploads($con, $uploadedFiles);
        http_response_code(500);
        echo json_encode(['status' => 500, 'message' => 'Unable to save comment.']);
        exit;
    }

    $last_activity = date("m/d/Y h:i a");
    $activityStmt = $con->prepare("UPDATE tickets SET last_activity = ? WHERE ticket_id = ?");
    if (!$activityStmt) {
        error_log('Unable to prepare ticket activity update after comment.');
    } else {
        $activityStmt->bind_param('si', $last_activity, $comment_ticket_id);
        if (!$activityStmt->execute()) {
            error_log('Unable to update ticket activity after comment.');
        }
        $activityStmt->close();
    }

    echo json_encode(['status' => 100]);
    exit;
}
































if (isset($_POST['evaluateTechnicianForm'])) {
    if ($user_role !== 'client') {
        http_response_code(403);
        echo json_encode(['status' => 403, 'message' => 'Only ticket owners may submit evaluations.']);
        exit;
    }

    $ticketId = filter_var($_POST['ticketId'] ?? null, FILTER_VALIDATE_INT);
    $solutionSatisfaction = filter_var($_POST['solutionSatisfaction'] ?? null, FILTER_VALIDATE_INT);
    $customerSatisfaction = filter_var($_POST['customerSatisfaction'] ?? null, FILTER_VALIDATE_INT);
    $comments = trim((string) ($_POST['comments'] ?? ''));
    if (!$ticketId || $ticketId < 1
        || !$solutionSatisfaction || $solutionSatisfaction < 1 || $solutionSatisfaction > 5
        || !$customerSatisfaction || $customerSatisfaction < 1 || $customerSatisfaction > 5) {
        http_response_code(400);
        echo json_encode(['status' => 101, 'message' => 'Select valid evaluation ratings.']);
        exit;
    }

    $ticketStmt = $con->prepare("SELECT user_id, technician FROM tickets WHERE ticket_id = ?");
    if (!$ticketStmt) {
        http_response_code(500);
        echo json_encode(['status' => 500, 'message' => 'Unable to verify ticket access.']);
        exit;
    }
    $ticketStmt->bind_param('i', $ticketId);
    $ticketStmt->execute();
    $ticket = $ticketStmt->get_result()->fetch_assoc();
    $ticketStmt->close();
    if (!$ticket || (int) $ticket['user_id'] !== $authenticatedUserId) {
        http_response_code(403);
        echo json_encode(['status' => 403, 'message' => 'Forbidden.']);
        exit;
    }
    $ticketTechnician = (int) $ticket['technician'];
    if ($ticketTechnician < 1) {
        http_response_code(400);
        echo json_encode(['status' => 400, 'message' => 'This ticket has no assigned technician.']);
        exit;
    }

    date_default_timezone_set('Asia/Manila');
    $dateStamp = date("m/d/Y h:i a");
    $con->begin_transaction();
    $deleteStmt = $con->prepare("DELETE FROM evaluation WHERE ticket_id = ?");
    $insertStmt = $con->prepare(
        "INSERT INTO evaluation (ticket_id, technician_id, client_id, solution, customer_satisfaction, comments, date_stamp)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    if (!$deleteStmt || !$insertStmt) {
        $con->rollback();
        if ($deleteStmt) {
            $deleteStmt->close();
        }
        if ($insertStmt) {
            $insertStmt->close();
        }
        http_response_code(500);
        echo json_encode(['status' => 500, 'message' => 'Unable to save evaluation.']);
        exit;
    }
    $deleteStmt->bind_param('i', $ticketId);
    $deleted = $deleteStmt->execute();
    $deleteStmt->close();
    if (!$deleted) {
        $con->rollback();
        $insertStmt->close();
        http_response_code(500);
        echo json_encode(['status' => 500, 'message' => 'Unable to save evaluation.']);
        exit;
    }

    $insertStmt->bind_param(
        'iiiiiss',
        $ticketId,
        $ticketTechnician,
        $authenticatedUserId,
        $solutionSatisfaction,
        $customerSatisfaction,
        $comments,
        $dateStamp
    );
    $saved = $insertStmt->execute();
    $insertStmt->close();
    if (!$saved) {
        $con->rollback();
        http_response_code(500);
        echo json_encode(['status' => 500, 'message' => 'Unable to save evaluation.']);
        exit;
    }
    $con->commit();
    echo json_encode(['status' => 100]);
    exit;
}
