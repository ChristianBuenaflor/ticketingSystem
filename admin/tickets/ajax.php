<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

include("../../includes/connections.php");

function ticketAjaxResponse(int $httpStatus, array $payload): void
{
    http_response_code($httpStatus);
    echo json_encode($payload);
    exit;
}

function cleanupTicketUploads(mysqli $con, array $fileIds, array $paths): void
{
    foreach ($fileIds as $fileId) {
        $stmt = $con->prepare("DELETE FROM files WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $fileId);
            $stmt->execute();
            $stmt->close();
        }
    }

    foreach ($paths as $path) {
        if (is_file($path)) {
            unlink($path);
        }
    }
}

if (empty($_SESSION['logged_in']) || (int) ($_SESSION['user_id'] ?? 0) < 1) {
    ticketAjaxResponse(401, ['status' => 401, 'message' => 'Authentication required.']);
}

if (!isManagementRole((string) ($_SESSION['role'] ?? ''))) {
    ticketAjaxResponse(403, ['status' => 403, 'message' => 'Forbidden.']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && (!isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        || !hash_equals($_SESSION['csrf_token'], (string) $_POST['csrf_token']))) {
    ticketAjaxResponse(403, ['status' => 403, 'message' => 'Invalid security token.']);
}

if (isset($_GET['updateid'])) {
    $ticketId = filter_var($_GET['updateid'], FILTER_VALIDATE_INT);
    if (!$ticketId || $ticketId < 1) {
        ticketAjaxResponse(400, ['status' => 400, 'message' => 'Invalid ticket ID.']);
    }

    $stmt = $con->prepare("SELECT * FROM tickets WHERE ticket_id = ?");
    if (!$stmt) {
        ticketAjaxResponse(500, ['status' => 500, 'message' => 'Unable to load ticket.']);
    }
    $stmt->bind_param("i", $ticketId);
    if (!$stmt->execute()) {
        $stmt->close();
        ticketAjaxResponse(500, ['status' => 500, 'message' => 'Unable to load ticket.']);
    }
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!$row) {
        ticketAjaxResponse(404, ['status' => 404, 'message' => 'Ticket not found.']);
    }

    ticketAjaxResponse(200, ['status' => 200, 'data' => $row]);
}

if (!isset($_POST['post-ticket'])) {
    ticketAjaxResponse(400, ['status' => 400, 'message' => 'Invalid request.']);
}

$rawTicketId = $_POST['m_ticketId'] ?? '';
$ticketId = $rawTicketId === '' ? 0 : filter_var($rawTicketId, FILTER_VALIDATE_INT);
$userId = filter_var($_POST['user_id'] ?? '', FILTER_VALIDATE_INT);
$projectId = ($_POST['project'] ?? '') === '' ? 0 : filter_var($_POST['project'], FILTER_VALIDATE_INT);
$rawTicketTypeId = $_POST['ticket_type'] ?? '';
$ticketTypeId = $rawTicketTypeId === '' ? 0 : filter_var($rawTicketTypeId, FILTER_VALIDATE_INT);
$title = trim((string) ($_POST['ticket_title'] ?? ''));
$description = trim((string) ($_POST['ticket_description'] ?? ''));
$status = trim((string) ($_POST['ticket_status'] ?? ''));
$statusOptions = ['open' => 'Open', 'in progress' => 'In progress', 'closed' => 'Closed'];
$status = $statusOptions[strtolower($status)] ?? '';

if ($ticketId === false || $ticketId < 0 || !$userId || $userId < 1
    || $projectId === false || $projectId < 0 || $ticketTypeId === false || $ticketTypeId < 0) {
    ticketAjaxResponse(400, ['status' => 400, 'message' => 'Invalid ticket data.']);
}

if ($title === '' || strlen($title) > 255) {
    ticketAjaxResponse(400, ['status' => 101, 'message' => 'A valid title is required.']);
}
if (strlen($description) > 1000) {
    ticketAjaxResponse(400, ['status' => 400, 'message' => 'Description is too long.']);
}
if (!in_array($status, ['Open', 'In progress', 'Closed'], true)) {
    ticketAjaxResponse(400, ['status' => 103, 'message' => 'Invalid ticket status.']);
}

if ($ticketId > 0) {
    $lastActivity = date('m/d/Y h:i a');
    $stmt = $con->prepare(
        "UPDATE tickets SET title = ?, project_id = ?, type_id = ?, description = ?, status = ?, last_activity = ? WHERE ticket_id = ?"
    );
    if (!$stmt) {
        ticketAjaxResponse(500, ['status' => 500, 'message' => 'Unable to update ticket.']);
    }
    $stmt->bind_param("siisssi", $title, $projectId, $ticketTypeId, $description, $status, $lastActivity, $ticketId);
    $updated = $stmt->execute();
    $stmt->close();
    if (!$updated) {
        ticketAjaxResponse(500, ['status' => 500, 'message' => 'Unable to update ticket.']);
    }
    ticketAjaxResponse(200, ['status' => 200, 'id' => $ticketId]);
}

$userStmt = $con->prepare("SELECT 1 FROM users WHERE user_id = ?");
if (!$userStmt) {
    ticketAjaxResponse(500, ['status' => 500, 'message' => 'Unable to validate ticket owner.']);
}
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$userExists = $userStmt->get_result()->num_rows > 0;
$userStmt->close();
if (!$userExists) {
    ticketAjaxResponse(400, ['status' => 400, 'message' => 'Invalid ticket owner.']);
}

