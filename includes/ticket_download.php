<?php
function denyTicketDownload(int $status, string $message): void
{
    http_response_code($status);
    exit($message);
}

require_once __DIR__ . '/role_permissions.php';

function canDownloadTicket(array $ticket, string $role, int $userId): bool
{
    return canAccessTicket($ticket, $role, $userId);
}

function ticketDownloadFileName(mysqli $con, int $fileId, int $ticketId, string $role, int $userId): ?string
{
    if ($ticketId > 0) {
        $stmt = $con->prepare(
            "SELECT f.file_name FROM files f
             WHERE f.id = ?
               AND (
                 EXISTS (SELECT 1 FROM tickets t WHERE t.ticket_id = ? AND FIND_IN_SET(CAST(f.id AS CHAR), t.files) > 0)
                 OR EXISTS (SELECT 1 FROM ticket_comments tc WHERE tc.ticket_id = ? AND FIND_IN_SET(CAST(f.id AS CHAR), tc.files) > 0)
               )
             LIMIT 1"
        );
        if (!$stmt) {
            denyTicketDownload(500, 'Unable to verify ticket attachments.');
        }
        $stmt->bind_param('iii', $fileId, $ticketId, $ticketId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ? $row['file_name'] : null;
    }

    $stmt = $con->prepare(
        "SELECT t.ticket_id, t.user_id, t.technician, f.file_name
         FROM ticket_comments tc
         INNER JOIN tickets t ON t.ticket_id = tc.ticket_id
         INNER JOIN files f ON f.id = ?
         WHERE FIND_IN_SET(CAST(f.id AS CHAR), tc.files) > 0"
    );
    if (!$stmt) {
        denyTicketDownload(500, 'Unable to verify comment attachments.');
    }
    $stmt->bind_param('i', $fileId);
    $stmt->execute();
    $result = $stmt->get_result();
    $fileName = null;
    while ($row = $result->fetch_assoc()) {
        if (canDownloadTicket($row, $role, $userId)) {
            $fileName = $row['file_name'];
            break;
        }
    }
    $stmt->close();
    return $fileName;
}

function serveTicketFileArchive(mysqli $con, bool $adminOnly): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $userId = (int) ($_SESSION['user_id'] ?? 0);
    $role = (string) ($_SESSION['role'] ?? '');
    if (empty($_SESSION['logged_in']) || $userId < 1) {
        denyTicketDownload(401, 'Authentication required.');
    }
    if ($adminOnly && !isManagementRole($role)) {
        denyTicketDownload(403, 'Forbidden.');
    }
    if (!$adminOnly && !in_array(normalizeUserRole($role), ['admin', 'director', 'head', 'clerk'], true)) {
        denyTicketDownload(403, 'Forbidden.');
    }

    $rawTicketId = $_GET['ticketID'] ?? null;
    $ticketId = filter_var($rawTicketId, FILTER_VALIDATE_INT);
    if ($ticketId === false || $ticketId === null || $ticketId < 0) {
        denyTicketDownload(400, 'Invalid ticket ID.');
    }

    if ($ticketId > 0) {
        $stmt = $con->prepare('SELECT ticket_id, user_id, technician FROM tickets WHERE ticket_id = ?');
        if (!$stmt) {
            denyTicketDownload(500, 'Unable to verify ticket access.');
        }
        $stmt->bind_param('i', $ticketId);
        $stmt->execute();
        $result = $stmt->get_result();
        $ticket = $result->fetch_assoc();
        $stmt->close();
        if (!$ticket) {
            denyTicketDownload(404, 'Ticket not found.');
        }
        if (!canDownloadTicket($ticket, $role, $userId)) {
            denyTicketDownload(403, 'Forbidden.');
        }
    }

    $rawFileIds = $_GET['files'] ?? '';
    if (!is_string($rawFileIds) || $rawFileIds === '') {
        denyTicketDownload(400, 'No files were selected.');
    }
    $rawIds = explode(',', $rawFileIds);
    if (count($rawIds) > 50) {
        denyTicketDownload(400, 'Too many files were selected.');
    }
    $fileIds = [];
    foreach ($rawIds as $rawId) {
        if (!preg_match('/^[1-9][0-9]*$/', $rawId)) {
            denyTicketDownload(400, 'Invalid file ID.');
        }
        $fileIds[] = (int) $rawId;
    }
    $fileIds = array_values(array_unique($fileIds));
    if (!$fileIds) {
        denyTicketDownload(400, 'No files were selected.');
    }

    $fileDirectory = realpath(__DIR__ . '/../files/ticket');
    if ($fileDirectory === false) {
        denyTicketDownload(500, 'Attachment storage is unavailable.');
    }
    $downloadFiles = [];
    foreach ($fileIds as $fileId) {
        $fileName = ticketDownloadFileName($con, $fileId, $ticketId, $role, $userId);
        if ($fileName === null) {
            denyTicketDownload(403, 'One or more files are not accessible.');
        }
        $filePath = realpath($fileDirectory . DIRECTORY_SEPARATOR . basename($fileName));
        if ($filePath === false || strpos($filePath, $fileDirectory . DIRECTORY_SEPARATOR) !== 0 || !is_file($filePath)) {
            denyTicketDownload(404, 'An attachment could not be found.');
        }
        $entryName = preg_replace('/[^A-Za-z0-9._ -]/', '_', basename($fileName));
        $downloadFiles[] = [$fileId, $filePath, $entryName];
    }

    if (!class_exists('ZipArchive')) {
        denyTicketDownload(500, 'ZIP support is not enabled.');
    }
    $archivePath = tempnam(sys_get_temp_dir(), 'ticket-files-');
    if ($archivePath === false) {
        denyTicketDownload(500, 'Unable to create the download archive.');
    }

    $zip = new ZipArchive();
    if ($zip->open($archivePath, ZipArchive::OVERWRITE) !== true) {
        unlink($archivePath);
        denyTicketDownload(500, 'Unable to create the download archive.');
    }
    foreach ($downloadFiles as [$fileId, $filePath, $entryName]) {
        if (!$zip->addFile($filePath, $fileId . '_' . $entryName)) {
            $zip->close();
            unlink($archivePath);
            denyTicketDownload(500, 'Unable to add an attachment to the archive.');
        }
    }
    if (!$zip->close()) {
        unlink($archivePath);
        denyTicketDownload(500, 'Unable to finish the download archive.');
    }

    $archiveName = $ticketId > 0
        ? 'Ticket_' . sprintf('%04d', $ticketId) . '_files.zip'
        : 'comment_files.zip';
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $archiveName . '"');
    header('Content-Length: ' . filesize($archivePath));
    header('Cache-Control: private, no-store');
    readfile($archivePath);
    unlink($archivePath);
    exit;
}

