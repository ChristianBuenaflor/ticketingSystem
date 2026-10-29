<?php
function storeTicketUploads(mysqli $con, string $field): array
{
    $stored = ['ids' => [], 'paths' => []];
    if (!isset($_FILES[$field])) {
        return $stored;
    }

    $upload = $_FILES[$field];
    if (!is_array($upload['name'] ?? null) || count($upload['name']) > 20) {
        throw new RuntimeException('Invalid upload.');
    }

    $uploadDirectory = realpath(__DIR__ . '/../files/ticket');
    if ($uploadDirectory === false || !is_writable($uploadDirectory)) {
        throw new RuntimeException('Ticket upload storage is unavailable.');
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
    $finfo = new finfo(FILEINFO_MIME_TYPE);

    foreach ($upload['name'] as $index => $originalName) {
        $uploadError = $upload['error'][$index] ?? UPLOAD_ERR_NO_FILE;
        if ($uploadError === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        if ($uploadError !== UPLOAD_ERR_OK) {
            cleanupStoredTicketUploads($con, $stored);
            throw new RuntimeException('A file could not be uploaded.');
        }

        $temporaryPath = $upload['tmp_name'][$index] ?? '';
        $fileSize = is_uploaded_file($temporaryPath) ? filesize($temporaryPath) : false;
        $extension = strtolower(pathinfo((string) $originalName, PATHINFO_EXTENSION));
        $mimeType = $temporaryPath !== '' ? $finfo->file($temporaryPath) : false;
        if ($fileSize === false || $fileSize < 1 || $fileSize > 25 * 1024 * 1024
            || !isset($allowedTypes[$extension]) || !in_array($mimeType, $allowedTypes[$extension], true)) {
            cleanupStoredTicketUploads($con, $stored);
            throw new RuntimeException('Unsupported file type or size.');
        }

        $storedName = bin2hex(random_bytes(16)) . '.' . $extension;
        $storedPath = $uploadDirectory . DIRECTORY_SEPARATOR . $storedName;
        if (!move_uploaded_file($temporaryPath, $storedPath)) {
            cleanupStoredTicketUploads($con, $stored);
            throw new RuntimeException('Unable to store uploaded file.');
        }

        $stmt = $con->prepare("INSERT INTO files (file_name, file_type, file_size) VALUES (?, ?, ?)");
        if (!$stmt) {
            unlink($storedPath);
            cleanupStoredTicketUploads($con, $stored);
            throw new RuntimeException('Unable to save uploaded file.');
        }
        $fileSizeString = (string) $fileSize;
        $stmt->bind_param('sss', $storedName, $mimeType, $fileSizeString);
        $saved = $stmt->execute();
        $fileId = $saved ? (int) $con->insert_id : 0;
        $stmt->close();
        if (!$saved) {
            unlink($storedPath);
            cleanupStoredTicketUploads($con, $stored);
            throw new RuntimeException('Unable to save uploaded file.');
        }

        $stored['ids'][] = $fileId;
        $stored['paths'][] = $storedPath;
    }

    return $stored;
}

function cleanupStoredTicketUploads(mysqli $con, array $stored): void
{
    foreach ($stored['ids'] ?? [] as $fileId) {
        $stmt = $con->prepare("DELETE FROM files WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param('i', $fileId);
            if (!$stmt->execute()) {
                error_log('Unable to remove an orphaned ticket attachment record.');
            }
            $stmt->close();
        } else {
            error_log('Unable to prepare cleanup for an orphaned ticket attachment record.');
        }
    }

    foreach ($stored['paths'] ?? [] as $path) {
        if (is_file($path) && !unlink($path)) {
            error_log('Unable to remove an orphaned ticket attachment file.');
        }
    }
}