$allowedTypes = [
    'jpg' => ['image/jpeg'],
    'jpeg' => ['image/jpeg'],
    'png' => ['image/png'],
    'gif' => ['image/gif'],
    'webp' => ['image/webp'],
    'pdf' => ['application/pdf'],
    'txt' => ['text/plain'],
    'csv' => ['text/csv', 'text/plain', 'application/vnd.ms-excel'],
    'zip' => ['application/zip', 'application/x-zip'],
    'docx' => ['application/zip', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
    'xlsx' => ['application/zip', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
    'pptx' => ['application/zip', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'],
];
$uploadedFileIds = [];
$uploadedFilePaths = [];
$fileIdsString = '';

if (isset($_FILES['ticket_files'])) {
    $upload = $_FILES['ticket_files'];
    if (!is_array($upload['name'] ?? null) || count($upload['name']) > 20) {
        ticketAjaxResponse(400, ['status' => 400, 'message' => 'Invalid upload.']);
    }
    $uploadDirectory = realpath(__DIR__ . '/../../files/ticket');
    if ($uploadDirectory === false || !is_writable($uploadDirectory)) {
        ticketAjaxResponse(500, ['status' => 500, 'message' => 'Ticket upload storage is unavailable.']);
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);

    foreach ($upload['name'] as $index => $originalName) {
        $uploadError = $upload['error'][$index] ?? UPLOAD_ERR_NO_FILE;
        if ($uploadError === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        if ($uploadError !== UPLOAD_ERR_OK) {
            cleanupTicketUploads($con, $uploadedFileIds, $uploadedFilePaths);
            ticketAjaxResponse(400, ['status' => 400, 'message' => 'A file could not be uploaded.']);
        }

        $temporaryPath = $upload['tmp_name'][$index] ?? '';
        $fileSize = is_uploaded_file($temporaryPath) ? filesize($temporaryPath) : false;
        $extension = strtolower(pathinfo((string) $originalName, PATHINFO_EXTENSION));
        $mimeType = $temporaryPath !== '' ? $finfo->file($temporaryPath) : false;
        if ($fileSize === false || $fileSize < 1 || $fileSize > 25 * 1024 * 1024
            || !isset($allowedTypes[$extension]) || !in_array($mimeType, $allowedTypes[$extension], true)) {
            cleanupTicketUploads($con, $uploadedFileIds, $uploadedFilePaths);
            ticketAjaxResponse(400, ['status' => 400, 'message' => 'Unsupported file type or size.']);
        }

        $storedName = bin2hex(random_bytes(16)) . '.' . $extension;
        $storedPath = $uploadDirectory . DIRECTORY_SEPARATOR . $storedName;
        if (!move_uploaded_file($temporaryPath, $storedPath)) {
            cleanupTicketUploads($con, $uploadedFileIds, $uploadedFilePaths);
            ticketAjaxResponse(500, ['status' => 500, 'message' => 'Unable to store uploaded file.']);
        }

        $fileStmt = $con->prepare("INSERT INTO files (file_name, file_type, file_size) VALUES (?, ?, ?)");
        if (!$fileStmt) {
            unlink($storedPath);
            cleanupTicketUploads($con, $uploadedFileIds, $uploadedFilePaths);
            ticketAjaxResponse(500, ['status' => 500, 'message' => 'Unable to save uploaded file.']);
        }
        $fileSizeString = (string) $fileSize;
        $fileStmt->bind_param("sss", $storedName, $mimeType, $fileSizeString);
        $savedFile = $fileStmt->execute();
        $fileId = $savedFile ? (int) $con->insert_id : 0;
        $fileStmt->close();
        if (!$savedFile) {
            unlink($storedPath);
            cleanupTicketUploads($con, $uploadedFileIds, $uploadedFilePaths);
            ticketAjaxResponse(500, ['status' => 500, 'message' => 'Unable to save uploaded file.']);
        }

        $uploadedFileIds[] = $fileId;
        $uploadedFilePaths[] = $storedPath;
    }
}

$fileIdsString = implode(',', $uploadedFileIds);
$createdDate = date('m/d/Y h:i a');
$stmt = $con->prepare(
    "INSERT INTO tickets (user_id, title, project_id, type_id, description, status, files, created) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);
if (!$stmt) {
    cleanupTicketUploads($con, $uploadedFileIds, $uploadedFilePaths);
    ticketAjaxResponse(500, ['status' => 500, 'message' => 'Unable to create ticket.']);
}
$stmt->bind_param("isiiisss", $userId, $title, $projectId, $ticketTypeId, $description, $status, $fileIdsString, $createdDate);
$created = $stmt->execute();
$newTicketId = $created ? (int) $con->insert_id : 0;
$stmt->close();
if (!$created) {
    cleanupTicketUploads($con, $uploadedFileIds, $uploadedFilePaths);
    ticketAjaxResponse(500, ['status' => 500, 'message' => 'Unable to create ticket.']);
}

ticketAjaxResponse(200, ['status' => 100, 'id' => sprintf('%04d', $newTicketId)]);