function serveTicketAttachment(mysqli $con, bool $adminOnly): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $userId = (int) ($_SESSION['user_id'] ?? 0);
    $role = (string) ($_SESSION['role'] ?? '');
    if (empty($_SESSION['logged_in']) || $userId < 1) {
        denyTicketDownload(401, 'Authentication required.');
    }
    if ($adminOnly && !isManagementRole($role)) {
        denyTicketDownload(403, 'Forbidden.');
    }
    if (!$adminOnly && !in_array(normalizeUserRole($role), ['admin', 'director', 'head', 'clerk'], true)) {
        denyTicketDownload(403, 'Forbidden.');
    }

    $fileId = filter_var($_GET['fileID'] ?? null, FILTER_VALIDATE_INT);
    $ticketId = filter_var($_GET['ticketID'] ?? null, FILTER_VALIDATE_INT);
    if (!$fileId || $fileId < 1 || !$ticketId || $ticketId < 1) {
        denyTicketDownload(400, 'Invalid attachment request.');
    }

    $stmt = $con->prepare('SELECT ticket_id, user_id, technician FROM tickets WHERE ticket_id = ?');
    if (!$stmt) {
        denyTicketDownload(500, 'Unable to verify ticket access.');
    }
    $stmt->bind_param('i', $ticketId);
    $stmt->execute();
    $result = $stmt->get_result();
    $ticket = $result->fetch_assoc();
    $stmt->close();
    if (!$ticket) {
        denyTicketDownload(404, 'Ticket not found.');
    }
    if (!canDownloadTicket($ticket, $role, $userId)) {
        denyTicketDownload(403, 'Forbidden.');
    }

    $stmt = $con->prepare(
        "SELECT f.file_name, f.file_type
         FROM files f
         WHERE f.id = ?
           AND (
             EXISTS (SELECT 1 FROM tickets t WHERE t.ticket_id = ? AND FIND_IN_SET(CAST(f.id AS CHAR), t.files) > 0)
             OR EXISTS (SELECT 1 FROM ticket_comments tc WHERE tc.ticket_id = ? AND FIND_IN_SET(CAST(f.id AS CHAR), tc.files) > 0)
           )
         LIMIT 1"
    );
    if (!$stmt) {
        denyTicketDownload(500, 'Unable to verify attachment access.');
    }
    $stmt->bind_param('iii', $fileId, $ticketId, $ticketId);
    $stmt->execute();
    $result = $stmt->get_result();
    $file = $result->fetch_assoc();
    $stmt->close();
    if (!$file) {
        denyTicketDownload(404, 'Attachment not found.');
    }

    $fileDirectory = realpath(__DIR__ . '/../files/ticket');
    $filePath = $fileDirectory === false
        ? false
        : realpath($fileDirectory . DIRECTORY_SEPARATOR . basename($file['file_name']));
    if ($filePath === false || strpos($filePath, $fileDirectory . DIRECTORY_SEPARATOR) !== 0 || !is_file($filePath)) {
        denyTicketDownload(404, 'Attachment not found.');
    }

    $imageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $inline = ($_GET['inline'] ?? '') === '1' && in_array($file['file_type'], $imageTypes, true);
    $contentType = $inline ? $file['file_type'] : 'application/octet-stream';
    $downloadName = preg_replace('/[^A-Za-z0-9._ -]/', '_', basename($file['file_name']));
    header('Content-Type: ' . $contentType);
    header('Content-Length: ' . filesize($filePath));
    header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . $downloadName . '"');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: private, no-store');
    readfile($filePath);
    exit;
}
